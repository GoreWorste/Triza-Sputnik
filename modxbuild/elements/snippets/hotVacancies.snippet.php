<?php
$prefix = $modx->getOption('table_prefix');
$sc  = $prefix . 'site_content';
$cvt = $prefix . 'site_tmplvar_contentvalues';
$tvt = $prefix . 'site_tmplvars';
$esc = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

$container = $modx->findResource('vacancy');
$excludeAliases = array('test', '123', 'кизатыр', 'электрогазосварщик', 'щзркмийвмийущм', 'башкиртостанщик');

$sql = "SELECT c.id, c.pagetitle, c.alias FROM {$sc} c
        WHERE c.parent = " . (int)$container . " AND c.published = 1 AND c.deleted = 0 AND c.isfolder = 0
        ORDER BY c.id DESC LIMIT 20";
$stmt = $modx->query($sql);
$allRows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();
$rows = array();
foreach ($allRows as $r) {
    if (in_array(mb_strtolower((string)$r['alias'], 'UTF-8'), $excludeAliases, true)) continue;
    $rows[] = $r;
    if (count($rows) >= 4) break;
}

$ids = array();
foreach ($rows as $r) $ids[] = (int)$r['id'];
$tvmap = array();
if ($ids) {
    $in = implode(',', $ids);
    $ts = $modx->query("SELECT cv.contentid, tv.name, cv.value FROM {$cvt} cv
        JOIN {$tvt} tv ON tv.id = cv.tmplvarid
        WHERE cv.contentid IN ({$in}) AND tv.name IN ('vac_city','vac_contract','vac_salary')");
    if ($ts) foreach ($ts->fetchAll(PDO::FETCH_ASSOC) as $t) {
        $tvmap[$t['contentid']][$t['name']] = $t['value'];
    }
}
$labels = array('0'=>'Полный день','1'=>'Временный','2'=>'Сменный','3'=>'Вахта');

$out = '<div class="tz-bento">';
foreach ($rows as $i => $r) {
    $id = (int)$r['id'];
    $tv = isset($tvmap[$id]) ? $tvmap[$id] : array();
    $link = '/vacancy/' . rawurlencode($r['alias']);
    $contract = isset($tv['vac_contract']) ? (string)$tv['vac_contract'] : '0';
    $feat = $i === 0 ? ' tz-bento__item--hero' : '';
    $out .= '<a class="tz-bento__item tz-reveal' . $feat . '" href="' . $link . '" style="--tz-delay:' . ($i * 0.07) . 's">';
    $out .= '<span class="tz-bento__idx">0' . ($i + 1) . '</span>';
    $out .= '<span class="tz-bento__tag">' . $esc(isset($labels[$contract]) ? $labels[$contract] : $contract) . '</span>';
    $out .= '<h3 class="tz-bento__name">' . $esc($r['pagetitle']) . '</h3>';
    $out .= '<p class="tz-bento__loc"><i class="fa fa-map-marker"></i> ' . $esc(isset($tv['vac_city']) ? $tv['vac_city'] : '') . '</p>';
    if (!empty($tv['vac_salary'])) {
        $out .= '<p class="tz-bento__pay">' . $tv['vac_salary'] . '</p>';
    }
    $out .= '<span class="tz-bento__go">Подробнее →</span>';
    $out .= '</a>';
}
$out .= '</div>';
return $out;
