#!/usr/bin/env python3
"""Safe health-check for deploy/admin/withdraw tooling. Does not modify the server.

Usage:
  python3 modxbuild/verify_project.py
  python3 modxbuild/verify_project.py --verbose
"""
from __future__ import annotations

import argparse
import json
import os
import re
import subprocess
import sys
from dataclasses import dataclass, field
from pathlib import Path

from project_scope import (
    CHUNKS,
    CUSTOM_DB_TABLES,
    PLUGINS,
    SNIPPETS,
    TEMPLATES,
    TOP_LEVEL_ALIASES,
    TRACKED_FILES,
    TV_NAMES,
)

ROOT = Path(__file__).resolve().parent.parent


def load_env_file(path: Path) -> None:
    if not path.is_file():
        return
    for line in path.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        os.environ.setdefault(key.strip(), value.strip())


load_env_file(ROOT / ".env")
load_env_file(ROOT / ".env.example")

SITE = os.environ.get("MODX_SITE", "https://modx.romanovivv.ru")
ADMIN_USER = os.environ.get("MODX_ADMIN_USER", "admin")
ADMIN_PASS = os.environ.get("MODX_ADMIN_PASS", "")
ADMIN2_USER = os.environ.get("MODX_ADMIN2_USER", "admin2")
ADMIN2_PASS = os.environ.get("MODX_ADMIN2_PASS", "")
COOKIE = "/tmp/tizira_modx_verify.txt"
MGR_HTML = "/tmp/tizira_modx_verify_mgr.html"


@dataclass
class Report:
    ok: list[str] = field(default_factory=list)
    warn: list[str] = field(default_factory=list)
    fail: list[str] = field(default_factory=list)

    def add(self, level: str, message: str) -> None:
        getattr(self, level).append(message)

    @property
    def success(self) -> bool:
        return not self.fail


def run(cmd, check=False):
    return subprocess.run(cmd, check=check, capture_output=True, text=True)


def manager_login(username: str, password: str) -> bool:
    cookie = f"/tmp/tizira_modx_verify_{username}.txt"
    result = run([
        "curl", "-sL", "-c", cookie, "-b", cookie,
        "-X", "POST", f"{SITE}/manager/",
        "-d", f"login_context=mgr&username={username}&password={password}&rememberme=1&login=1",
        "-o", MGR_HTML,
    ])
    if result.returncode != 0:
        return False
    html = Path(MGR_HTML).read_text(encoding="utf-8", errors="ignore")
    return "<title>Dashboard" in html


def api_login() -> str:
    run([
        "curl", "-sL", "-c", COOKIE,
        "-X", "POST", f"{SITE}/manager/",
        "-d", f"login_context=mgr&username={ADMIN_USER}&password={ADMIN_PASS}&rememberme=1&login=1",
        "-o", MGR_HTML,
    ])
    html = Path(MGR_HTML).read_text(encoding="utf-8", errors="ignore")
    match = re.search(r"HTTP_MODAUTH=([^\"]+)", html)
    if not match or "Dashboard" not in html:
        raise RuntimeError("MODX API login failed")
    return match.group(1)


def api(token: str, data: dict) -> dict:
    cmd = ["curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}", "-X", "POST", f"{SITE}/connectors/index.php"]
    for key, value in data.items():
        cmd += ["--data-urlencode", f"{key}={value}"]
    result = run(cmd)
    try:
        return json.loads(result.stdout)
    except json.JSONDecodeError:
        return {"success": False, "message": result.stdout[:200]}


def element_exists(token: str, kind: str, element_id: int, expected_name: str) -> bool:
    resp = api(token, {"action": f"element/{kind}/get", "id": str(element_id)})
    if not resp.get("success"):
        return False
    obj = resp.get("object") or {}
    name_key = "templatename" if kind == "template" else "name"
    return (obj.get(name_key) or "") == expected_name


def plugin_names(token: str) -> set[str]:
    names = set()
    for pid in range(1, 40):
        resp = api(token, {"action": "element/plugin/get", "id": str(pid)})
        if not resp.get("success"):
            continue
        name = (resp.get("object") or {}).get("name")
        if name:
            names.add(name)
    return names


def tv_names_on_server(token: str) -> set[str]:
    names = set()
    for tv_id in range(1, 60):
        resp = api(token, {"action": "element/tv/get", "id": str(tv_id)})
        if not resp.get("success"):
            continue
        name = (resp.get("object") or {}).get("name")
        if name:
            names.add(name)
    return names


def file_exists(token: str, remote: str) -> bool:
    resp = api(token, {"action": "browser/file/get", "file": remote})
    return bool(resp.get("success"))


def collect_theme_files(token: str) -> list[str]:
    found = []
    for path in TRACKED_FILES:
        if file_exists(token, path):
            found.append(path)
    return found


def get_template_ids(token: str) -> set[int]:
    ids = set()
    for tpl_id, name in TEMPLATES.items():
        if element_exists(token, "template", tpl_id, name):
            ids.add(tpl_id)
    return ids


def collect_project_resources(token: str) -> list[dict]:
    """Lightweight estimate: scan first pages and count project sections."""
    template_ids = get_template_ids(token)
    resources = []
    seen_aliases = set()

    for resource_id in range(1, 201):
        resp = api(token, {"action": "resource/get", "id": str(resource_id)})
        if not resp.get("success") or not resp.get("object"):
            continue
        obj = resp["object"]
        alias = (obj.get("alias") or "").strip()
        template = int(obj.get("template") or 0)
        if alias in TOP_LEVEL_ALIASES or template in template_ids:
            if alias and alias in seen_aliases:
                continue
            if alias:
                seen_aliases.add(alias)
            resources.append({
                "id": int(obj.get("id") or resource_id),
                "alias": alias,
                "template": template,
            })
    return resources


def latest_backup() -> Path | None:
    backups = sorted((ROOT / "backups").glob("tizira-*.tar.gz"), reverse=True)
    return backups[0] if backups else None


def print_section(title: str) -> None:
    print(f"\n== {title} ==")


def main() -> int:
    parser = argparse.ArgumentParser(description="Verify MODX project tooling readiness")
    parser.add_argument("--verbose", action="store_true")
    args = parser.parse_args()
    report = Report()

    print_section("Credentials")
    if not ADMIN_PASS:
        report.add("fail", "MODX_ADMIN_PASS is empty in .env")
    else:
        report.add("ok", f"primary admin credentials loaded for '{ADMIN_USER}'")

    if not ADMIN2_PASS:
        report.add("warn", "MODX_ADMIN2_PASS is empty — second admin login will not be checked")
    elif len(ADMIN2_PASS) < 12:
        report.add("warn", "MODX_ADMIN2_PASS shorter than 12 chars — MODX may reject it")

    print_section("Manager login")
    if ADMIN_PASS and manager_login(ADMIN_USER, ADMIN_PASS):
        report.add("ok", f"primary admin '{ADMIN_USER}' can log into manager")
    else:
        report.add("fail", f"primary admin '{ADMIN_USER}' cannot log into manager")

    if ADMIN2_PASS:
        if manager_login(ADMIN2_USER, ADMIN2_PASS):
            report.add("ok", f"second admin '{ADMIN2_USER}' can log into manager")
        else:
            report.add("fail", f"second admin '{ADMIN2_USER}' cannot log into manager")

    print_section("MODX API")
    token = None
    try:
        token = api_login()
        report.add("ok", "MODX API login works (deploy/withdraw scripts can authenticate)")
    except RuntimeError as exc:
        report.add("fail", str(exc))

    if token:
        print_section("Deployed elements")
        missing_chunks = [name for cid, name in CHUNKS.items() if not element_exists(token, "chunk", cid, name)]
        missing_snippets = [name for sid, name in SNIPPETS.items() if not element_exists(token, "snippet", sid, name)]
        missing_templates = [name for tid, name in TEMPLATES.items() if not element_exists(token, "template", tid, name)]
        server_plugins = plugin_names(token)
        server_tvs = tv_names_on_server(token)
        missing_plugins = [name for name in PLUGINS if name not in server_plugins]
        missing_tvs = [name for name in TV_NAMES if name not in server_tvs]

        if missing_chunks:
            report.add("warn", f"missing chunks: {', '.join(missing_chunks)}")
        else:
            report.add("ok", f"all {len(CHUNKS)} project chunks are present")

        if missing_snippets:
            report.add("warn", f"missing snippets: {', '.join(missing_snippets)}")
        else:
            report.add("ok", f"all {len(SNIPPETS)} project snippets are present")

        if missing_templates:
            report.add("warn", f"missing templates: {', '.join(missing_templates)}")
        else:
            report.add("ok", f"all {len(TEMPLATES)} project templates are present")

        if missing_plugins:
            report.add("warn", f"missing plugins: {', '.join(missing_plugins)}")
        else:
            report.add("ok", f"all {len(PLUGINS)} project plugins are present")

        if missing_tvs:
            report.add("warn", f"missing TVs: {', '.join(missing_tvs)}")
        else:
            report.add("ok", f"all {len(TV_NAMES)} project TVs are present")

        print_section("Theme files")
        missing_files = [path for path in TRACKED_FILES if not file_exists(token, path)]
        theme_files = collect_theme_files(token)
        if missing_files:
            report.add("warn", f"missing tracked files: {', '.join(missing_files)}")
        else:
            report.add("ok", f"all {len(TRACKED_FILES)} tracked theme files are present")
        report.add("ok", f"found {len(theme_files)} tracked theme files on server")

        print_section("Withdraw dry-run")
        resources = collect_project_resources(token)
        report.add("ok", f"withdraw --full would target at least {len(resources)} project resources/pages")
        report.add("ok", f"withdraw --full would drop DB tables: {', '.join(CUSTOM_DB_TABLES)}")
        report.add("ok", "withdraw scripts are ready if manager access remains available")

        if args.verbose and resources:
            print("  sample resources:")
            for item in resources[:10]:
                print(f"    - id={item['id']} alias={item['alias'] or '(no alias)'}")
            if len(resources) > 10:
                print(f"    ... and {len(resources) - 10} more")

    print_section("Local backups")
    archive = latest_backup()
    if archive:
        size_mb = archive.stat().st_size / (1024 * 1024)
        report.add("ok", f"latest local backup: {archive.name} ({size_mb:.1f} MB)")
    else:
        report.add("warn", "no local backup archive found — run: python3 modxbuild/backup_full.py")

    print_section("Summary")
    for message in report.ok:
        print(f"OK   {message}")
    for message in report.warn:
        print(f"WARN {message}")
    for message in report.fail:
        print(f"FAIL {message}")

    if report.success:
        print("\nAll critical checks passed.")
        if report.warn:
            print("There are warnings — review them before relying on withdraw.")
        return 0

    print("\nSome critical checks failed — fix issues above before relying on scripts.")
    return 1


if __name__ == "__main__":
    sys.exit(main())
