<?php
/**
 * jobList — vacancy listing with filters.
 */
$prefix = $modx->getOption('table_prefix');
$ctbl = $prefix . 'triza_categories';
$esc = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

$container = $modx->resource ? (int)$modx->resource->get('id') : 0;
$excludeAliases = array('test', '123', 'кизатыр', 'электрогазосварщик', 'щзркмийвмийущм', 'башкиртостанщик');

$fCat = isset($_GET['filter_jobcategory']) ? trim($_GET['filter_jobcategory']) : '';
$fLoc = isset($_GET['filter_joblocation']) ? trim($_GET['filter_joblocation']) : '';
$fSearch = isset($_GET['filter_search']) ? trim($_GET['filter_search']) : '';

$sc = $prefix . 'site_content';
$stmt = $modx->query("SELECT id, pagetitle, alias FROM {$sc}
    WHERE parent = {$container} AND published = 1 AND deleted = 0 AND isfolder = 0");
$rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();
$byId = array();
$ids = array();
foreach ($rows as $r) { $byId[$r['id']] = $r + array('tv' => array()); $ids[] = (int)$r['id']; }

if ($ids) {
    $cvt = $prefix . 'site_tmplvar_contentvalues';
    $tvt = $prefix . 'site_tmplvars';
    $in = implode(',', $ids);
    $ts = $modx->query("SELECT cv.contentid, tv.name, cv.value FROM {$cvt} cv JOIN {$tvt} tv ON tv.id = cv.tmplvarid
        WHERE cv.contentid IN ({$in}) AND tv.name LIKE 'vac_%'");
    if ($ts) foreach ($ts->fetchAll(PDO::FETCH_ASSOC) as $t) {
        if (isset($byId[$t['contentid']])) $byId[$t['contentid']]['tv'][$t['name']] = $t['value'];
    }
}

$catNames = array();
$cs0 = $modx->query("SELECT id, name FROM {$ctbl}");
if ($cs0) foreach ($cs0->fetchAll(PDO::FETCH_ASSOC) as $c) { $catNames[(string)$c['id']] = $c['name']; }

$labels = array('0'=>'Полный рабочий день','1'=>'Временный сотрудник','2'=>'Сменный график','3'=>'Вахта');
$items = array();
foreach ($byId as $rid => $r) {
    if (in_array(mb_strtolower((string)$r['alias'], 'UTF-8'), $excludeAliases, true)) continue;
    $tv = $r['tv'];
    $cat = isset($tv['vac_category']) ? (string)$tv['vac_category'] : '';
    $city = isset($tv['vac_city']) ? (string)$tv['vac_city'] : '';
    $contract = isset($tv['vac_contract']) ? (string)$tv['vac_contract'] : '0';
    $start = isset($tv['vac_start']) && trim($tv['vac_start']) !== '' ? $tv['vac_start'] : 'Ближайшее время';

    if ($fCat !== '' && $cat !== $fCat) continue;
    if ($fLoc !== '' && $city !== $fLoc) continue;
    if ($fSearch !== '') {
        $hay = $r['pagetitle'] . ' ' . $city . ' ' . (isset($catNames[$cat]) ? $catNames[$cat] : '')
             . ' ' . (isset($tv['vac_salary']) ? $tv['vac_salary'] : '')
             . ' ' . (isset($tv['vac_tasks']) ? $tv['vac_tasks'] : '')
             . ' ' . (isset($tv['vac_profile']) ? $tv['vac_profile'] : '')
             . ' ' . (isset($tv['vac_perspective']) ? $tv['vac_perspective'] : '');
        if (mb_stripos($hay, $fSearch, 0, 'UTF-8') === false) continue;
    }
    $items[] = array(
        'id' => (int)$rid,
        'alias' => $r['alias'],
        'title' => $r['pagetitle'],
        'city' => $city,
        'cl' => isset($labels[$contract]) ? $labels[$contract] : $contract,
        'start' => $start,
        'salary' => isset($tv['vac_salary']) ? $tv['vac_salary'] : '',
    );
}
usort($items, function ($a, $b) {
    $ka = ctype_digit((string)$a['alias']) ? (int)$a['alias'] : (int)$a['id'];
    $kb = ctype_digit((string)$b['alias']) ? (int)$b['alias'] : (int)$b['id'];
    return $kb - $ka;
});

$catOpts = '<option value="">Все категории</option>';
$cs = $modx->query("SELECT id, name FROM {$ctbl} WHERE state=1 AND id>1 ORDER BY name ASC");
if ($cs) foreach ($cs->fetchAll(PDO::FETCH_ASSOC) as $c) {
    $sel = ((string)$c['id'] === $fCat) ? ' selected' : '';
    $catOpts .= '<option value="' . (int)$c['id'] . '"' . $sel . '>' . $esc($c['name']) . '</option>';
}

$locs = array();
foreach ($byId as $r) { $c = isset($r['tv']['vac_city']) ? trim($r['tv']['vac_city']) : ''; if ($c !== '') $locs[$c] = 1; }
$locList = array_keys($locs);
sort($locList, SORT_STRING | SORT_FLAG_CASE);
$locOpts = '<option value="">Все города</option>';
foreach ($locList as $c) {
    $sel = ($c === $fLoc) ? ' selected' : '';
    $locOpts .= '<option value="' . $esc($c) . '"' . $sel . '>' . $esc($c) . '</option>';
}

$form  = '<div class="tz-command tz-command--page" id="tz-vacancy-search">';
$form .= '<div class="tz-command__box">';
$form .= '<div class="tz-command__head"><span class="tz-label">Фильтр</span><p>Найдено: <strong>' . count($items) . '</strong></p></div>';
$form .= '<form action="/vacancy" method="get" name="jobokSearchForm" id="jobokSearchForm" class="tz-command__form">';
$form .= '<div id="filter-bar" class="tz-command__fields">';
$form .= '<div class="tz-command__field"><label class="tz-command__label" for="filter_jobcategory">Категория</label>';
$form .= '<select name="filter_jobcategory" id="filter_jobcategory" class="tz-command__input" onchange="this.form.submit()">' . $catOpts . '</select></div>';
$form .= '<div class="tz-command__field"><label class="tz-command__label" for="filter_joblocation">Город</label>';
$form .= '<select name="filter_joblocation" id="filter_joblocation" class="tz-command__input" onchange="this.form.submit()">' . $locOpts . '</select></div>';
$form .= '<div class="tz-command__field tz-command__field--grow"><label class="tz-command__label" for="filter_search">Поиск</label>';
$form .= '<input type="text" name="filter_search" id="filter_search" class="tz-command__input" placeholder="Ключевые слова…" value="' . $esc($fSearch) . '"></div>';
$form .= '<div class="tz-command__field tz-command__field--btns"><span class="tz-command__label tz-command__label--hide">Действия</span>';
$form .= '<div class="tz-command__btns">';
$form .= '<button class="tz-btn tz-btn--glow" type="submit" id="jobokay_search_submit">Найти</button>';
$form .= '<button class="tz-btn tz-btn--line tz-btn--sm" type="button" id="jobokay_search_clear" onclick="window.location.href=\'/vacancy\'">×</button>';
$form .= '</div></div>';
$form .= '</div></form></div></div>';

$list = '<div class="tz-feed">';
if (!$items) {
    $list .= '<div class="tz-empty"><p>По вашему запросу вакансий не найдено. Попробуйте изменить фильтры.</p></div>';
}
foreach ($items as $i => $it) {
    $link = '/vacancy/' . rawurlencode($it['alias']);
    $list .= '<a class="tz-feed__row tz-reveal" href="' . $link . '" style="--tz-delay:' . ($i % 10 * 0.04) . 's">';
    $list .= '<span class="tz-feed__num">' . str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) . '</span>';
    $list .= '<div class="tz-feed__body">';
    $list .= '<h3 class="tz-feed__title">' . $esc($it['title']) . '</h3>';
    $list .= '<p class="tz-feed__meta">' . $esc($it['city']) . ' · ' . $esc($it['cl']) . ' · ' . $esc($it['start']) . '</p>';
    $list .= '</div>';
    if (trim($it['salary']) !== '') {
        $list .= '<span class="tz-feed__pay">' . $it['salary'] . '</span>';
    }
    $list .= '<span class="tz-feed__arrow">→</span>';
    $list .= '</a>';
}
$list .= '</div>';

return $form . $list;
