#!/usr/bin/env python3
"""Deploy theme assets and MODX elements to modx.romanovivv.ru."""
import json
import os
import re
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent


def load_env() -> None:
    for name in (".env", ".env.example"):
        path = ROOT / name
        if not path.is_file():
            continue
        for line in path.read_text(encoding="utf-8").splitlines():
            line = line.strip()
            if not line or line.startswith("#") or "=" not in line:
                continue
            key, value = line.split("=", 1)
            os.environ.setdefault(key.strip(), value.strip())


load_env()

SITE = os.environ.get("MODX_SITE", "https://modx.romanovivv.ru")
USER = os.environ.get("MODX_ADMIN_USER", "admin")
PASS = os.environ.get("MODX_ADMIN_PASS", "AdmJQuGcTd04AX9")
COOKIE = "/tmp/tizira_modx.txt"
MGR_HTML = "/tmp/tizira_mgr.html"
ELEMENTS = ROOT / "modxbuild/elements"

CHUNKS = {
    1: "head",
    2: "topbarLogo",
    3: "footer",
    4: "mapBlock",
    5: "copyright",
    6: "tail",
    7: "slider",
    8: "searchBar",
    9: "pageFooterBands",
}

SNIPPETS = {
    1: "spMenu",
    2: "pageTitle",
    3: "hotVacancies",
    4: "jobList",
    5: "newsList",
    6: "jobDetail",
    7: "newsDetail",
    8: "newsPageType",
    9: "aboutContent",
    10: "pageProse",
    11: "trainingsContent",
    12: "contactsContent",
    13: "staffingForm",
}

TEMPLATES = {
    2: "base",
    3: "vacancy",
    4: "blog",
    5: "news",
}

FILES = [
    ("templates/jd_consult/css/theme-modern.css", ROOT / "modxbuild/assets/theme-modern.css"),
    ("templates/jd_consult/css/responsive.css", ROOT / "modxbuild/assets/responsive.css"),
    ("templates/jd_consult/css/cookie-consent.css", ROOT / "modxbuild/assets/cookie-consent.css"),
    ("templates/jd_consult/js/cookie-consent.js", ROOT / "build/assets/template/js/cookie-consent.js"),
    ("templates/jd_consult/js/theme-animations.js", ROOT / "modxbuild/assets/theme-animations.js"),
    ("assets/about/about-content.html", ROOT / "modxbuild/assets/about/about-content.html"),
    ("assets/trainings/trainings-content.html", ROOT / "modxbuild/assets/trainings/trainings-content.html"),
    ("assets/contacts/contacts-content.html", ROOT / "modxbuild/assets/contacts/contacts-content.html"),
    ("assets/home/home-content.html", ROOT / "modxbuild/assets/home/home-content.html"),
    ("assets/services/rekrutment.html", ROOT / "modxbuild/assets/services/rekrutment.html"),
    ("assets/services/regionalnyy-podbor.html", ROOT / "modxbuild/assets/services/regionalnyy-podbor.html"),
    ("assets/services/autstaffing.html", ROOT / "modxbuild/assets/services/autstaffing.html"),
    ("assets/services/executive-search.html", ROOT / "modxbuild/assets/services/executive-search.html"),
    ("assets/services/khedkhanting.html", ROOT / "modxbuild/assets/services/khedkhanting.html"),
    ("assets/services/domashnij-personal.html", ROOT / "modxbuild/assets/services/domashnij-personal.html"),
    ("assets/services/zapros-na-podbor-personala.html", ROOT / "modxbuild/assets/services/zapros-na-podbor-personala.html"),
]

DIRS = [
    "assets/home/",
    "assets/services/",
    "assets/about/",
]


def run(cmd, check=True):
    if cmd and cmd[0] == "curl" and "--max-time" not in cmd:
        cmd = cmd[:1] + ["--max-time", "120", "--connect-timeout", "30"] + cmd[1:]
    return subprocess.run(cmd, check=check, capture_output=True, text=True)


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
    try:
        return json.loads(result.stdout)
    except json.JSONDecodeError:
        print(result.stdout[:500], file=sys.stderr)
        raise


def login():
    users = []
    if USER and PASS:
        users.append((USER, PASS))
    u2, p2 = os.environ.get("MODX_ADMIN2_USER", ""), os.environ.get("MODX_ADMIN2_PASS", "")
    if u2 and p2 and (u2, p2) not in users:
        users.append((u2, p2))
    for user, password in users:
        run([
            "curl", "-sL", "-c", COOKIE,
            "-X", "POST", f"{SITE}/manager/",
            "-d", f"login_context=mgr&username={user}&password={password}&rememberme=1&login=1",
            "-o", MGR_HTML,
        ])
        html = Path(MGR_HTML).read_text(encoding="utf-8", errors="ignore")
        m = re.search(r"HTTP_MODAUTH=([^\"]+)", html)
        if m and "Dashboard" in html:
            print(f"logged in as {user}")
            return m.group(1)
    raise RuntimeError("MODX login failed")


def read_php(path):
    s = path.read_text(encoding="utf-8")
    s = re.sub(r"^\s*<\?php\s*", "", s, count=1)
    s = re.sub(r"\?>\s*$", "", s)
    if path.name == "staffingForm.snippet.php":
        site_key = os.environ.get("RECAPTCHA_SITE_KEY", "")
        secret_key = os.environ.get("RECAPTCHA_SECRET_KEY", "")
        s = s.replace("{{RECAPTCHA_SITE_KEY}}", site_key)
        s = s.replace("{{RECAPTCHA_SECRET_KEY}}", secret_key)
    return s


def upsert_chunk(token, chunk_id, name):
    path = ELEMENTS / f"chunks/{name}.chunk.html"
    body = path.read_text(encoding="utf-8")
    resp = api(token, {
        "action": "element/chunk/update",
        "id": str(chunk_id),
        "name": name,
        "snippet": body,
    })
    if resp.get("success"):
        print(f"chunk {name} updated")
        return
    resp = api(token, {
        "action": "element/chunk/create",
        "name": name,
        "snippet": body,
    })
    if not resp.get("success"):
        raise RuntimeError(f"chunk {name}: {resp.get('message')}")
    print(f"chunk {name} created")


def upsert_snippet(token, snippet_id, name):
    path = ELEMENTS / f"snippets/{name}.snippet.php"
    body = read_php(path)
    resp = api(token, {
        "action": "element/snippet/update",
        "id": str(snippet_id),
        "name": name,
        "snippet": body,
        "locked": "0",
    })
    if resp.get("success"):
        print(f"snippet {name} updated")
        return
    # fallback: find by name
    for sid in range(1, 40):
        got = api(token, {"action": "element/snippet/get", "id": str(sid)})
        if got.get("success") and (got.get("object") or {}).get("name") == name:
            resp = api(token, {
                "action": "element/snippet/update",
                "id": str(sid),
                "name": name,
                "snippet": body,
                "locked": "0",
            })
            if resp.get("success"):
                print(f"snippet {name} updated (id={sid})")
                return
            break
    resp = api(token, {
        "action": "element/snippet/create",
        "name": name,
        "snippet": body,
        "locked": "0",
    })
    if not resp.get("success"):
        raise RuntimeError(f"snippet {name}: {resp.get('message')}")
    print(f"snippet {name} created")


def upsert_template(token, tpl_id, name, content):
    resp = api(token, {
        "action": "element/template/update",
        "id": str(tpl_id),
        "templatename": name,
        "content": content,
    })
    if resp.get("success"):
        print(f"template {name} updated")
        return tpl_id
    resp = api(token, {
        "action": "element/template/create",
        "templatename": name,
        "content": content,
    })
    if not resp.get("success"):
        raise RuntimeError(f"template {name}: {resp.get('message')}")
    new_id = resp.get("object", {}).get("id", tpl_id)
    print(f"template {name} created (id={new_id})")
    return new_id


def upsert_plugin(token, name):
    path = ELEMENTS / f"snippets/{name}.snippet.php"
    body = read_php(path)
    plugin_id = None
    for pid in range(1, 30):
        resp = api(token, {"action": "element/plugin/get", "id": str(pid)})
        if not resp.get("success"):
            continue
        obj = resp.get("object") or {}
        if obj.get("name") == name:
            plugin_id = pid
            break
    if plugin_id:
        resp = api(token, {
            "action": "element/plugin/update",
            "id": str(plugin_id),
            "name": name,
            "plugincode": body,
            "disabled": "0",
        })
        if not resp.get("success"):
            raise RuntimeError(f"plugin {name}: {resp.get('message')}")
        for event in ("OnBeforeDocFormSave",):
            api(token, {
                "action": "element/plugin/event/update",
                "pluginid": str(plugin_id),
                "event": event,
                "priority": "0",
            })
        print(f"plugin {name} updated")
        return
    resp = api(token, {
        "action": "element/plugin/create",
        "name": name,
        "plugincode": body,
        "disabled": "0",
    })
    if not resp.get("success"):
        raise RuntimeError(f"plugin {name}: {resp.get('message')}")
    plugin_id = resp.get("object", {}).get("id")
    for event in ("OnWebPageInit", "OnBeforeDocFormSave"):
        api(token, {
            "action": "element/plugin/event/update",
            "pluginid": str(plugin_id),
            "event": event,
            "priority": "0",
        })
    print(f"plugin {name} created")


def assign_news_templates(token, blog_tpl_id, news_tpl_id):
    print("news templates: runtime switch via newsPageType in base template")


def main():
    token = login()
    print("logged in")

    for chunk_id, name in CHUNKS.items():
        upsert_chunk(token, chunk_id, name)

    for snippet_id, name in SNIPPETS.items():
        upsert_snippet(token, snippet_id, name)

    base_tpl = (ELEMENTS / "templates/base.tpl.html").read_text(encoding="utf-8")
    tpl_paths = {
        "base": ELEMENTS / "templates/base.tpl.html",
        "vacancy": ELEMENTS / "templates/vacancy.tpl.html",
        "blog": ELEMENTS / "templates/blog.tpl.html",
        "news": ELEMENTS / "templates/news.tpl.html",
    }
    tpl_ids = {}
    for tpl_id, name in TEMPLATES.items():
        path = tpl_paths.get(name)
        if path and path.exists():
            content = path.read_text(encoding="utf-8")
        elif name == "base":
            content = base_tpl
        elif name == "vacancy":
            content = (ELEMENTS / "templates/vacancy.tpl.html").read_text(encoding="utf-8")
        else:
            raise RuntimeError(f"missing template file for {name}")
        tpl_ids[name] = upsert_template(token, tpl_id, name, content)

    upsert_plugin(token, "newsDefaults")
    assign_news_templates(token, tpl_ids["blog"], tpl_ids["news"])

    # ensure remote dirs exist for new content packs
    for folder in ("assets/home/", "assets/services/", "assets/about/partners/", "assets/about/partners/color/"):
        parent = str(Path(folder).parent)
        if parent and not parent.endswith("/"):
            parent += "/"
        api(token, {"action": "browser/directory/create", "parent": parent, "name": Path(folder).name})

    partner_files = sorted(
        p for p in (ROOT / "modxbuild/assets/about/partners").rglob("*")
        if p.is_file() and not p.name.startswith(".") and "_extracted" not in p.parts
    )
    upload_files = list(FILES)
    for partner in partner_files:
        rel = partner.relative_to(ROOT / "modxbuild/assets/about/partners")
        upload_files.append((f"assets/about/partners/{rel.as_posix()}", partner))

    for remote, local in upload_files:
        run([
            "curl", "-sL", "--max-time", "60", "--connect-timeout", "20",
            "-b", COOKIE, "-H", f"modAuth: {token}", "-X", "POST", f"{SITE}/connectors/index.php",
            "--data-urlencode", "action=browser/file/remove",
            "--data-urlencode", f"file={remote}",
        ], check=False)
        # upload with original filename expected by remote path
        upload_name = Path(remote).name
        tmp = Path("/tmp") / upload_name
        tmp.write_bytes(Path(local).read_bytes())
        resp = api(token, {
            "action": "browser/file/upload",
            "path": str(Path(remote).parent) + "/",
        }, files={"file": str(tmp)})
        if not resp.get("success"):
            raise RuntimeError(f"upload {remote}: {resp.get('message')}")
        print(f"uploaded {remote}")

    api(token, {"action": "system/clearcache"})
    print("deploy complete")


if __name__ == "__main__":
    main()
