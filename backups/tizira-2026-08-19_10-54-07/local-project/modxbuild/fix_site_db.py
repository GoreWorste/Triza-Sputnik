#!/usr/bin/env python3
"""Restore MODX resources after wrong DB / mixed database.

- Removes alien pallet-site pages (ids ~1003+)
- Recreates SPUTNIK pages (create works; update returns HTTP 500 on this host)
- Sets site_start / site_name via system/settings/update (namespace=core)

Run: python3 modxbuild/fix_site_db.py
"""
from __future__ import annotations

import json
import os
import re
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent

# Known wrong-site resources from mixed database
ALIEN_IDS = list(range(1005, 1016)) + list(range(1003, 1024))

# Pages that may exist with wrong template/snippet and must be recreated
RECREATE_ALIASES = {"about", "contacts"}

SCAN_RANGES = [(1, 200), (900, 1200)]

CORE_PAGES = [
    (0, "home", "Главная", "Кадровое агентство СПУТНИК-Персонал"),
    (1, "about", "О компании", "О компании — СПУТНИК-Персонал"),
    (2, "vacancy", "Вакансии", "Вакансии — СПУТНИК-Персонал"),
    (3, "rekrutment", "Рекрутмент", "Качественный рекрутмент — СПУТНИК-Персонал"),
    (4, "regionalnyy-podbor", "Региональный подбор", "Региональный подбор персонала — СПУТНИК-Персонал"),
    (5, "autstaffing", "Аутстаффинг", "Аутстаффинг — СПУТНИК-Персонал"),
    (6, "executive-search", "Executive Search", "Executive search — СПУТНИК-Персонал"),
    (7, "khedkhanting", "Хедхантинг", "Хедхантинг — СПУТНИК-Персонал"),
    (8, "domashnij-personal", "Домашний персонал", "Домашний персонал — СПУТНИК-Персонал"),
    (9, "trainings-and-webinars", "Обучение", "Обучение — СПУТНИК-Персонал"),
    (10, "contacts", "Контакты", "Контакты — СПУТНИК-Персонал"),
    (11, "blog", "Новости", "Новости — СПУТНИК-Персонал"),
    (12, "zapros-na-podbor-personala", "Запрос на подбор персонала", "Запрос на подбор персонала — СПУТНИК-Персонал"),
]

LEGAL_PAGES = [
    (20, "politika-obrabotki-personalnykh-dannykh-polzovatelej-sajta", "Политика обработки персональных данных", "Политика ПДн"),
    (21, "soglashenie-ob-okazanii-uslug-po-ispolzovaniyu-sajta", "Соглашение об оказании услуг", "Соглашение"),
]


def load_env() -> None:
    for name in (".env", ".env.example"):
        path = ROOT / name
        if not path.is_file():
            continue
        for line in path.read_text(encoding="utf-8").splitlines():
            line = line.strip()
            if not line or line.startswith("#") or "=" not in line:
                continue
            key, value = line.split("=", 1)
            os.environ.setdefault(key.strip(), value.strip())


load_env()

SITE = os.environ.get("MODX_SITE", "https://modx.romanovivv.ru")
COOKIE = "/tmp/tizira_fix_site_db.txt"
MGR_HTML = "/tmp/tizira_fix_site_db_mgr.html"


def run(cmd: list[str], check: bool = True) -> subprocess.CompletedProcess[str]:
    return subprocess.run(cmd, check=check, capture_output=True, text=True)


def api(token: str, data: dict | None = None) -> dict:
    cmd = [
        "curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}",
        "-X", "POST", f"{SITE}/connectors/index.php", "--max-time", "60",
    ]
    for k, v in (data or {}).items():
        cmd += ["--data-urlencode", f"{k}={v}"]
    raw = run(cmd).stdout
    if not raw.strip():
        return {"success": False, "message": "empty response", "raw": True}
    try:
        return json.loads(raw)
    except json.JSONDecodeError as exc:
        raise RuntimeError(f"API JSON error: {exc}; body={raw[:200]!r}") from exc


def login() -> str:
    users = []
    u1, p1 = os.environ.get("MODX_ADMIN_USER", "admin"), os.environ.get("MODX_ADMIN_PASS", "")
    u2, p2 = os.environ.get("MODX_ADMIN2_USER", "admin2"), os.environ.get("MODX_ADMIN2_PASS", "")
    if u1 and p1:
        users.append((u1, p1))
    if u2 and p2 and (u2, p2) not in users:
        users.append((u2, p2))
    if not users:
        raise RuntimeError("No MODX credentials in .env")

    for user, password in users:
        run([
            "curl", "-sL", "-c", COOKIE,
            "-X", "POST", f"{SITE}/manager/",
            "-d", f"login_context=mgr&username={user}&password={password}&rememberme=1&login=1",
            "-o", MGR_HTML,
        ])
        html = Path(MGR_HTML).read_text(encoding="utf-8", errors="ignore")
        m = re.search(r"HTTP_MODAUTH=([^\"]+)", html)
        if m and "Dashboard" in html:
            print(f"logged in as {user}")
            return m.group(1)
        print(f"login failed for {user}", file=sys.stderr)
    raise RuntimeError("MODX login failed for all configured accounts")


def scan_resources(token: str) -> dict[str, dict]:
    by_alias: dict[str, dict] = {}
    by_id: dict[int, dict] = {}
    for start, end in SCAN_RANGES:
        for rid in range(start, end):
            resp = api(token, {"action": "resource/get", "id": str(rid)})
            if not resp.get("success"):
                continue
            obj = resp.get("object") or {}
            if obj.get("deleted"):
                continue
            by_id[int(obj["id"])] = obj
            alias = obj.get("alias")
            if alias:
                by_alias[alias] = obj
    print(f"found {len(by_id)} resources ({len(by_alias)} aliases)")
    return by_alias


def delete_resource(token: str, rid: int, label: str = "") -> bool:
    resp = api(token, {"action": "resource/delete", "id": str(rid)})
    ok = bool(resp.get("success"))
    tag = label or str(rid)
    print(f"delete {tag} id={rid}: {'ok' if ok else resp.get('message', 'fail')}")
    return ok


def remove_aliens(token: str, by_alias: dict[str, dict]) -> None:
    # Children before parents
    for rid in sorted(ALIEN_IDS, reverse=True):
        resp = api(token, {"action": "resource/get", "id": str(rid)})
        if not resp.get("success"):
            continue
        obj = resp.get("object") or {}
        if obj.get("deleted"):
            continue
        delete_resource(token, rid, obj.get("alias", ""))
        alias = obj.get("alias")
        if alias and alias in by_alias:
            del by_alias[alias]


def remove_broken_pages(token: str, by_alias: dict[str, dict]) -> None:
    for alias in RECREATE_ALIASES:
        obj = by_alias.get(alias)
        if not obj:
            continue
        tpl = int(obj.get("template") or 0)
        content = (obj.get("content") or "").strip()
        if tpl == 2 and "andromedda" not in content.lower():
            print(f"keep {alias} id={obj['id']} (template ok)")
            continue
        delete_resource(token, int(obj["id"]), alias)
        del by_alias[alias]


def page_content(alias: str) -> str:
    if alias == "home":
        return "<!-- home via assets/home/home-content.html -->"
    if alias in {"about", "contacts", "trainings-and-webinars"}:
        return f"<!-- {alias} via pageProse / assets -->"
    if alias in {
        "rekrutment", "regionalnyy-podbor", "autstaffing", "executive-search",
        "khedkhanting", "domashnij-personal", "zapros-na-podbor-personala",
    }:
        return f"<!-- file: assets/services/{alias}.html -->"
    return "<!-- content via pageProse / assets -->"


def create_page(token: str, by_alias: dict[str, dict], menuindex: int, alias: str, title: str, longtitle: str) -> int:
    existing = by_alias.get(alias)
    if existing:
        return int(existing["id"])

    payload = {
        "action": "resource/create",
        "pagetitle": title,
        "longtitle": longtitle,
        "alias": alias,
        "parent": "0",
        "template": "2",
        "published": "1",
        "hidemenu": "0" if alias not in {
            "politika-obrabotki-personalnykh-dannykh-polzovatelej-sajta",
            "soglashenie-ob-okazanii-uslug-po-ispolzovaniyu-sajta",
        } else "1",
        "isfolder": "1" if alias in {"vacancy", "blog", "trainings-and-webinars"} else "0",
        "richtext": "0",
        "searchable": "1",
        "cacheable": "1",
        "content": page_content(alias),
        "menuindex": str(menuindex),
        "class_key": "modDocument",
        "context_key": "web",
        "uri": alias,
        "uri_override": "1",
    }
    resp = api(token, payload)
    if not resp.get("success"):
        raise RuntimeError(f"create {alias}: {resp.get('message')}")
    rid = int((resp.get("object") or {}).get("id") or 0)
    by_alias[alias] = {"id": rid, "alias": alias, "template": 2}
    print(f"created {alias} id={rid}")
    return rid


def set_site_settings(token: str, home_id: int) -> None:
    for key, value in (
        ("site_name", "Кадровое агентство СПУТНИК-Персонал"),
        ("site_start", str(home_id)),
        ("error_page", str(home_id)),
        ("unauthorized_page", str(home_id)),
    ):
        resp = api(token, {
            "action": "system/settings/update",
            "namespace": "core",
            "key": key,
            "value": value,
        })
        print(f"setting {key}={value}: {resp.get('success')} {resp.get('message', '')}")


def main() -> None:
    token = login()
    by_alias = scan_resources(token)

    # 1) Drop broken about/contacts (wrong template 6 / foreign snippets)
    remove_broken_pages(token, by_alias)

    # 2) Create home first so we can point site_start before deleting index (1003)
    home_id = create_page(token, by_alias, 0, "home", "Главная", "Кадровое агентство СПУТНИК-Персонал")
    set_site_settings(token, home_id)

    # 3) Remove alien pallet-site tree
    remove_aliens(token, by_alias)

    # 4) Create remaining SPUTNIK pages
    for menuindex, alias, title, longtitle in CORE_PAGES + LEGAL_PAGES:
        if alias == "home":
            continue
        create_page(token, by_alias, menuindex, alias, title, longtitle)

    set_site_settings(token, home_id)
    api(token, {"action": "system/clearcache"})
    print("fix_site_db complete — run: python3 modxbuild/deploy_remote.py")


if __name__ == "__main__":
    main()
