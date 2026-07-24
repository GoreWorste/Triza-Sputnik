#!/usr/bin/env python3
"""Save current live site state as rollback snapshot (backups/current/)."""
from __future__ import annotations

import json
import re
import subprocess
import sys
from datetime import datetime
from pathlib import Path

SITE = "https://modx.romanovivv.ru"
USER = "admin"
PASS = "AdmJQuGcTd04AX9"
COOKIE = "/tmp/tizira_modx_snapshot.txt"
MGR_HTML = "/tmp/tizira_modx_snapshot_mgr.html"
ROOT = Path(__file__).resolve().parent.parent
SNAPSHOT = ROOT / "backups" / "current"

ELEMENT_RANGES = {
    "chunk": 30,
    "snippet": 30,
    "template": 15,
    "plugin": 15,
}

TRACKED_FILES = [
    "templates/jd_consult/css/theme-modern.css",
    "templates/jd_consult/css/responsive.css",
    "templates/jd_consult/css/cookie-consent.css",
    "templates/jd_consult/css/presets/preset1.css",
    "templates/jd_consult/js/cookie-consent.js",
    "templates/jd_consult/js/theme-animations.js",
]


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


def api(token: str, data: dict) -> dict:
    cmd = [
        "curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}",
        "-X", "POST", f"{SITE}/connectors/index.php",
    ]
    for k, v in data.items():
        cmd += ["--data-urlencode", f"{k}={v}"]
    return json.loads(run(cmd).stdout)


def main() -> None:
    if SNAPSHOT.exists():
        import shutil
        shutil.rmtree(SNAPSHOT)
    SNAPSHOT.mkdir(parents=True)

    token = login()
    stats = {"elements": 0, "files": 0}

    for etype, max_id in ELEMENT_RANGES.items():
        out = SNAPSHOT / "elements" / etype
        out.mkdir(parents=True, exist_ok=True)
        for i in range(1, max_id + 1):
            resp = api(token, {"action": f"element/{etype}/get", "id": str(i)})
            if not resp.get("success") or not resp.get("object"):
                continue
            obj = resp["object"]
            name = re.sub(r"[^\w.\-]+", "_", obj.get("name") or f"id_{i}")
            path = out / f"{i:03d}_{name}.json"
            path.write_text(json.dumps(obj, ensure_ascii=False, indent=2), encoding="utf-8")
            stats["elements"] += 1

    files_dir = SNAPSHOT / "files"
    files_dir.mkdir(parents=True)
    for remote in TRACKED_FILES:
        resp = api(token, {"action": "browser/file/get", "file": remote})
        if not resp.get("success"):
            print(f"warn: could not fetch {remote}", file=sys.stderr)
            continue
        content = resp["object"].get("content", "")
        dest = files_dir / remote
        dest.parent.mkdir(parents=True, exist_ok=True)
        dest.write_text(content, encoding="utf-8", errors="replace")
        stats["files"] += 1

    manifest = {
        "site": SITE,
        "created_at": datetime.now().isoformat(timespec="seconds"),
        "description": "Rollback point — restore with: python3 modxbuild/snapshot_restore.py",
        "stats": stats,
        "tracked_files": TRACKED_FILES,
    }
    (SNAPSHOT / "manifest.json").write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    print(f"snapshot saved: {SNAPSHOT}")
    print(f"elements: {stats['elements']}, files: {stats['files']}")
    print("rollback: python3 modxbuild/snapshot_restore.py")


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        sys.exit(1)
