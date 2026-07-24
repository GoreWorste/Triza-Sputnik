<?php
/**
 * import.php — create vacancies table + import all content resources.
 * Run: php import.php
 */
define('MODX_API_MODE', true);
require '/home/romanov/web/modx.romanovivv.ru/public_html/index.php';
$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('ECHO');

$DATA = __DIR__ . '/data';
$prefix = $modx->getOption('table_prefix');
$tbl = $prefix . 'triza_vacancies';

/* ---------- 1) vacancies table ---------- */
$modx->exec("DROP TABLE IF EXISTS {$tbl}");
$modx->exec("CREATE TABLE {$tbl} (
    id INT UNSIGNED NOT NULL PRIMARY KEY,
    category_id INT NOT NULL DEFAULT 0,
    category_name VARCHAR(255) NOT NULL DEFAULT '',
    title VARCHAR(255) NOT NULL DEFAULT '',
    location VARCHAR(255) NOT NULL DEFAULT '',
    contract INT NOT NULL DEFAULT 0,
    contract_label VARCHAR(64) NOT NULL DEFAULT '',
    start_label VARCHAR(64) NOT NULL DEFAULT '',
    salary_html MEDIUMTEXT NULL,
    tasks_html MEDIUMTEXT NULL,
    profile_html MEDIUMTEXT NULL,
    perspective_html MEDIUMTEXT NULL,
    contactinfo_html MEDIUMTEXT NULL,
    homepage TINYINT NOT NULL DEFAULT 0,
    state TINYINT NOT NULL DEFAULT 0,
    ordering INT NOT NULL DEFAULT 0,
    KEY state_idx (state), KEY cat_idx (category_id), KEY hp_idx (homepage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// categories lookup table
$ctbl = $prefix . 'triza_categories';
$modx->exec("DROP TABLE IF EXISTS {$ctbl}");
$modx->exec("CREATE TABLE {$ctbl} (id INT PRIMARY KEY, name VARCHAR(255) NOT NULL DEFAULT '', state TINYINT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$cats = json_decode(file_get_contents("$DATA/categories.json"), true);
$ci = $modx->prepare("INSERT INTO {$ctbl} (id,name,state) VALUES (?,?,?)");
foreach ($cats as $c) { $ci->execute([$c['id'],$c['name'],$c['state']]); }
echo "categories imported: " . count($cats) . "\n";

$vacs = json_decode(file_get_contents("$DATA/vacancies.json"), true);
$ins = $modx->prepare("INSERT INTO {$tbl}
 (id,category_id,category_name,title,location,contract,contract_label,start_label,
  salary_html,tasks_html,profile_html,perspective_html,contactinfo_html,homepage,state,ordering)
 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$n = 0;
foreach ($vacs as $v) {
    $ins->execute([
        $v['id'],$v['category_id'],$v['category_name'],$v['title'],$v['location'],
        $v['contract'],$v['contract_label'],$v['start_label'],
        $v['salary_html'],$v['tasks_html'],$v['profile_html'],$v['perspective_html'],$v['contactinfo_html'],
        $v['homepage'],$v['state'],$v['ordering'],
    ]);
    $n++;
}
echo "vacancies imported: $n\n";

/* ---------- 2) wipe existing resources ---------- */
foreach ($modx->getCollection('modResource') as $r) { $r->remove(); }
echo "cleared old resources\n";

$baseTpl = $modx->getObject('modTemplate', ['templatename' => 'base']);
$baseTplId = $baseTpl ? $baseTpl->get('id') : 0;

/* ---------- 3) pages ---------- */
$pages = json_decode(file_get_contents("$DATA/pages.json"), true);
$aliasToId = [];

function createResource($modx, $baseTplId, $p, $parentId) {
    $res = $modx->newObject('modResource');
    $res->fromArray([
        'pagetitle'   => $p['pagetitle'],
        'longtitle'   => $p['pagetitle'],
        'alias'       => $p['alias'],
        'published'   => 1,
        'deleted'     => 0,
        'hidemenu'    => 1,
        'isfolder'    => isset($p['isfolder']) ? (int)$p['isfolder'] : 0,
        'template'    => $baseTplId,
        'parent'      => $parentId,
        'content'     => $p['content'],
        'richtext'    => 0,
        'searchable'  => 1,
        'cacheable'   => 1,
        'menuindex'   => isset($p['menuindex']) ? (int)$p['menuindex'] : 0,
        'context_key' => 'web',
        'content_type'=> 1,
        'class_key'   => 'modDocument',
    ]);
    $res->save();
    return $res;
}

// pass 1: parents with empty parent_alias
$deferred = [];
foreach ($pages as $p) {
    if (($p['parent_alias'] ?? '') === '') {
        $res = createResource($modx, $baseTplId, $p, 0);
        $aliasToId[$p['alias']] = $res->get('id');
        // set TVs
        $res->setTVValue('title_mode', $p['title_mode']);
        $res->setTVValue('show_search', (string)$p['show_search']);
        echo "page: {$p['alias']} (id={$res->get('id')})\n";
    } else {
        $deferred[] = $p;
    }
}
// pass 2: children
foreach ($deferred as $p) {
    $pid = $aliasToId[$p['parent_alias']] ?? 0;
    $res = createResource($modx, $baseTplId, $p, $pid);
    $aliasToId[$p['alias']] = $res->get('id');
    $res->setTVValue('title_mode', $p['title_mode']);
    $res->setTVValue('show_search', (string)$p['show_search']);
    echo "page(child): {$p['alias']} (id={$res->get('id')}, parent={$pid})\n";
}

/* ---------- 4) news under blog ---------- */
$blogId = $aliasToId['blog'] ?? 0;
$news = json_decode(file_get_contents("$DATA/news.json"), true);
$nn = 0;
foreach ($news as $it) {
    $res = $modx->newObject('modResource');
    $ts = strtotime($it['created']) ?: time();
    $fullTitle = $it['pagetitle'];
    $shortTitle = mb_substr($fullTitle, 0, 180, 'UTF-8');
    $res->fromArray([
        'pagetitle' => $shortTitle,
        'longtitle' => $shortTitle,
        'introtext' => $fullTitle,
        'alias'     => mb_substr($it['alias'], 0, 180, 'UTF-8'),
        'published' => 1, 'deleted' => 0, 'hidemenu' => 1,
        'template'  => $baseTplId, 'parent' => $blogId,
        'content'   => $it['content'], 'richtext' => 0,
        'menuindex' => (int)$it['menuindex'],
        'publishedon' => $ts,
        'context_key' => 'web', 'content_type' => 1, 'class_key' => 'modDocument',
    ]);
    $res->save();
    $res->setTVValue('title_mode', 'breadcrumb');
    $res->setTVValue('show_search', '0');
    $nn++;
}
echo "news imported: $nn (parent blog=$blogId)\n";

/* ---------- 5) system settings ---------- */
function setOpt($modx, $key, $val) {
    $s = $modx->getObject('modSystemSetting', ['key' => $key]);
    if (!$s) { $s = $modx->newObject('modSystemSetting'); $s->set('key', $key); $s->set('namespace','core'); $s->set('area','site'); }
    $s->set('value', $val);
    $s->save();
}
$homeId = $aliasToId['home'] ?? 1;
setOpt($modx, 'site_start', $homeId);
setOpt($modx, 'site_name', 'Кадровое агентство ТРИЗА-Спутник');
setOpt($modx, 'error_page', $homeId);
setOpt($modx, 'unauthorized_page', $homeId);
setOpt($modx, 'friendly_urls', '1');
setOpt($modx, 'friendly_urls_strict', '0');
setOpt($modx, 'use_alias_path', '1');
setOpt($modx, 'container_suffix', '');
setOpt($modx, 'automatic_alias', '1');
setOpt($modx, 'default_template', $baseTplId);
// remove .html extension from text/html content type → clean URLs like /about
$ctype = $modx->getObject('modContentType', ['mime_type' => 'text/html']);
if ($ctype) { $ctype->set('file_extensions', ''); $ctype->save(); echo "content-type ext cleared\n"; }
echo "settings updated (site_start=$homeId)\n";

/* ---------- 6) refresh ---------- */
$modx->cacheManager->refresh();
// rebuild uris
foreach ($modx->getCollection('modResource') as $r) { $r->set('uri_override', 0); $r->save(); }
$modx->cacheManager->refresh();
echo "== IMPORT DONE ==\n";
