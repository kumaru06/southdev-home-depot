"""
Fresh full FTP deploy to Hostinger — excludes vendor and sensitive/local files.
"""
import ftplib
import os
import sys
from pathlib import Path

LOCAL_ROOT = Path(r"C:\xampp\htdocs\southdev-home-depot")
REMOTE_ROOT = "public_html"
ENV_DEPLOY = LOCAL_ROOT / ".env.deploy"
ENV_PRODUCTION = LOCAL_ROOT / ".env.production"

EXCLUDE_DIRS = {
    ".git",
    "repo.git",
    "docs",
    "database",
    ".venv",
    "vendor",
    "tools",
    "node_modules",
    "storage\\mails",
    "storage/mails",
    "agent-transcripts",
}

EXCLUDE_FILES = {
    "southdev-hostinger.zip",
    "southdev-deploy.zip",
    "deploy.ps1",
    "deploy-hostinger.ps1",
    "deploy-git-status.ps1",
    "deploy-full-ftp.py",
    "ftp_upload.ps1",
    "ftp_curl_upload.ps1",
    "pack-for-hostinger.ps1",
    ".env",
    ".env.deploy",
    ".env.production",
    ".env.hostinger.example",
    ".env.deploy.example",
    ".env.example",
    ".gitignore",
    "tmp_index_html.html",
    "tmp_products_html.html",
    "tmp_products_html_after.css.html",
}


def load_env(path: Path) -> dict:
    cfg = {}
    for line in path.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, val = line.split("=", 1)
        cfg[key.strip()] = val.strip().strip('"')
    return cfg


def should_skip(rel: str) -> bool:
    parts = rel.replace("\\", "/").split("/")
    if parts[0] in EXCLUDE_DIRS:
        return True
    if any(p in EXCLUDE_DIRS for p in parts):
        return True
    name = parts[-1]
    if name in EXCLUDE_FILES:
        return True
    if name.startswith("tmp_") and name.endswith(".html"):
        return True
    return False


def ensure_dir(ftp: ftplib.FTP, remote_dir: str, cache: set) -> None:
    if remote_dir in cache or remote_dir in ("", "/"):
        return
    parts = [p for p in remote_dir.strip("/").split("/") if p]
    path = ""
    for part in parts:
        path = f"{path}/{part}" if path else part
        if path in cache:
            continue
        try:
            ftp.cwd("/")
            ftp.cwd(path)
            cache.add(path)
        except Exception:
            try:
                ftp.cwd("/")
                parent = "/".join(path.split("/")[:-1])
                if parent:
                    ftp.cwd(parent)
                else:
                    ftp.cwd("/")
                ftp.mkd(part)
                cache.add(path)
            except Exception:
                # may already exist due to race / concurrent mkdir
                cache.add(path)


def main() -> int:
    if not ENV_DEPLOY.exists():
        print("Missing .env.deploy")
        return 1

    cfg = load_env(ENV_DEPLOY)
    host = cfg.get("FTP_HOST")
    user = cfg.get("FTP_USER")
    password = cfg.get("FTP_PASS")
    if not host or not user or not password:
        print("FTP_HOST, FTP_USER, FTP_PASS required in .env.deploy")
        return 1

    files = []
    for root, dirs, filenames in os.walk(LOCAL_ROOT):
        rel_root = os.path.relpath(root, LOCAL_ROOT)
        # prune excluded dirs in-place for speed
        dirs[:] = [
            d for d in dirs
            if d not in EXCLUDE_DIRS and not should_skip(os.path.join(rel_root, d) if rel_root != "." else d)
        ]
        for name in filenames:
            full = Path(root) / name
            rel = str(full.relative_to(LOCAL_ROOT)).replace("\\", "/")
            if should_skip(rel):
                continue
            files.append((full, rel))

    files.sort(key=lambda x: x[1])
    print(f"Fresh FTP deploy to {host}")
    print(f"Uploading {len(files)} files (excluding vendor)...")
    print("")

    ok = 0
    fail = 0
    dir_cache = set()

    try:
        ftp = ftplib.FTP(host, timeout=180)
        ftp.login(user, password)
        ftp.set_pasv(True)
        ftp.cwd("/")
    except Exception as e:
        print(f"FTP login failed: {e}")
        return 1

    # Prefer production env as remote .env
    if ENV_PRODUCTION.exists():
        try:
            ensure_dir(ftp, REMOTE_ROOT, dir_cache)
            with open(ENV_PRODUCTION, "rb") as f:
                ftp.storbinary(f"STOR {REMOTE_ROOT}/.env", f, 8192)
            print(f"  [OK] /{REMOTE_ROOT}/.env")
            ok += 1
        except Exception as e:
            print(f"  [FAIL] /{REMOTE_ROOT}/.env - {e}")
            fail += 1

    for i, (full, rel) in enumerate(files, 1):
        remote_path = f"{REMOTE_ROOT}/{rel}"
        remote_dir = remote_path.rsplit("/", 1)[0]
        remote_file = remote_path.rsplit("/", 1)[-1]
        try:
            ensure_dir(ftp, remote_dir, dir_cache)
            ftp.cwd("/")
            ftp.cwd(remote_dir)
            with open(full, "rb") as f:
                ftp.storbinary(f"STOR {remote_file}", f, 8192)
            ok += 1
            if i % 25 == 0 or i == len(files):
                print(f"  progress: {i}/{len(files)}  (ok={ok}, fail={fail})")
            else:
                print(f"  [OK] /{remote_path}")
        except Exception as e:
            fail += 1
            print(f"  [FAIL] /{remote_path} - {e}")
            # reconnect once on failure
            try:
                ftp.quit()
            except Exception:
                pass
            try:
                ftp = ftplib.FTP(host, timeout=180)
                ftp.login(user, password)
                ftp.set_pasv(True)
                dir_cache = set()
            except Exception as re:
                print(f"Reconnect failed: {re}")
                break

    try:
        ftp.quit()
    except Exception:
        pass

    print("")
    print(f"Done. OK={ok} FAIL={fail}")
    print("Live: https://southdevhomedepotdavao.com")
    return 0 if fail == 0 else 2


if __name__ == "__main__":
    sys.exit(main())
