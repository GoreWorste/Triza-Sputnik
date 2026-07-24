#!/usr/bin/env python3
"""Create or update a second MODX administrator with full rights.

Usage:
  MODX_ADMIN2_USER=admin2 \\
  MODX_ADMIN2_PASS='strong-password-here' \\
  MODX_ADMIN2_EMAIL=you@example.com \\
  python3 modxbuild/create_admin.py --confirm

The second admin is a normal Administrator group member — not hidden, same rights as admin.
"""
from __future__ import annotations

import argparse
import json
import os
import re
import subprocess
import sys
from pathlib import Path

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
USER = os.environ.get("MODX_ADMIN_USER", "admin")
PASS = os.environ.get("MODX_ADMIN_PASS", "AdmJQuGcTd04AX9")
COOKIE = "/tmp/tizira_modx_create_admin.txt"
MGR_HTML = "/tmp/tizira_modx_create_admin_mgr.html"

ADMIN_GROUP_ID = "1"   # MODX default "Administrator" user group
ADMIN_ROLE_ID = "2"    # MODX default "Administrator" role inside that group


def run(cmd, check=True):
    return subprocess.run(cmd, check=check, capture_output=True, text=True)


def api(token, data):
    cmd = ["curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}", "-X", "POST", f"{SITE}/connectors/index.php"]
    for key, value in data.items():
        cmd += ["--data-urlencode", f"{key}={value}"]
    result = run(cmd)
    try:
        return json.loads(result.stdout)
    except json.JSONDecodeError:
        print(result.stdout[:500], file=sys.stderr)
        raise


def format_error(resp):
    parts = [resp.get("message", "")]
    for item in resp.get("data") or []:
        if isinstance(item, dict) and item.get("msg"):
            parts.append(item["msg"])
    text = "; ".join(part for part in parts if part)
    return text or repr(resp)


def user_password_fields(password):
    return {
        "passwordgenmethod": "u",
        "passwordnotifymethod": "s",
        "specifiedpassword": password,
        "confirmpassword": password,
    }


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
        if "modx-login-form" in html or "Login" in html:
            raise RuntimeError(
                f"MODX login failed for user '{USER}'. "
                "Check MODX_ADMIN_USER and MODX_ADMIN_PASS in .env"
            )
        raise RuntimeError("MODX login failed — unexpected manager response")
    return match.group(1)


def find_user(token, username):
    resp = api(token, {
        "action": "security/user/getlist",
        "start": "0",
        "limit": "200",
        "query": username,
    })
    if isinstance(resp, dict) and resp.get("results"):
        for item in resp["results"]:
            if (item.get("username") or "").lower() == username.lower():
                return int(item["id"])
    for user_id in range(1, 50):
        resp = api(token, {"action": "security/user/get", "id": str(user_id)})
        if not resp.get("success"):
            continue
        obj = resp.get("object") or {}
        if (obj.get("username") or "").lower() == username.lower():
            return user_id
    return None


def user_in_admin_group(token, user_id):
    resp = api(token, {
        "action": "security/group/user/getlist",
        "user": str(user_id),
        "start": "0",
        "limit": "20",
    })
    rows = []
    if isinstance(resp, dict):
        rows = resp.get("results") or resp.get("object") or []
    elif isinstance(resp, list):
        rows = resp
    for row in rows:
        group_id = str(row.get("user_group") or row.get("usergroup") or "")
        if group_id == ADMIN_GROUP_ID:
            return True
    return False


def ensure_admin_group(token, user_id):
    if user_in_admin_group(token, user_id):
        print(f"user id={user_id} already in Administrator group")
        return
    resp = api(token, {
        "action": "security/group/user/create",
        "user": str(user_id),
        "usergroup": ADMIN_GROUP_ID,
        "role": ADMIN_ROLE_ID,
    })
    if not resp.get("success"):
        message = format_error(resp)
        if "already in this user group" not in message.lower():
            raise RuntimeError(f"failed to add user to Administrator group: {message}")
        print(f"user id={user_id} already in Administrator group")
        return
    print(f"user id={user_id} added to Administrator group")


def create_or_update_admin(token, username, password, email):
    user_id = find_user(token, username)
    if user_id:
        resp = api(token, {
            "action": "security/user/update",
            "id": str(user_id),
            "username": username,
            "email": email,
            "active": "1",
            "sudo": "1",
            **user_password_fields(password),
        })
        if not resp.get("success"):
            raise RuntimeError(f"failed to update user: {format_error(resp)}")
        print(f"updated existing user {username} (id={user_id})")
    else:
        resp = api(token, {
            "action": "security/user/create",
            "username": username,
            "email": email,
            "active": "1",
            "sudo": "1",
            **user_password_fields(password),
        })
        if not resp.get("success"):
            raise RuntimeError(f"failed to create user: {format_error(resp)}")
        user_id = int((resp.get("object") or {}).get("id") or 0)
        if not user_id:
            user_id = find_user(token, username)
        if not user_id:
            raise RuntimeError("user created but id not found")
        print(f"created user {username} (id={user_id})")

    ensure_admin_group(token, user_id)
    print(f"second admin ready: {username}")
    print(f"login: {SITE}/manager/")


def main():
    parser = argparse.ArgumentParser(description="Create second MODX administrator")
    parser.add_argument("--confirm", action="store_true", help="Required to apply changes")
    parser.add_argument("--username", default=os.environ.get("MODX_ADMIN2_USER", "admin2"))
    parser.add_argument("--email", default=os.environ.get("MODX_ADMIN2_EMAIL", "admin2@local.test"))
    args = parser.parse_args()

    password = os.environ.get("MODX_ADMIN2_PASS", "")
    if not password:
        print("Set MODX_ADMIN2_PASS environment variable with the new admin password.", file=sys.stderr)
        sys.exit(1)
    if len(password) < 12:
        print("MODX_ADMIN2_PASS is too short. Use at least 12 characters.", file=sys.stderr)
        sys.exit(1)

    if not args.confirm:
        print("Nothing changed. Example:", file=sys.stderr)
        print(
            "  MODX_ADMIN2_USER=admin2 MODX_ADMIN2_PASS='...' MODX_ADMIN2_EMAIL=you@mail.com "
            "python3 modxbuild/create_admin.py --confirm",
            file=sys.stderr,
        )
        sys.exit(1)

    if args.username.lower() == USER.lower():
        print("Choose a different username than the primary admin.", file=sys.stderr)
        sys.exit(1)

    token = login()
    print(f"logged in to {SITE}")
    create_or_update_admin(token, args.username, password, args.email)


if __name__ == "__main__":
    main()
