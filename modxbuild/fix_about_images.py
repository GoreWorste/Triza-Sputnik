#!/usr/bin/env python3
"""Extract about-page base64 images, upload to MODX, rewrite resource content."""
import base64
import json
import re
import subprocess
import sys
from pathlib import Path

SITE = "https://modx.romanovivv.ru"
USER = "admin"
PASS = "AdmJQuGcTd04AX9"
COOKIE = "/tmp/tizira_modx.txt"
MGR_HTML = "/tmp/tizira_modx.txt.mgr"
ROOT = Path(__file__).resolve().parent.parent
BACKUP = ROOT / "backups/tizira-2026-07-23_21-13-32/modx-resources/0098_about.json"
OUT_DIR = ROOT / "modxbuild/assets/about"
ABOUT_ID = 98


def run(cmd, check=True):
    return subprocess.run(cmd, check=check, capture_output=True, text=True)


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


def api(token, data=None, files=None):
    cmd = ["curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}", "-X", "POST", f"{SITE}/connectors/index.php"]
    if files:
        for k, v in (data or {}).items():
            cmd += ["-F", f"{k}={v}"]
        for k, path in files.items():
            cmd += ["-F", f"{k}=@{path}"]
    else:
        for k, v in (data or {}).items():
            cmd += ["--data-urlencode", f"{k}={v}"]
    result = run(cmd)
    body = result.stdout.strip()
    if not body:
        raise RuntimeError(f"empty API response for {data.get('action')}")
    return json.loads(body)


def extract_images(content):
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    files = []
    idx = 0

    def replace_one(match):
        nonlocal idx
        src = match.group(1)
        m = re.match(r"data:image/(jpeg|jpg|png|gif|webp);base64,(.+)", src, re.I | re.S)
        if not m:
            return match.group(0)
        ext = m.group(1).lower().replace("jpeg", "jpg")
        raw = base64.b64decode(m.group(2))
        idx += 1
        name = f"about-{idx}.{ext}"
        local = OUT_DIR / name
        local.write_bytes(raw)
        files.append(local)
        return f'src="/images/about/{name}"'

    new_content = re.sub(r'src="(data:image/[^"]+)"', replace_one, content)
    return new_content, files


def upload_file(token, remote_path, local_path):
    api(token, {"action": "browser/file/remove", "file": remote_path})
    resp = api(token, {
        "action": "browser/file/upload",
        "path": str(Path(remote_path).parent) + "/",
    }, files={"file": str(local_path)})
    if not resp.get("success"):
        raise RuntimeError(f"upload {remote_path}: {resp.get('message')}")


def main():
    if not BACKUP.exists():
        raise SystemExit(f"backup not found: {BACKUP}")

    data = json.loads(BACKUP.read_text(encoding="utf-8"))
    content = data.get("content") or ""
    new_content, image_files = extract_images(content)
    print(f"extracted {len(image_files)} images")
    print(f"content {len(content)} -> {len(new_content)} bytes")

    content_file = OUT_DIR / "about-content.html"
    content_file.write_text(new_content, encoding="utf-8")

    fix_php = OUT_DIR / "fix_about_run.php"
    fix_php.write_text(f"""<?php
define('MODX_API_MODE', true);
require dirname(__DIR__, 2) . '/index.php';
$id = {ABOUT_ID};
$res = $modx->getObject('modResource', $id);
if (!$res) {{
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(500);
    echo 'resource not found';
    exit;
}}
$content = file_get_contents(__DIR__ . '/about-content.html');
if ($content === false) {{
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(500);
    echo 'content file missing';
    exit;
}}
$res->set('content', $content);
if (!$res->save()) {{
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(500);
    echo 'save failed';
    exit;
}}
$modx->cacheManager->refresh();
header('Content-Type: text/plain; charset=utf-8');
echo 'ok bytes=' . strlen($content);
""", encoding="utf-8")

    token = login()
    print("logged in")

    for local in image_files:
        upload_file(token, f"images/about/{local.name}", local)
        print(f"uploaded images/about/{local.name}")

    upload_file(token, "assets/about/about-content.html", content_file)
    upload_file(token, "assets/about/fix_about_run.php", fix_php)
    print("uploaded fix runner")

    out = run(["curl", "-sL", "-w", "\\nHTTP:%{http_code}", f"{SITE}/assets/about/fix_about_run.php"], check=False)
    print("fix run:", out.stdout.strip())
    if "ok bytes=" not in out.stdout:
        raise SystemExit("about content fix failed")

    api(token, {"action": "browser/file/remove", "file": "assets/about/fix_about_run.php"})
    api(token, {"action": "browser/file/remove", "file": "assets/about/about-content.html"})
    print("cleaned up runner")
    print("done")


if __name__ == "__main__":
    main()
