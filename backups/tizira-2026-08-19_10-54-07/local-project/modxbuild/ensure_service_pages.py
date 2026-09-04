#!/usr/bin/env python3
"""Create/update service resources and rename site branding pages."""
import json
import re
import subprocess
import sys
from pathlib import Path

SITE = "https://modx.romanovivv.ru"
USER = "admin"
PASS = "AdmJQuGcTd04AX9"
COOKIE = "/tmp/tizira_modx.txt"
MGR_HTML = "/tmp/tizira_mgr.html"

PAGES = [
    ("Рекрутмент", "rekrutment", 10),
    ("Региональный подбор", "regionalnyy-podbor", 11),
    ("Аутстаффинг", "autstaffing", 12),
    ("Executive Search", "executive-search", 13),
    ("Хедхантинг", "khedkhanting", 14),
    ("Домашний персонал", "domashnij-personal", 15),
]


def run(cmd, check=True):
    return subprocess.run(cmd, check=check, capture_output=True, text=True)


def api(token, data=None):
    cmd = [
        "curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}",
        "-X", "POST", f"{SITE}/connectors/index.php",
    ]
    for k, v in (data or {}).items():
        cmd += ["--data-urlencode", f"{k}={v}"]
    result = run(cmd)
    try:
        return json.loads(result.stdout)
    except json.JSONDecodeError:
        print(result.stdout[:1000], file=sys.stderr)
        raise


def login():
    run([
        "curl", "-sL", "-c", COOKIE,
        "-X", "POST", f"{SITE}/manager/",
        "-d", f"login_context=mgr&username={USER}&password={PASS}&rememberme=1&login=1",
        "-o", MGR_HTML,
    ])
    html = Path(MGR_HTML).read_text(encoding="utf-8", errors="ignore")
    m = re.search(r"HTTP_MODAUTH=([^\"]+)", html)
    if not m or "Dashboard" not in html:
        raise RuntimeError("MODX login failed")
    return m.group(1)


def list_resources(token):
    resp = api(token, {
        "action": "resource/getlist",
        "limit": "200",
        "start": "0",
    })
    if resp.get("success"):
        return resp.get("results") or resp.get("object") or []
    # fallback scan
    out = []
    for rid in range(1, 120):
        r = api(token, {"action": "resource/get", "id": str(rid)})
        if r.get("success"):
            out.append(r.get("object") or {})
    return out


def find_base_template(token):
    for tid in range(1, 30):
        resp = api(token, {"action": "element/template/get", "id": str(tid)})
        if resp.get("success") and (resp.get("object") or {}).get("templatename") == "base":
            return tid
    return 2


def upsert(token, title, alias, menuindex, template, by_alias):
    existing = by_alias.get(alias)
    data = {
        "pagetitle": title,
        "longtitle": title,
        "alias": alias,
        "published": "1",
        "hidemenu": "0",
        "isfolder": "0",
        "template": str(template),
        "parent": "0",
        "content": f"<!-- file override: assets/services/{alias}.html -->",
        "richtext": "0",
        "searchable": "1",
        "cacheable": "0",
        "menuindex": str(menuindex),
        "context_key": "web",
        "class_key": "modDocument",
        "content_type": "1",
        "uri_override": "0",
        "show_in_tree": "1",
    }
    # TV defaults if supported as fields
    data["tvtitle_mode"] = "breadcrumb"
    data["tvshow_search"] = "0"

    if existing:
        data["action"] = "resource/update"
        data["id"] = str(existing["id"])
        resp = api(token, data)
        print(f"update {alias}: success={resp.get('success')} msg={resp.get('message')}")
        return existing["id"]

    data["action"] = "resource/create"
    resp = api(token, data)
    rid = (resp.get("object") or {}).get("id")
    print(f"create {alias}: success={resp.get('success')} id={rid} msg={resp.get('message')}")
    return rid


def main():
    token = login()
    print("logged in")
    template = find_base_template(token)
    print("template", template)

    resources = list_resources(token)
    by_alias = {}
    for r in resources:
        if isinstance(r, dict) and r.get("alias"):
            by_alias[r["alias"]] = r
    print("resources known", len(by_alias))

    for title, alias, idx in PAGES:
        upsert(token, title, alias, idx, template, by_alias)

    # retitle zapros page
    z = by_alias.get("zapros-na-podbor-personala")
    if z:
        resp = api(token, {
            "action": "resource/update",
            "id": str(z["id"]),
            "pagetitle": "Запрос на подбор персонала",
            "longtitle": "Запрос на подбор персонала — СПУТНИК-Персонал",
            "alias": "zapros-na-podbor-personala",
            "published": "1",
            "template": str(z.get("template") or template),
            "content": z.get("content") or "",
            "parent": str(z.get("parent") or 0),
            "context_key": "web",
            "class_key": "modDocument",
            "cacheable": "0",
        })
        print("zapros retitle", resp.get("success"))

    # site_name
    resp = api(token, {
        "action": "system/settings/updatefromgrid",
        "data": json.dumps({
            "key": "site_name",
            "value": "Кадровое агентство СПУТНИК-Персонал",
            "area": "site",
            "namespace": "core",
        }),
    })
    print("site_name", resp.get("success"), resp.get("message"))

    api(token, {"action": "resource/resourcegroup/getlist"})
    clear = api(token, {"action": "cache/clear"})
    print("cache clear", clear.get("success"))
    print("done")


if __name__ == "__main__":
    main()
