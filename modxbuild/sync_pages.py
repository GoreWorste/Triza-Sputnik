#!/usr/bin/env python3
"""Create/update service pages and site_name on live MODX (non-destructive)."""
from __future__ import annotations

import json
import re
import subprocess
import sys
from pathlib import Path

SITE = "https://modx.romanovivv.ru"
USER = "admin"
PASS = "AdmJQuGcTd04AX9"
COOKIE = "/tmp/tizira_modx_sync.txt"
MGR_HTML = "/tmp/tizira_mgr_sync.html"

PAGES = [
    ("rekrutment", "Рекрутмент", "Качественный рекрутмент — СПУТНИК-Персонал"),
    ("regionalnyy-podbor", "Региональный подбор", "Региональный подбор персонала — СПУТНИК-Персонал"),
    ("autstaffing", "Аутстаффинг", "Аутстаффинг и предоставление персонала — СПУТНИК-Персонал"),
    ("executive-search", "Executive Search", "Executive search — СПУТНИК-Персонал"),
    ("khedkhanting", "Хедхантинг", "Хедхантинг — СПУТНИК-Персонал"),
    ("domashnij-personal", "Домашний персонал", "Домашний персонал — СПУТНИК-Персонал"),
]

RENAMES = [
    ("trainings-and-webinars", "Обучение"),
    ("zapros-na-podbor-personala", "Запрос на подбор персонала"),
    ("blog", "Новости"),
]


def run(cmd: list[str], check: bool = True) -> subprocess.CompletedProcess[str]:
    return subprocess.run(cmd, check=check, capture_output=True, text=True)


def api(token: str, data: dict | None = None) -> dict:
    cmd = [
        "curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}",
        "-X", "POST", f"{SITE}/connectors/index.php",
        "--max-time", "60",
    ]
    for k, v in (data or {}).items():
        cmd += ["--data-urlencode", f"{k}={v}"]
    result = run(cmd)
    try:
        return json.loads(result.stdout)
    except json.JSONDecodeError:
        return {"success": False, "message": result.stdout[:800], "raw": True}


def login() -> str:
    run([
        "curl", "-sL", "-c", COOKIE,
        "-X", "POST", f"{SITE}/manager/",
        "-d", f"login_context=mgr&username={USER}&password={PASS}&rememberme=1&login=1",
        "-o", MGR_HTML,
    ])
    html = Path(MGR_HTML).read_text(encoding="utf-8", errors="ignore")
    m = re.search(r'HTTP_MODAUTH=([^\"]+)', html)
    if not m or "Dashboard" not in html:
        raise RuntimeError("MODX login failed")
    return m.group(1)


def load_alias_map(token: str) -> dict[str, int]:
    """One scan of resources to map alias -> id."""
    alias_map: dict[str, int] = {}
    start = 0
    while True:
        resp = api(token, {
            "action": "resource/getlist",
            "start": str(start),
            "limit": "100",
        })
        results = resp.get("results") or []
        if not results and not resp.get("success", True):
            print("getlist fail:", resp.get("message"))
            break
        for obj in results:
            alias = obj.get("alias")
            if alias:
                alias_map[alias] = int(obj["id"])
        total = int(resp.get("total") or 0)
        start += len(results)
        if not results or start >= total:
            break
    print(f"indexed {len(alias_map)} resources")
    return alias_map


def upsert_page(token: str, alias_map: dict[str, int], alias: str, title: str, longtitle: str, menuindex: int) -> None:
    existing = alias_map.get(alias)
    base = {
        "pagetitle": title,
        "longtitle": longtitle,
        "alias": alias,
        "parent": "0",
        "template": "2",
        "published": "1",
        "hidemenu": "0",
        "isfolder": "0",
        "richtext": "0",
        "searchable": "1",
        "cacheable": "1",
        "content": f"<!-- content from assets/services/{alias}.html via pageProse -->",
        "menuindex": str(menuindex),
        "class_key": "modDocument",
        "context_key": "web",
        "uri": alias,
        "uri_override": "1",
    }
    if existing:
        payload = {"action": "resource/update", "id": str(existing), **base}
        resp = api(token, payload)
        if not resp.get("success"):
            raise RuntimeError(f"update {alias}: {resp.get('message')}")
        print(f"updated {alias} id={existing}")
        rid = existing
    else:
        payload = {"action": "resource/create", **base}
        resp = api(token, payload)
        if not resp.get("success"):
            raise RuntimeError(f"create {alias}: {resp.get('message')}")
        rid = int((resp.get("object") or {}).get("id") or 0)
        alias_map[alias] = rid
        print(f"created {alias} id={rid}")

    # best-effort TVs
    for tv, value in (("title_mode", "breadcrumb"), ("show_search", "0")):
        api(token, {
            "action": "resource/tvs/updatefromgrid",
            "data": json.dumps({"id": rid, "tvname": tv, "value": value}),
        })


def set_site_name(token: str) -> None:
    for action in (
        {"action": "system/settings/update", "key": "site_name", "value": "Кадровое агентство СПУТНИК-Персонал"},
        {"action": "workspace/package/update", "skip": "1"},  # noop placeholder
    ):
        if action.get("skip"):
            continue
        resp = api(token, action)
        print("site_name:", resp.get("success"), resp.get("message"))


def rename_pages(token: str, alias_map: dict[str, int]) -> None:
    for alias, title in RENAMES:
        rid = alias_map.get(alias)
        if not rid:
            print(f"rename skip missing {alias}")
            continue
        resp = api(token, {
            "action": "resource/update",
            "id": str(rid),
            "pagetitle": title,
            "alias": alias,
        })
        print(f"rename {alias}: {resp.get('success')} {resp.get('message','')}")


def clear_cache(token: str) -> None:
    resp = api(token, {"action": "system/clearcache"})
    print("cache:", resp.get("success"), resp.get("message", ""))


def main() -> None:
    token = login()
    print("logged in")
    set_site_name(token)
    alias_map = load_alias_map(token)
    for i, (alias, title, longtitle) in enumerate(PAGES, start=10):
        upsert_page(token, alias_map, alias, title, longtitle, menuindex=i)
    rename_pages(token, alias_map)
    clear_cache(token)
    print("sync pages complete")


if __name__ == "__main__":
    main()
