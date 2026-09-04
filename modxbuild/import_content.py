#!/usr/bin/env python3
"""Import news, trainings, vacancies from backup DB + triza_vacancies table.

Uses MODX API (resource/create) and a temporary importRunner snippet on the server
to run migrate_vacancies logic (PHP upload is blocked on hosting).

Run: python3 modxbuild/import_content.py
"""
from __future__ import annotations

import json
import os
import re
import subprocess
import sys
import time
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
BACKUP_RES = ROOT / "backups/tizira-2026-07-23_21-13-32/modx-resources"
SITE = os.environ.get("MODX_SITE", "https://modx.romanovivv.ru")
COOKIE = "/tmp/tizira_import_content.txt"
MGR_HTML = "/tmp/tizira_import_content_mgr.html"

NEWS_EXCLUDE = {"test", "пэдийуаи", "бутуту", "жлпижмцкломосывзщостэ", "еуые", "lenta-novostej", "sertifikat-sto"}
VAC_EXCLUDE = {"test", "123", "кизатыр", "электрогазосварщик", "щзркмийвмийущм", "башкиртостанщик"}

MIGRATE_SNIPPET = r'''
$token = isset($_GET['import_token']) ? (string)$_GET['import_token'] : '';
if ($token !== 'tizira-import-2026') return 'forbidden';

$prefix = $modx->getOption('table_prefix');
$cvt = $prefix . 'site_tmplvar_contentvalues';
$container = (int)$modx->findResource('vacancy');
if (!$container) return 'no vacancy container';

$vtpl = $modx->getObject('modTemplate', ['templatename' => 'vacancy']);
$baseTpl = $modx->getObject('modTemplate', ['templatename' => 'base']);
if (!$vtpl || !$baseTpl) return 'no templates';
$vacTplId = (int)$vtpl->get('id');
$baseTplId = (int)$baseTpl->get('id');

$tvId = [];
foreach ($modx->getCollection('modTemplateVar') as $tv) {
    if (strpos($tv->get('name'), 'vac_') === 0) $tvId[$tv->get('name')] = (int)$tv->get('id');
}

$arch = $modx->getObject('modResource', ['alias' => 'arhiv', 'parent' => $container]);
if (!$arch) {
    $arch = $modx->newObject('modResource');
    $arch->fromArray([
        'pagetitle' => 'Архив вакансий', 'alias' => 'arhiv', 'published' => 0, 'deleted' => 0,
        'hidemenu' => 1, 'isfolder' => 1, 'template' => $baseTplId, 'parent' => $container,
        'content' => '', 'richtext' => 0, 'context_key' => 'web', 'content_type' => 1,
        'class_key' => 'modDocument',
    ]);
    $arch->save();
}
$archId = (int)$arch->get('id');

$old = $modx->getCollection('modResource', ['parent:IN' => [$container, $archId]]);
$oldIds = [];
foreach ($old as $o) {
    $id = (int)$o->get('id');
    if ($id !== $archId) $oldIds[] = $id;
}
if ($oldIds) {
    $in = implode(',', $oldIds);
    $modx->exec("DELETE FROM {$cvt} WHERE contentid IN ({$in})");
    $modx->exec("DELETE FROM {$prefix}site_content WHERE id IN ({$in})");
}

$tbl = $prefix . 'triza_vacancies';
$stmt = $modx->query("SELECT * FROM {$tbl} ORDER BY id ASC");
$vacs = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
if (!$vacs) return 'triza_vacancies empty';

$insCV = $modx->prepare("INSERT INTO {$cvt} (tmplvarid, contentid, value) VALUES (?,?,?)");
$exclude = array('test','123','кизатыр','электрогазосварщик','щзркмийвмийущм','башкиртостанщик');
$created = 0; $archived = 0;
$fieldMap = [
    'vac_city' => 'location', 'vac_category' => 'category_id', 'vac_contract' => 'contract',
    'vac_start' => 'start_label', 'vac_salary' => 'salary_html', 'vac_tasks' => 'tasks_html',
    'vac_profile' => 'profile_html', 'vac_perspective' => 'perspective_html', 'vac_contacts' => 'contactinfo_html',
];

foreach ($vacs as $v) {
    $alias = (string)$v['id'];
    if (in_array(mb_strtolower($alias, 'UTF-8'), $exclude, true)) continue;
    $isActive = ((int)$v['state'] === 1);
    $parent = $isActive ? $container : $archId;
    $ts = time();
    $res = $modx->newObject('modResource');
    $res->fromArray([
        'pagetitle' => mb_substr($v['title'], 0, 180, 'UTF-8'),
        'longtitle' => mb_substr($v['title'], 0, 180, 'UTF-8'),
        'alias' => $alias,
        'published' => $isActive ? 1 : 0,
        'deleted' => 0, 'hidemenu' => 1, 'isfolder' => 0,
        'template' => $vacTplId, 'parent' => $parent,
        'content' => '', 'richtext' => 0, 'searchable' => 1, 'cacheable' => 1,
        'menuindex' => 0, 'publishedon' => $ts,
        'context_key' => 'web', 'content_type' => 1, 'class_key' => 'modDocument',
    ]);
    $res->save();
    $rid = (int)$res->get('id');
    foreach ($fieldMap as $tvName => $vk) {
        if (!isset($tvId[$tvName])) continue;
        $val = (string)($v[$vk] ?? '');
        if ($tvName === 'vac_contract') $val = (string)(int)$v['contract'];
        if ($tvName === 'vac_category') {
            if ((int)$v['category_id'] <= 0) continue;
            $val = (string)(int)$v['category_id'];
        }
        if ($val === '' && $tvName !== 'vac_contract') continue;
        $insCV->execute([$tvId[$tvName], $rid, $val]);
    }
    if (!empty($v['homepage']) && isset($tvId['vac_homepage'])) {
        $insCV->execute([$tvId['vac_homepage'], $rid, '1']);
    }
    if ($isActive) $created++; else $archived++;
}

foreach ($modx->getCollection('modResource', ['parent:IN' => [$container, $archId]]) as $r) {
    if ((int)$r->get('id') === $archId) continue;
    $r->set('uri', ''); $r->set('uri_override', 0); $r->save();
}
$modx->cacheManager->refresh();
return "vacancies migrated: active={$created}, archived={$archived}, total=" . count($vacs);
'''


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


def run(cmd: list[str], check: bool = True) -> subprocess.CompletedProcess[str]:
    return subprocess.run(cmd, check=check, capture_output=True, text=True)


def api(token: str, data: dict | None = None) -> dict:
    cmd = [
        "curl", "-sL", "-b", COOKIE, "-H", f"modAuth: {token}",
        "-X", "POST", f"{SITE}/connectors/index.php", "--max-time", "120",
    ]
    for k, v in (data or {}).items():
        cmd += ["--data-urlencode", f"{k}={v}"]
    raw = run(cmd).stdout
    if not raw.strip():
        return {"success": False, "message": "empty response"}
    try:
        return json.loads(raw)
    except json.JSONDecodeError:
        return {"success": False, "message": raw[:300]}


def login() -> str:
    users = []
    u1, p1 = os.environ.get("MODX_ADMIN_USER", ""), os.environ.get("MODX_ADMIN_PASS", "")
    u2, p2 = os.environ.get("MODX_ADMIN2_USER", "admin2"), os.environ.get("MODX_ADMIN2_PASS", "")
    if u1 and p1:
        users.append((u1, p1))
    if u2 and p2:
        users.append((u2, p2))
    for user, password in users:
        run([
            "curl", "-sL", "-c", COOKIE, "-X", "POST", f"{SITE}/manager/",
            "-d", f"login_context=mgr&username={user}&password={password}&rememberme=1&login=1",
            "-o", MGR_HTML,
        ])
        html = Path(MGR_HTML).read_text(encoding="utf-8", errors="ignore")
        m = re.search(r"HTTP_MODAUTH=([^\"]+)", html)
        if m and "Dashboard" in html:
            print(f"logged in as {user}")
            return m.group(1)
    raise RuntimeError("MODX login failed")


def scan_aliases(token: str) -> dict[str, int]:
    by_alias: dict[str, int] = {}
    for rid in list(range(1030, 1060)) + list(range(1, 200)):
        resp = api(token, {"action": "resource/get", "id": str(rid)})
        if not resp.get("success"):
            continue
        obj = resp.get("object") or {}
        if obj.get("deleted"):
            continue
        alias = obj.get("alias")
        if alias:
            by_alias[alias] = int(obj["id"])
    return by_alias


def create_child(token: str, parent_id: int, obj: dict, template: str = "2") -> int | None:
    alias = obj["alias"]
    payload = {
        "action": "resource/create",
        "pagetitle": obj.get("pagetitle", alias),
        "longtitle": obj.get("longtitle", obj.get("pagetitle", alias)),
        "alias": alias,
        "parent": str(parent_id),
        "template": template,
        "published": "1" if obj.get("published", True) else "0",
        "hidemenu": "1",
        "isfolder": "0",
        "richtext": "0",
        "searchable": "1",
        "cacheable": "1",
        "content": obj.get("content") or "",
        "menuindex": str(obj.get("menuindex", 0)),
        "class_key": "modDocument",
        "context_key": "web",
        "uri_override": "1",
    }
    if obj.get("publishedon"):
        payload["publishedon"] = obj["publishedon"]
    resp = api(token, payload)
    if resp.get("success"):
        rid = int((resp.get("object") or {}).get("id") or 0)
        print(f"  created {alias} id={rid}")
        return rid
    msg = resp.get("message") or ""
    data = resp.get("data") or []
    if data:
        msg = "; ".join(str(x.get("msg", x)) for x in data)
    if "already using the URI" in msg or "already exists" in msg.lower():
        print(f"  skip {alias} (exists)")
        return None
    print(f"  FAIL create {alias}: {msg}", file=sys.stderr)
    return None


def import_from_backup(token: str, aliases: dict[str, int]) -> None:
    if not BACKUP_RES.is_dir():
        raise RuntimeError(f"backup not found: {BACKUP_RES}")

    blog_id = aliases.get("blog")
    train_id = aliases.get("trainings-and-webinars")
    home_id = aliases.get("home")
    if not blog_id or not train_id:
        raise RuntimeError("blog or trainings container missing — run fix_site_db.py first")

    existing = set(aliases.keys())
    news_n = train_n = cert_n = 0

    for path in sorted(BACKUP_RES.glob("*.json")):
        obj = json.loads(path.read_text(encoding="utf-8"))
        alias = obj.get("alias", "")
        parent = obj.get("parent")
        uri = obj.get("uri", "")

        if parent == 105:
            if alias in NEWS_EXCLUDE or alias in existing:
                continue
            if create_child(token, blog_id, obj):
                news_n += 1
                existing.add(alias)
        elif uri.startswith("trainings-and-webinars/") and parent not in (0, 116):
            if alias in existing:
                continue
            if create_child(token, train_id, obj):
                train_n += 1
                existing.add(alias)
        elif uri == "home/sertifikat-sto" and home_id and alias not in existing:
            if create_child(token, home_id, obj):
                cert_n += 1
                existing.add(alias)

    print(f"imported news={news_n}, trainings={train_n}, cert={cert_n}")


def ensure_import_runner(token: str) -> tuple[int, int]:
    """Returns (snippet_id, page_id)."""
    snippet_id = None
    for sid in range(1, 50):
        resp = api(token, {"action": "element/snippet/get", "id": str(sid)})
        if resp.get("success") and (resp.get("object") or {}).get("name") == "importRunner":
            snippet_id = sid
            break
    if not snippet_id:
        resp = api(token, {"action": "element/snippet/create", "name": "importRunner", "snippet": "return 'init';", "locked": "0"})
        snippet_id = int((resp.get("object") or {}).get("id") or 0)

    page_id = None
    aliases = scan_aliases(token)
    if "import-runner-tmp" in aliases:
        page_id = aliases["import-runner-tmp"]
    else:
        resp = api(token, {
            "action": "resource/create",
            "pagetitle": "Import",
            "alias": "import-runner-tmp",
            "parent": "0",
            "template": "2",
            "published": "1",
            "hidemenu": "1",
            "content": "[[!importRunner]]",
            "class_key": "modDocument",
            "context_key": "web",
            "uri": "import-runner-tmp",
            "uri_override": "1",
        })
        page_id = int((resp.get("object") or {}).get("id") or 0)

    return snippet_id, page_id


def migrate_vacancies(token: str, snippet_id: int) -> str:
    api(token, {
        "action": "element/snippet/update",
        "id": str(snippet_id),
        "name": "importRunner",
        "snippet": MIGRATE_SNIPPET.strip(),
        "locked": "0",
    })
    html = run(["curl", "-sL", f"{SITE}/import-runner-tmp?import_token=tizira-import-2026"], check=False).stdout
    m = re.search(r'tz-prose">\s*([^<]+)\s*</div>', html)
    result = m.group(1).strip() if m else "unknown (check page manually)"
    print(f"migrate: {result}")
    return result


def cleanup_test_resources(token: str) -> None:
    aliases = scan_aliases(token)
    for alias in ("test-vac-999", "test-vac-998", "import-runner-tmp"):
        rid = aliases.get(alias)
        if rid:
            api(token, {"action": "resource/delete", "id": str(rid)})
            print(f"deleted {alias} id={rid}")


def main() -> None:
    token = login()
    aliases = scan_aliases(token)
    print(f"containers: home={aliases.get('home')} blog={aliases.get('blog')} vacancy={aliases.get('vacancy')}")

    import_from_backup(token, aliases)

    snippet_id, _ = ensure_import_runner(token)
    migrate_vacancies(token, snippet_id)

    api(token, {"action": "element/snippet/remove", "id": str(snippet_id)})
    print("removed importRunner snippet")

    cleanup_test_resources(token)
    api(token, {"action": "system/clearcache"})
    print("import_content complete")


if __name__ == "__main__":
    main()
