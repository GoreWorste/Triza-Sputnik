<?php
/**
 * build.php — create MODX elements (chunks, snippets, TVs, template, plugin).
 * Run: php build.php
 */
define('MODX_API_MODE', true);
require '/home/romanov/web/modx.romanovivv.ru/public_html/index.php';
$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('ECHO');

$DIR = __DIR__ . '/elements';

function readBody($file) {
    $s = file_get_contents($file);
    return $s;
}
function readPhp($file) {
    $s = file_get_contents($file);
    // strip leading <?php and trailing ? >
    $s = preg_replace('/^\s*<\?php\s*/', '', $s, 1);
    $s = preg_replace('/\?>\s*$/', '', $s);
    return $s;
}

/* ---------- CHUNKS ---------- */
$chunks = ['head','topbarLogo','footer','mapBlock','copyright','tail','slider','searchBar','pageFooterBands'];
foreach ($chunks as $name) {
    $f = "$DIR/chunks/$name.chunk.html";
    if (!file_exists($f)) { echo "MISS chunk $name\n"; continue; }
    $obj = $modx->getObject('modChunk', ['name' => $name]);
    if (!$obj) { $obj = $modx->newObject('modChunk'); $obj->set('name', $name); }
    $obj->set('snippet', readBody($f));
    $obj->set('category', 0);
    $obj->save();
    echo "chunk: $name\n";
}

/* ---------- SNIPPETS ---------- */
$snippets = ['spMenu','pageTitle','hotVacancies','jobList','jobDetail','newsList','newsDetail','newsPageType','aboutContent','pageProse','trainingsContent','contactsContent','staffingForm'];
foreach ($snippets as $name) {
    $f = "$DIR/snippets/$name.snippet.php";
    if (!file_exists($f)) { echo "MISS snippet $name\n"; continue; }
    $obj = $modx->getObject('modSnippet', ['name' => $name]);
    if (!$obj) { $obj = $modx->newObject('modSnippet'); $obj->set('name', $name); }
    $obj->set('snippet', readPhp($f));
    $obj->set('category', 0);
    $obj->save();
    echo "snippet: $name\n";
}

/* ---------- PLUGIN (vacancyRouter on OnPageNotFound) ---------- */
$pname = 'vacancyRouter';
$pl = $modx->getObject('modPlugin', ['name' => $pname]);
if (!$pl) { $pl = $modx->newObject('modPlugin'); $pl->set('name', $pname); }
$pl->set('plugincode', readPhp("$DIR/snippets/vacancyRouter.snippet.php"));
$pl->set('category', 0);
$pl->save();
// attach event
$evt = $modx->getObject('modPluginEvent', ['pluginid' => $pl->get('id'), 'event' => 'OnPageNotFound']);
if (!$evt) {
    $evt = $modx->newObject('modPluginEvent');
    $evt->set('pluginid', $pl->get('id'));
    $evt->set('event', 'OnPageNotFound');
    $evt->set('priority', 0);
    $evt->set('propertyset', 0);
    $evt->save();
}
echo "plugin: $pname (OnPageNotFound)\n";

/* ---------- PLUGIN vacancyDefaults (auto-set vacancy template) ---------- */
$vp = $modx->getObject('modPlugin', ['name' => 'vacancyDefaults']);
if (!$vp) { $vp = $modx->newObject('modPlugin'); $vp->set('name', 'vacancyDefaults'); }
$vp->set('plugincode', readPhp("$DIR/snippets/vacancyDefaults.snippet.php"));
$vp->set('category', 0);
$vp->save();
$vevt = $modx->getObject('modPluginEvent', ['pluginid' => $vp->get('id'), 'event' => 'OnBeforeDocFormSave']);
if (!$vevt) {
    $vevt = $modx->newObject('modPluginEvent');
    $vevt->set('pluginid', $vp->get('id'));
    $vevt->set('event', 'OnBeforeDocFormSave');
    $vevt->set('priority', 0);
    $vevt->set('propertyset', 0);
    $vevt->save();
}
echo "plugin: vacancyDefaults (OnBeforeDocFormSave)\n";

/* ---------- TEMPLATE base ---------- */
$tpl = $modx->getObject('modTemplate', ['templatename' => 'base']);
if (!$tpl) { $tpl = $modx->newObject('modTemplate'); $tpl->set('templatename', 'base'); }
$tpl->set('content', readBody("$DIR/templates/base.tpl.html"));
$tpl->set('category', 0);
$tpl->save();
$baseTplId = $tpl->get('id');
echo "template: base (id=$baseTplId)\n";

/* ---------- TEMPLATE vacancy (detail = base chrome + jobDetail) ---------- */
$vtplContent = str_replace('[[*content]]', '[[!jobDetail]]', readBody("$DIR/templates/base.tpl.html"));
$vtpl = $modx->getObject('modTemplate', ['templatename' => 'vacancy']);
if (!$vtpl) { $vtpl = $modx->newObject('modTemplate'); $vtpl->set('templatename', 'vacancy'); }
$vtpl->set('content', $vtplContent);
$vtpl->set('category', 0);
$vtpl->save();
$vacTplId = $vtpl->get('id');
echo "template: vacancy (id=$vacTplId)\n";

/* ---------- TEMPLATE blog (news list) ---------- */
$btpl = $modx->getObject('modTemplate', ['templatename' => 'blog']);
if (!$btpl) { $btpl = $modx->newObject('modTemplate'); $btpl->set('templatename', 'blog'); }
$btpl->set('content', readBody("$DIR/templates/blog.tpl.html"));
$btpl->set('category', 0);
$btpl->save();
$blogTplId = $btpl->get('id');
echo "template: blog (id=$blogTplId)\n";

/* ---------- TEMPLATE news (article detail) ---------- */
$ntpl = $modx->getObject('modTemplate', ['templatename' => 'news']);
if (!$ntpl) { $ntpl = $modx->newObject('modTemplate'); $ntpl->set('templatename', 'news'); }
$ntpl->set('content', readBody("$DIR/templates/news.tpl.html"));
$ntpl->set('category', 0);
$ntpl->save();
$newsTplId = $ntpl->get('id');
echo "template: news (id=$newsTplId)\n";

/* ---------- PLUGIN newsDefaults ---------- */
$np = $modx->getObject('modPlugin', ['name' => 'newsDefaults']);
if (!$np) { $np = $modx->newObject('modPlugin'); $np->set('name', 'newsDefaults'); }
$np->set('plugincode', readPhp("$DIR/snippets/newsDefaults.snippet.php"));
$np->set('category', 0);
$np->save();
$nevt = $modx->getObject('modPluginEvent', ['pluginid' => $np->get('id'), 'event' => 'OnBeforeDocFormSave']);
if (!$nevt) {
    $nevt = $modx->newObject('modPluginEvent');
    $nevt->set('pluginid', $np->get('id'));
    $nevt->set('event', 'OnBeforeDocFormSave');
    $nevt->set('priority', 0);
    $nevt->set('propertyset', 0);
    $nevt->save();
}
$wevt = $modx->getObject('modPluginEvent', ['pluginid' => $np->get('id'), 'event' => 'OnWebPageInit']);
if (!$wevt) {
    $wevt = $modx->newObject('modPluginEvent');
    $wevt->set('pluginid', $np->get('id'));
    $wevt->set('event', 'OnWebPageInit');
    $wevt->set('priority', 0);
    $wevt->set('propertyset', 0);
    $wevt->save();
}
echo "plugin: newsDefaults (OnWebPageInit, OnBeforeDocFormSave)\n";

$blogId = (int)$modx->findResource('blog');
if ($blogId) {
    $blogRes = $modx->getObject('modResource', $blogId);
    if ($blogRes) {
        $blogRes->set('template', (int)$blogTplId);
        $blogRes->save();
    }
    foreach ($modx->getCollection('modResource', ['parent' => $blogId, 'deleted' => 0]) as $child) {
        $child->set('template', (int)$newsTplId);
        $child->save();
    }
    echo "news templates assigned under blog=$blogId\n";
}

/* ---------- disable obsolete vacancyRouter plugin ---------- */
$oldpl = $modx->getObject('modPlugin', ['name' => 'vacancyRouter']);
if ($oldpl) {
    foreach ($modx->getCollection('modPluginEvent', ['pluginid' => $oldpl->get('id')]) as $pe) { $pe->remove(); }
    $oldpl->remove();
    echo "plugin vacancyRouter removed (vacancies are real resources now)\n";
}

/* ---------- VACANCY TVs ---------- */
$prefix = $modx->getOption('table_prefix');
$vtvs = [
  ['name'=>'vac_city','caption'=>'Город','type'=>'text','default_value'=>''],
  ['name'=>'vac_category','caption'=>'Категория','type'=>'listbox',
   'elements'=>'@SELECT `name`, `id` FROM `'.$prefix.'triza_categories` WHERE state=1 AND id>1 ORDER BY `name`','default_value'=>''],
  ['name'=>'vac_contract','caption'=>'Тип занятости','type'=>'listbox',
   'elements'=>'Полный рабочий день==0||Временный сотрудник==1||Сменный график==2||Вахта==3','default_value'=>'0'],
  ['name'=>'vac_start','caption'=>'Дата начала','type'=>'text','default_value'=>'Ближайшее время'],
  ['name'=>'vac_salary','caption'=>'Заработная плата','type'=>'richtext','default_value'=>''],
  ['name'=>'vac_tasks','caption'=>'Требования к кандидату','type'=>'richtext','default_value'=>''],
  ['name'=>'vac_profile','caption'=>'Характер выполняемой работы','type'=>'richtext','default_value'=>''],
  ['name'=>'vac_perspective','caption'=>'Условия работы','type'=>'richtext','default_value'=>''],
  ['name'=>'vac_contacts','caption'=>'Контакты (необязательно)','type'=>'richtext','default_value'=>''],
  ['name'=>'vac_homepage','caption'=>'Показывать в «Горячие вакансии» на главной','type'=>'checkbox',
   'elements'=>'Да==1','default_value'=>''],
];
foreach ($vtvs as $t) {
    $tv = $modx->getObject('modTemplateVar', ['name' => $t['name']]);
    if (!$tv) { $tv = $modx->newObject('modTemplateVar'); $tv->set('name', $t['name']); }
    $tv->set('caption', $t['caption']);
    $tv->set('type', $t['type']);
    $tv->set('default_value', $t['default_value']);
    $tv->set('elements', isset($t['elements']) ? $t['elements'] : '');
    $tv->set('category', 0);
    $tv->save();
    $link = $modx->getObject('modTemplateVarTemplate', ['tmplvarid'=>$tv->get('id'),'templateid'=>$vacTplId]);
    if (!$link) {
        $link = $modx->newObject('modTemplateVarTemplate');
        $link->set('tmplvarid', $tv->get('id'));
        $link->set('templateid', $vacTplId);
        $link->save();
    }
    echo "vac tv: {$t['name']}\n";
}

/* ---------- TVs ---------- */
$tvs = [
    ['name'=>'title_mode','caption'=>'Режим заголовка','type'=>'listbox','default_value'=>'breadcrumb',
     'elements'=>'breadcrumb||slider||none'],
    ['name'=>'show_search','caption'=>'Показывать быстрый поиск','type'=>'checkbox','default_value'=>'0','elements'=>'1'],
    ['name'=>'cssClass','caption'=>'Доп. CSS класс body','type'=>'text','default_value'=>''],
];
foreach ($tvs as $t) {
    $tv = $modx->getObject('modTemplateVar', ['name' => $t['name']]);
    if (!$tv) { $tv = $modx->newObject('modTemplateVar'); $tv->set('name', $t['name']); }
    $tv->set('caption', $t['caption']);
    $tv->set('type', $t['type']);
    $tv->set('default_value', $t['default_value']);
    if (isset($t['elements'])) $tv->set('elements', $t['elements']);
    $tv->set('category', 0);
    $tv->save();
    // bind to base template
    $link = $modx->getObject('modTemplateVarTemplate', ['tmplvarid'=>$tv->get('id'),'templateid'=>$baseTplId]);
    if (!$link) {
        $link = $modx->newObject('modTemplateVarTemplate');
        $link->set('tmplvarid', $tv->get('id'));
        $link->set('templateid', $baseTplId);
        $link->save();
    }
    echo "tv: {$t['name']}\n";
}

$modx->cacheManager->refresh();
echo "== BUILD DONE ==\n";
