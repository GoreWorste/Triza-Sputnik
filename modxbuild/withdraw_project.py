#!/usr/bin/env python3
"""Withdraw deployed project from remote MODX site.

Modes:
  python withdraw_project.py --confirm              # elements + tracked files only
  python withdraw_project.py --confirm --full       # pages, DB tables, theme dirs, cache
  python withdraw_project.py --confirm --full --backup-first

Works only while you still have legitimate manager access.
Keep local backups (backup_full.py) — without them, neither you nor the client can restore.
"""
from __future__ import annotations

import argparse
import json
import re
import secrets
import subprocess
import sys
import tempfile
import urllib.parse
from pathlib import Path

from project_scope import (
    CHUNKS,
    CUSTOM_DB_TABLES,
    PLUGINS,
    RESOURCE_SCAN_MAX_ID,
    SECTION_ROOT_ALIASES,
    SNIPPETS,
    TEMPLATE_NAMES,
    TEMPLATES,
    THEME_PREFIXES,
    TOP_LEVEL_ALIASES,
    TRACKED_FILES,
    TV_NAMES,
)

SITE = "https://modx.romanovivv.ru"
USER = "admin"
PASS = "AdmJQuGcTd04AX9"
COOKIE = "/tmp/tizira_modx_withdraw.txt"
MGR_HTML = "/tmp/tizira_modx_withdraw_mgr.html"
ROOT = Path(__file__).resolve().parent.parent
CLEANUP_TEMPLATE = Path(__file__).resolve().parent / "assets/withdraw_cleanup.php"
CLEANUP_REMOTE = "assets/_withdraw_cleanup.php"


def run(cmd, check=True):
    return subprocess.run(cmd, check=check, capture_output=True, text=True)


def api(token, data=None, files=None):
    cmd = ["curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}", "-X", "POST", f"{SITE}/connectors/index.php"]
    if files:
        for key, value in (data or {}).items():
            cmd += ["-F", f"{key}={value}"]
        for key, path in files.items():
            cmd += ["-F", f"{key}=@{path}"]
    else:
        for key, value in (data or {}).items():
            cmd += ["--data-urlencode", f"{key}={value}"]
    result = run(cmd)
    try:
        return json.loads(result.stdout)
    except json.JSONDecodeError:
        print(result.stdout[:500], file=sys.stderr)
        raise


def login():
    run([
        "curl", "-sL", "-c", COOKIE,
        "-X", "POST", f"{SITE}/manager/",
        "-d", f"login_context=mgr&username={USER}&password={PASS}&rememberme=1&login=1",
        "-o", MGR_HTML,
    ])
    html = Path(MGR_HTML).read_text(encoding="utf-8", errors="ignore")
    match = re.search(r"HTTP_MODAUTH=([^\"]+)", html)
    if not match or "Dashboard" not in html:
        raise RuntimeError("MODX login failed — manager access may have been revoked")
    return match.group(1)


def remove_element(token, kind, element_id, name):
    resp = api(token, {"action": f"element/{kind}/remove", "id": str(element_id)})
    if resp.get("success"):
        print(f"{kind} {name} (id={element_id}) removed")
        return True
    print(f"{kind} {name} (id={element_id}): {resp.get('message', resp)}", file=sys.stderr)
    return False


def remove_plugin_by_name(token, name):
    for pid in range(1, 40):
        resp = api(token, {"action": "element/plugin/get", "id": str(pid)})
        if not resp.get("success"):
            continue
        obj = resp.get("object") or {}
        if obj.get("name") == name:
            remove_element(token, "plugin", pid, name)
            return


def remove_tv_by_name(token, name):
    for tv_id in range(1, 60):
        resp = api(token, {"action": "element/tv/get", "id": str(tv_id)})
        if not resp.get("success"):
            continue
        obj = resp.get("object") or {}
        if obj.get("name") == name:
            remove_element(token, "tv", tv_id, name)
            return


def remove_file(token, remote):
    resp = api(token, {"action": "browser/file/remove", "file": remote})
    if resp.get("success"):
        print(f"file {remote} removed")
        return True
    print(f"file {remote}: {resp.get('message', resp)}", file=sys.stderr)
    return False


def list_dir(token, dir_id):
    result = run([
        "curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}",
        "-X", "POST", f"{SITE}/connectors/index.php",
        "-d", f"action=browser/directory/getList&id={urllib.parse.quote(dir_id, safe='')}",
    ])
    try:
        data = json.loads(result.stdout)
    except json.JSONDecodeError:
        return []
    return data if isinstance(data, list) else []


def collect_files_under_prefixes(token, prefixes):
    files = []
    queue = ["/"]
    seen_dirs = set()
    while queue:
        dir_id = queue.pop(0)
        norm = dir_id.strip("/")
        if norm in seen_dirs:
            continue
        seen_dirs.add(norm)
        for item in list_dir(token, dir_id if dir_id != "/" else "/"):
            item_type = item.get("type")
            path = (item.get("path") or "").lstrip("/")
            if not path:
                continue
            if item_type == "dir":
                queue.append(path + "/")
                continue
            if item_type != "file":
                continue
            if any(path.startswith(prefix) for prefix in prefixes):
                files.append(path)
    return sorted(set(files), reverse=True)


def get_template_ids(token):
    ids = set()
    for tpl_id, name in TEMPLATES.items():
        resp = api(token, {"action": "element/template/get", "id": str(tpl_id)})
        if resp.get("success") and (resp.get("object") or {}).get("templatename") == name:
            ids.add(int(tpl_id))
            continue
        for scan_id in range(1, 30):
            resp = api(token, {"action": "element/template/get", "id": str(scan_id)})
            if not resp.get("success"):
                continue
            obj = resp.get("object") or {}
            if obj.get("templatename") == name:
                ids.add(int(scan_id))
                break
    return ids


def collect_project_resources(token):
    template_ids = get_template_ids(token)
    resources = {}
    alias_to_id = {}

    for resource_id in range(1, RESOURCE_SCAN_MAX_ID + 1):
        resp = api(token, {"action": "resource/get", "id": str(resource_id)})
        if not resp.get("success") or not resp.get("object"):
            continue
        obj = resp["object"]
        rid = int(obj.get("id") or resource_id)
        alias = (obj.get("alias") or "").strip()
        if alias:
            alias_to_id[alias] = rid
        resources[rid] = {
            "id": rid,
            "alias": alias,
            "parent": int(obj.get("parent") or 0),
            "template": int(obj.get("template") or 0),
            "pagetitle": obj.get("pagetitle") or "",
        }

    section_ids = {alias_to_id[alias] for alias in SECTION_ROOT_ALIASES if alias in alias_to_id}

    def in_project_tree(resource):
        rid = resource["id"]
        if resource["alias"] in TOP_LEVEL_ALIASES:
            return True
        if resource["template"] in template_ids:
            return True
        parent = resource["parent"]
        seen = set()
        while parent and parent not in seen:
            if parent in section_ids:
                return True
            seen.add(parent)
            parent = resources.get(parent, {}).get("parent", 0)
        return False

    selected = [resources[rid] for rid in sorted(resources) if in_project_tree(resources[rid])]

    def depth(resource):
        d = 0
        parent = resource["parent"]
        seen = set()
        while parent and parent not in seen and parent in resources:
            seen.add(parent)
            d += 1
            parent = resources[parent]["parent"]
        return d

    selected.sort(key=depth, reverse=True)
    return selected


def delete_resource(token, resource):
    rid = resource["id"]
    label = resource["alias"] or resource["pagetitle"] or str(rid)
    resp = api(token, {"action": "resource/delete", "id": str(rid)})
    if resp.get("success"):
        print(f"resource {label} (id={rid}) deleted")
        return True
    print(f"resource {label} (id={rid}): {resp.get('message', resp)}", file=sys.stderr)
    return False


def clear_cache(token):
    for action in ("system/cache/clear", "system/refreshuris"):
        resp = api(token, {"action": action})
        if resp.get("success"):
            print(f"cache action {action} ok")
            return True
    print("cache clear via API not available (cleanup script will refresh cache)", file=sys.stderr)
    return False


def run_db_cleanup(token):
    token_value = secrets.token_urlsafe(24)
    body = CLEANUP_TEMPLATE.read_text(encoding="utf-8").replace("{{TOKEN}}", token_value)
    with tempfile.NamedTemporaryFile("w", suffix=".php", delete=False, encoding="utf-8") as tmp:
        tmp.write(body)
        tmp_path = tmp.name

    try:
        resp = api(token, {
            "action": "browser/file/upload",
            "path": "assets/",
        }, files={"file": tmp_path})
        if not resp.get("success"):
            raise RuntimeError(f"cleanup upload failed: {resp.get('message', resp)}")

        url = f"{SITE}/{CLEANUP_REMOTE}?token={urllib.parse.quote(token_value)}"
        result = run(["curl", "-sL", "--fail", url], check=False)
        if result.returncode != 0:
            print(result.stdout, file=sys.stderr)
            print(result.stderr, file=sys.stderr)
            raise RuntimeError("cleanup script request failed")
        print(result.stdout.strip() or "cleanup script executed")
    finally:
        Path(tmp_path).unlink(missing_ok=True)
        remove_file(token, CLEANUP_REMOTE)


def run_backup_first():
    script = ROOT / "modxbuild/backup_full.py"
    print(f"running backup: {script}")
    subprocess.run([sys.executable, str(script)], check=True)


def withdraw_elements(token):
    for remote in TRACKED_FILES:
        remove_file(token, remote)

    for plugin_name in PLUGINS:
        remove_plugin_by_name(token, plugin_name)

    for snippet_id, name in SNIPPETS.items():
        remove_element(token, "snippet", snippet_id, name)

    for chunk_id, name in CHUNKS.items():
        remove_element(token, "chunk", chunk_id, name)

    for tpl_id, name in TEMPLATES.items():
        remove_element(token, "template", tpl_id, name)

    for tv_name in TV_NAMES:
        remove_tv_by_name(token, tv_name)


def withdraw_full(token):
    resources = collect_project_resources(token)
    print(f"project resources to delete: {len(resources)}")
    for resource in resources:
        delete_resource(token, resource)

    theme_files = collect_files_under_prefixes(token, THEME_PREFIXES)
    for remote in theme_files:
        remove_file(token, remote)

    withdraw_elements(token)
    run_db_cleanup(token)
    clear_cache(token)


def main():
    parser = argparse.ArgumentParser(description="Withdraw deployed MODX project from remote site")
    parser.add_argument("--confirm", action="store_true", help="Required to make any changes")
    parser.add_argument("--full", action="store_true", help="Also remove pages, DB tables, theme dirs and cache")
    parser.add_argument("--backup-first", action="store_true", help="Run backup_full.py before withdrawal")
    args = parser.parse_args()

    if not args.confirm:
        print(
            "Nothing changed. Examples:\n"
            "  python withdraw_project.py --confirm\n"
            "  python withdraw_project.py --confirm --full --backup-first",
            file=sys.stderr,
        )
        sys.exit(1)

    if args.backup_first:
        run_backup_first()

    token = login()
    print(f"logged in to {SITE}")

    if args.full:
        withdraw_full(token)
        print("full withdrawal complete")
        print("local backups in backups/ are your only restore copy — do not upload them to the client server")
    else:
        withdraw_elements(token)
        print("elements withdrawal complete (use --full to remove pages and DB tables too)")


if __name__ == "__main__":
    main()
