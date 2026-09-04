<?php
/**
 * One-shot: create/update service pages + site_name. Run on server via CLI.
 */
define('MODX_API_MODE', true);
require '/home/romanov/web/modx.romanovivv.ru/public_html/index.php';
$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('ECHO');

$baseTpl = $modx->getObject('modTemplate', ['templatename' => 'base']);
$baseTplId = $baseTpl ? (int)$baseTpl->get('id') : 2;

$pages = [
    ['Рекрутмент', 'rekrutment', 10, 'Качественный рекрутмент — СПУТНИК-Персонал'],
    ['Региональный подбор', 'regionalnyy-podbor', 11, 'Региональный подбор персонала — СПУТНИК-Персонал'],
    ['Аутстаффинг', 'autstaffing', 12, 'Аутстаффинг и предоставление персонала — СПУТНИК-Персонал'],
    ['Executive Search', 'executive-search', 13, 'Executive search — СПУТНИК-Персонал'],
    ['Хедхантинг', 'khedkhanting', 14, 'Хедхантинг — СПУТНИК-Персонал'],
    ['Домашний персонал', 'domashnij-personal', 15, 'Домашний персонал — СПУТНИК-Персонал'],
];

function upsertPage(modX $modx, $baseTplId, $title, $alias, $menuindex, $longtitle) {
    $res = $modx->getObject('modResource', ['alias' => $alias, 'context_key' => 'web']);
    if (!$res) {
        $res = $modx->newObject('modResource');
        $res->fromArray([
            'pagetitle' => $title,
            'longtitle' => $longtitle,
            'alias' => $alias,
            'published' => 1,
            'deleted' => 0,
            'hidemenu' => 0,
            'isfolder' => 0,
            'template' => $baseTplId,
            'parent' => 0,
            'content' => '',
            'richtext' => 0,
            'searchable' => 1,
            'cacheable' => 0,
            'menuindex' => $menuindex,
            'context_key' => 'web',
            'content_type' => 1,
            'class_key' => 'modDocument',
            'uri_override' => 0,
        ]);
        $res->save();
        echo "created {$alias} id={$res->get('id')}\n";
    } else {
        $res->fromArray([
            'pagetitle' => $title,
            'longtitle' => $longtitle,
            'published' => 1,
            'hidemenu' => 0,
            'template' => $baseTplId,
            'menuindex' => $menuindex,
            'cacheable' => 0,
        ]);
        $res->save();
        echo "updated {$alias} id={$res->get('id')}\n";
    }
    $res->setTVValue('title_mode', 'breadcrumb');
    $res->setTVValue('show_search', '0');
    return $res;
}

foreach ($pages as $p) {
    upsertPage($modx, $baseTplId, $p[0], $p[1], $p[2], $p[3]);
}

$renames = [
    'trainings-and-webinars' => ['Обучение', 'Обучение — СПУТНИК-Персонал'],
    'zapros-na-podbor-personala' => ['Запрос на подбор персонала', 'Запрос на подбор персонала — СПУТНИК-Персонал'],
];
foreach ($renames as $alias => $titles) {
    $res = $modx->getObject('modResource', ['alias' => $alias, 'context_key' => 'web']);
    if (!$res) {
        echo "missing {$alias}\n";
        continue;
    }
    $res->set('pagetitle', $titles[0]);
    $res->set('longtitle', $titles[1]);
    $res->set('cacheable', 0);
    $res->save();
    echo "renamed {$alias}\n";
}

$setting = $modx->getObject('modSystemSetting', ['key' => 'site_name']);
if (!$setting) {
    $setting = $modx->newObject('modSystemSetting');
    $setting->set('key', 'site_name');
    $setting->set('namespace', 'core');
    $setting->set('area', 'site');
}
$setting->set('value', 'Кадровое агентство СПУТНИК-Персонал');
$setting->save();
echo "site_name updated\n";

$modx->cacheManager->refresh();
echo "== DONE ==\n";
