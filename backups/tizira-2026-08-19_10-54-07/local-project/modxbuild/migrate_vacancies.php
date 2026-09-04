<?php
/**
 * migrate_vacancies.php — turn table rows into MODX resources (template "vacancy")
 * so they are fully editable in the manager. Active -> children of "Вакансии".
 * Archived -> children of an "Архив вакансий" folder. Idempotent.
 */
define('MODX_API_MODE', true);
require '/home/romanov/web/modx.romanovivv.ru/public_html/index.php';
$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('ECHO');

$DATA = __DIR__ . '/data';
$prefix = $modx->getOption('table_prefix');
$cvt = $prefix . 'site_tmplvar_contentvalues';

$container = (int)$modx->findResource('vacancy');
if (!$container) { echo "ERROR: no 'vacancy' container\n"; return; }
$vtpl = $modx->getObject('modTemplate', ['templatename' => 'vacancy']);
if (!$vtpl) { echo "ERROR: no 'vacancy' template (run build.php first)\n"; return; }
$vacTplId = (int)$vtpl->get('id');
$baseTpl = $modx->getObject('modTemplate', ['templatename' => 'base']);
$baseTplId = $baseTpl ? (int)$baseTpl->get('id') : $vacTplId;

/* ---- TV name -> id map ---- */
$tvId = [];
foreach ($modx->getCollection('modTemplateVar') as $tv) {
    if (strpos($tv->get('name'), 'vac_') === 0) $tvId[$tv->get('name')] = (int)$tv->get('id');
}

/* ---- archive folder (create or find) ---- */
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
echo "archive folder id = $archId\n";

/* ---- wipe existing vacancy resources (children of container or archive), keep archive folder ---- */
$old = $modx->getCollection('modResource', ['parent:IN' => [$container, $archId]]);
$oldIds = [];
foreach ($old as $o) { if ((int)$o->get('id') !== $archId) $oldIds[] = (int)$o->get('id'); }
if ($oldIds) {
    $in = implode(',', $oldIds);
    $modx->exec("DELETE FROM {$cvt} WHERE contentid IN ({$in})");
    $modx->exec("DELETE FROM {$prefix}site_content WHERE id IN ({$in})");
    echo "removed " . count($oldIds) . " old vacancy resources\n";
}

/* ---- create resources ---- */
$vacs = json_decode(file_get_contents("$DATA/vacancies.json"), true);
$insCV = $modx->prepare("INSERT INTO {$cvt} (tmplvarid, contentid, value) VALUES (?,?,?)");

$created = 0; $archived = 0;
$fieldMap = [
    'vac_city'        => 'location',
    'vac_category'    => 'category_id',
    'vac_contract'    => 'contract',
    'vac_start'       => 'start_label',
    'vac_salary'      => 'salary_html',
    'vac_tasks'       => 'tasks_html',
    'vac_profile'     => 'profile_html',
    'vac_perspective' => 'perspective_html',
    'vac_contacts'    => 'contactinfo_html',
];
foreach ($vacs as $v) {
    $isActive = ((int)$v['state'] === 1);
    $parent = $isActive ? $container : $archId;
    $ts = strtotime($v['created']);
    if ($ts === false || $ts < 0 || $ts > 2147483647) $ts = time();
    $res = $modx->newObject('modResource');
    $res->fromArray([
        'pagetitle'  => mb_substr($v['title'], 0, 180, 'UTF-8'),
        'longtitle'  => mb_substr($v['title'], 0, 180, 'UTF-8'),
        'alias'      => (string)$v['id'],
        'published'  => $isActive ? 1 : 0,
        'deleted'    => 0,
        'hidemenu'   => 1,
        'isfolder'   => 0,
        'template'   => $vacTplId,
        'parent'     => $parent,
        'content'    => '',
        'richtext'   => 0,
        'searchable' => 1,
        'cacheable'  => 1,
        'menuindex'  => 0,
        'publishedon'=> $ts,
        'context_key'=> 'web',
        'content_type'=> 1,
        'class_key'  => 'modDocument',
    ]);
    $res->save();
    $rid = (int)$res->get('id');

    // TV values (direct insert)
    foreach ($fieldMap as $tvName => $vk) {
        if (!isset($tvId[$tvName])) continue;
        $val = (string)$v[$vk];
        if ($tvName === 'vac_contract') { $val = (string)(int)$v['contract']; }
        if ($tvName === 'vac_category') { if ((int)$v['category_id'] <= 0) continue; $val = (string)(int)$v['category_id']; }
        if ($val === '' && $tvName !== 'vac_contract') continue;
        $insCV->execute([$tvId[$tvName], $rid, $val]);
    }
    if (!empty($v['homepage']) && isset($tvId['vac_homepage'])) {
        $insCV->execute([$tvId['vac_homepage'], $rid, '1']);
    }

    if ($isActive) $created++; else $archived++;
}
echo "created active: $created, archived: $archived\n";

/* ---- rebuild uris + cache ---- */
$ct = $modx->getObject('modContentType', ['mime_type' => 'text/html']);
if ($ct) { $ct->set('file_extensions', ''); $ct->save(); }
foreach ($modx->getCollection('modResource', ['parent:IN' => [$container, $archId]]) as $r) {
    $r->set('uri', ''); $r->set('uri_override', 0); $r->save();
}
$modx->cacheManager->refresh();
echo "== MIGRATION DONE ==\n";
