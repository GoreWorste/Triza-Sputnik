#!/usr/bin/env python3
"""Full backup of modx.romanovivv.ru via MODX Manager API + local project."""
from __future__ import annotations

import json
import re
import shutil
import subprocess
import sys
import tarfile
import time
import urllib.parse
from datetime import datetime
from pathlib import Path

SITE = "https://modx.romanovivv.ru"
USER = "admin"
PASS = "AdmJQuGcTd04AX9"
COOKIE = "/tmp/tizira_modx_backup.txt"
MGR_HTML = "/tmp/tizira_modx_backup_mgr.html"
ROOT = Path(__file__).resolve().parent.parent
BACKUPS = ROOT / "backups"

ELEMENT_TYPES = {
    "chunk": 30,
    "snippet": 30,
    "template": 15,
    "plugin": 15,
    "tv": 40,
}
RESOURCE_MAX_ID = 1500
SKIP_DIRS = {"manager", "core/cache", "core/packages", "core/export"}
TEXT_EXT = {
    ".php", ".html", ".htm", ".css", ".js", ".json", ".xml", ".txt", ".md",
    ".ini", ".tpl", ".chunk", ".less", ".svg", ".htaccess",
}


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


def api(token: str, data: dict) -> dict | list:
    cmd = [
        "curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}",
        "-X", "POST", f"{SITE}/connectors/index.php",
    ]
    for k, v in data.items():
        cmd += ["--data-urlencode", f"{k}={v}"]
    out = run(cmd).stdout
    try:
        return json.loads(out)
    except json.JSONDecodeError:
        return {"success": False, "raw": out[:500]}


def download_public(url_path: str, dest: Path) -> bool:
    dest.parent.mkdir(parents=True, exist_ok=True)
    url = SITE + ("" if url_path.startswith("/") else "/") + url_path
    r = run([
        "curl", "-sL", "--fail", "-o", str(dest), url,
    ], check=False)
    return r.returncode == 0 and dest.exists() and dest.stat().st_size > 0


def save_file_api(token: str, remote: str, dest: Path) -> bool:
    resp = api(token, {"action": "browser/file/get", "file": remote})
    if not resp.get("success"):
        return False
    obj = resp.get("object") or {}
    content = obj.get("content")
    if content is None:
        return False
    dest.parent.mkdir(parents=True, exist_ok=True)
    dest.write_text(content, encoding="utf-8", errors="replace")
    return True


def list_dir(token: str, dir_id: str) -> list[dict]:
    r = run([
        "curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}",
        "-X", "POST", f"{SITE}/connectors/index.php",
        "-d", f"action=browser/directory/getList&id={urllib.parse.quote(dir_id, safe='')}",
    ])
    try:
        data = json.loads(r.stdout)
    except json.JSONDecodeError:
        return []
    return data if isinstance(data, list) else []


def backup_files(token: str, out_dir: Path, stats: dict) -> None:
    files_root = out_dir / "server-files"
    queue = ["/"]

    while queue:
        dir_id = queue.pop(0)
        norm = dir_id.strip("/")
        if norm in SKIP_DIRS or any(norm.startswith(s + "/") or norm == s for s in SKIP_DIRS):
            continue
        for item in list_dir(token, dir_id if dir_id != "/" else "/"):
            typ = item.get("type")
            path = (item.get("path") or "").lstrip("/")
            if not path:
                continue
            if typ == "dir":
                queue.append(path + "/")
                continue
            if typ != "file":
                continue
            stats["files_total"] += 1
            dest = files_root / path
            ext = dest.suffix.lower()
            ok = False
            if ext in TEXT_EXT or ext == "":
                ok = save_file_api(token, path, dest)
            if not ok:
                ok = download_public(path, dest)
            if ok:
                stats["files_ok"] += 1
            else:
                stats["files_fail"] += 1
                stats["failed_paths"].append(path)


def backup_elements(token: str, out_dir: Path, stats: dict) -> None:
    elements_dir = out_dir / "modx-elements"
    for etype, max_id in ELEMENT_TYPES.items():
        etype_dir = elements_dir / etype
        etype_dir.mkdir(parents=True, exist_ok=True)
        for i in range(1, max_id + 1):
            resp = api(token, {"action": f"element/{etype}/get", "id": str(i)})
            if not resp.get("success"):
                continue
            obj = resp["object"]
            name = obj.get("name") or f"id_{i}"
            safe = re.sub(r"[^\w.\-]+", "_", name)
            (etype_dir / f"{i:03d}_{safe}.json").write_text(
                json.dumps(obj, ensure_ascii=False, indent=2),
                encoding="utf-8",
            )
            stats["elements"] += 1


def backup_resources(token: str, out_dir: Path, stats: dict) -> None:
    res_dir = out_dir / "modx-resources"
    res_dir.mkdir(parents=True, exist_ok=True)
    for i in range(1, RESOURCE_MAX_ID + 1):
        resp = api(token, {"action": "resource/get", "id": str(i)})
        if not resp.get("success") or not resp.get("object"):
            continue
        obj = resp["object"]
        alias = obj.get("alias") or f"id_{i}"
        safe = re.sub(r"[^\w.\-]+", "_", alias)[:80]
        (res_dir / f"{i:04d}_{safe}.json").write_text(
            json.dumps(obj, ensure_ascii=False, indent=2),
            encoding="utf-8",
        )
        stats["resources"] += 1


def backup_local_project(out_dir: Path) -> None:
    local = out_dir / "local-project"
    local.mkdir(parents=True, exist_ok=True)
    for name in ("modxbuild", "build", "docs", "shots", "dist"):
        src = ROOT / name
        if src.exists():
            shutil.copytree(src, local / name, dirs_exist_ok=True,
                            ignore=shutil.ignore_patterns("__pycache__", "*.pyc", ".git"))
    for fname in ("full_dump.sql",):
        src = ROOT / fname
        if src.exists():
            shutil.copy2(src, local / fname)


def write_manifest(out_dir: Path, stats: dict, started: float) -> None:
    manifest = {
        "site": SITE,
        "created_at": datetime.now().isoformat(timespec="seconds"),
        "duration_sec": round(time.time() - started, 1),
        "stats": stats,
        "contents": {
            "server-files": "All site files from MODX file manager",
            "modx-elements": "Chunks, snippets, templates, plugins, TVs (JSON)",
            "modx-resources": "All MODX resources (JSON)",
            "local-project": "Local theme/build sources from this repo",
            "config": "MODX configuration files",
        },
        "restore_note": (
            "For full DB restore use config/database credentials on the server "
            "and run mysqldump restore, or import modx-resources JSON via MODX manager."
        ),
    }
    (out_dir / "manifest.json").write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    readme = f"""ТРИЗА-Спутник — полный бэкап сайта
================================
Сайт: {SITE}
Дата: {manifest['created_at']}

Содержимое:
- server-files/     — файлы сайта с сервера
- modx-elements/    — элементы MODX (чанки, сниппеты, шаблоны…)
- modx-resources/   — все страницы и ресурсы MODX
- local-project/    — локальный проект темы (modxbuild, build, docs)
- config/           — конфигурация MODX

Статистика:
- файлов: {stats['files_ok']}/{stats['files_total']}
- элементов MODX: {stats['elements']}
- ресурсов: {stats['resources']}

Архив: {out_dir.name}.tar.gz
"""
    (out_dir / "README.txt").write_text(readme, encoding="utf-8")


def make_archive(out_dir: Path) -> Path:
    archive = out_dir.with_suffix(".tar.gz")
    with tarfile.open(archive, "w:gz") as tar:
        tar.add(out_dir, arcname=out_dir.name)
    return archive


def main() -> None:
    started = time.time()
    stamp = datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
    out_dir = BACKUPS / f"tizira-{stamp}"
    out_dir.mkdir(parents=True, exist_ok=True)

    stats = {
        "files_total": 0,
        "files_ok": 0,
        "files_fail": 0,
        "elements": 0,
        "resources": 0,
        "failed_paths": [],
    }

    print("logging in...")
    token = login()

    print("backing up config...")
    cfg_dir = out_dir / "config"
    cfg_dir.mkdir(parents=True, exist_ok=True)
    for f in ("core/config/config.inc.php", "config.core.php", "index.php"):
        save_file_api(token, f, cfg_dir / Path(f).name)

    print("backing up MODX elements...")
    backup_elements(token, out_dir, stats)

    print("backing up MODX resources...")
    backup_resources(token, out_dir, stats)

    print("backing up server files (this may take several minutes)...")
    backup_files(token, out_dir, stats)

    print("backing up local project...")
    backup_local_project(out_dir)

    write_manifest(out_dir, stats, started)

    print("creating archive...")
    archive = make_archive(out_dir)
    size_mb = archive.stat().st_size / (1024 * 1024)
    print(f"backup complete: {archive}")
    print(f"size: {size_mb:.1f} MB")
    print(f"files: {stats['files_ok']}/{stats['files_total']}, "
          f"elements: {stats['elements']}, resources: {stats['resources']}")
    if stats["files_fail"]:
        print(f"failed files: {stats['files_fail']} (see manifest.json)")


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        sys.exit(1)
