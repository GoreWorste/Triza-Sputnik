#!/usr/bin/env python3
"""Deploy MODX site bundle to jobcv.ru via FTP."""
from __future__ import annotations

import ftplib
import os
import sys
import time
from pathlib import Path

FTP_HOST = os.environ.get("JOBCV_FTP_HOST", "5.23.51.195")
FTP_USER = os.environ.get("JOBCV_FTP_USER", "jobcv")
FTP_PASS = os.environ.get("JOBCV_FTP_PASS", "")
REMOTE_ROOT = os.environ.get("JOBCV_FTP_PATH", "jobcv.ru/public_html")
LOCAL_ROOT = Path(os.environ.get("JOBCV_SITE_DIR", "/tmp/jobcv_site"))
RETRIES = int(os.environ.get("JOBCV_FTP_RETRIES", "5"))


def connect() -> ftplib.FTP:
    ftp = ftplib.FTP(FTP_HOST, timeout=180)
    ftp.login(FTP_USER, FTP_PASS)
    return ftp


def ensure_remote_dir(ftp: ftplib.FTP, remote_dir: str) -> None:
    ftp.cwd("/")
    for part in remote_dir.strip("/").split("/"):
        if not part:
            continue
        try:
            ftp.cwd(part)
        except ftplib.error_perm:
            ftp.mkd(part)
            ftp.cwd(part)


def remote_size(ftp: ftplib.FTP, name: str) -> int | None:
    try:
        return ftp.size(name)
    except ftplib.error_perm:
        return None


def upload_file(ftp: ftplib.FTP, local: Path, remote_dir: str) -> ftplib.FTP:
    ensure_remote_dir(ftp, remote_dir)
    size = local.stat().st_size
    if remote_size(ftp, local.name) == size:
        return ftp
    for attempt in range(1, RETRIES + 1):
        try:
            ensure_remote_dir(ftp, remote_dir)
            with local.open("rb") as fh:
                ftp.storbinary(f"STOR {local.name}", fh)
            print(f"uploaded {remote_dir}/{local.name}")
            return ftp
        except (TimeoutError, ftplib.error_temp, OSError, EOFError) as exc:
            if attempt == RETRIES:
                raise
            print(f"retry {attempt} {remote_dir}/{local.name}: {exc}", file=sys.stderr)
            time.sleep(2 * attempt)
            try:
                ftp.quit()
            except Exception:
                pass
            ftp = connect()
    return ftp


def upload_tree(ftp: ftplib.FTP, local: Path, remote_dir: str) -> ftplib.FTP:
    for item in sorted(local.iterdir()):
        if item.name.startswith("."):
            continue
        if item.is_dir():
            ftp = upload_tree(ftp, item, f"{remote_dir.rstrip('/')}/{item.name}")
        else:
            ftp = upload_file(ftp, item, remote_dir)
    return ftp


def main() -> None:
    if not FTP_PASS:
        print("Set JOBCV_FTP_PASS", file=sys.stderr)
        sys.exit(1)
    if not LOCAL_ROOT.is_dir():
        print(f"Missing site dir: {LOCAL_ROOT}", file=sys.stderr)
        sys.exit(1)

    ftp = connect()
    ftp = upload_tree(ftp, LOCAL_ROOT, REMOTE_ROOT)
    ftp.quit()
    print("deploy complete")


if __name__ == "__main__":
    main()
