#!/usr/bin/env python3
"""Restore site from backups/current/ snapshot."""
from __future__ import annotations

import json
import re
import subprocess
import sys
from pathlib import Path

SITE = "https://modx.romanovivv.ru"
USER = "admin"
PASS = "AdmJQuGcTd04AX9"
COOKIE = "/tmp/tizira_modx_snapshot.txt"
MGR_HTML = "/tmp/tizira_modx_snapshot_mgr.html"
ROOT = Path(__file__).resolve().parent.parent
SNAPSHOT = ROOT / "backups" / "current"


def run(cmd: list[str], check: bool = True) -> subprocess.CompletedProcess[str]:
    return subprocess.run(cmd, check=check, capture_output=True, text=True)


def login() -> str:
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


def api(token: str, data: dict | None = None, files: dict | None = None) -> dict:
    cmd = [
        "curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}",
        "-X", "POST", f"{SITE}/connectors/index.php",
    ]
    if files:
        for k, v in (data or {}).items():
            cmd += ["-F", f"{k}={v}"]
        for k, path in files.items():
            cmd += ["-F", f"{k}=@{path}"]
    else:
        for k, v in (data or {}).items():
            cmd += ["--data-urlencode", f"{k}={v}"]
    return json.loads(run(cmd).stdout)


def restore_elements(token: str, etype: str) -> int:
    elem_dir = SNAPSHOT / "elements" / etype
    if not elem_dir.exists():
        return 0
    count = 0
    for path in sorted(elem_dir.glob("*.json")):
        obj = json.loads(path.read_text(encoding="utf-8"))
        eid = str(obj["id"])
        name = obj["name"]
        field = "snippet" if etype != "template" else "content"
        body = obj.get("snippet") or obj.get("content") or ""
        if etype == "snippet":
            body = body.strip()
        resp = api(token, {
            "action": f"element/{etype}/update",
            "id": eid,
            "name": name,
            field: body,
        })
        if not resp.get("success"):
            raise RuntimeError(f"{etype} {name}: {resp.get('message')}")
        print(f"restored {etype} {name}")
        count += 1
    return count


def restore_files(token: str) -> int:
    files_dir = SNAPSHOT / "files"
    if not files_dir.exists():
        return 0
    count = 0
    for local in sorted(files_dir.rglob("*")):
        if not local.is_file():
            continue
        remote = str(local.relative_to(files_dir)).replace("\\", "/")
        api(token, {"action": "browser/file/remove", "file": remote})
        resp = api(token, {
            "action": "browser/file/upload",
            "path": str(Path(remote).parent) + "/",
        }, files={"file": str(local)})
        if not resp.get("success"):
            raise RuntimeError(f"upload {remote}: {resp.get('message')}")
        print(f"restored file {remote}")
        count += 1
    return count


def main() -> None:
    manifest_path = SNAPSHOT / "manifest.json"
    if not manifest_path.exists():
        print(f"ERROR: no snapshot at {SNAPSHOT}", file=sys.stderr)
        print("Run first: python3 modxbuild/snapshot_save.py", file=sys.stderr)
        sys.exit(1)

    manifest = json.loads(manifest_path.read_text(encoding="utf-8"))
    print(f"restoring snapshot from {manifest.get('created_at')}")

    token = login()
    total = 0
    for etype in ("chunk", "snippet", "template", "plugin"):
        total += restore_elements(token, etype)
    total += restore_files(token)

    print(f"restore complete ({total} items)")


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        sys.exit(1)
