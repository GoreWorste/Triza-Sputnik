<?php
/**
 * jobDetail — страница одной вакансии (новый дизайн tz-vd).
 */
$r = $modx->resource;
if (!$r) return '';
$esc = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

$id       = (int)$r->get('id');
$title    = $r->get('pagetitle');
$city     = (string)$r->getTVValue('vac_city');
$contract = (string)$r->getTVValue('vac_contract');
$start    = (string)$r->getTVValue('vac_start');
$salary   = (string)$r->getTVValue('vac_salary');
$tasks    = (string)$r->getTVValue('vac_tasks');
$profile  = (string)$r->getTVValue('vac_profile');
$persp    = (string)$r->getTVValue('vac_perspective');
$contacts = trim((string)$r->getTVValue('vac_contacts'));

$labels = array('0' => 'Полный день', '1' => 'Временный', '2' => 'Сменный график', '3' => 'Вахта');
$cl = isset($labels[$contract]) ? $labels[$contract] : $contract;
if ($start === '') $start = 'Ближайшее время';

$sections = array();
if (trim($tasks) !== '') {
    $sections[] = array('icon' => 'fa-check-circle', 'title' => 'Требования к кандидату', 'html' => $tasks);
}
if (trim($profile) !== '') {
    $sections[] = array('icon' => 'fa-list-ul', 'title' => 'Обязанности', 'html' => $profile);
}
if (trim($persp) !== '') {
    $sections[] = array('icon' => 'fa-star', 'title' => 'Условия работы', 'html' => $persp);
}

$prefix = $modx->getOption('table_prefix');
$sc = $prefix . 'site_content';
$container = (int)$modx->findResource('vacancy');
$related = array();
if ($container) {
    $stmt = $modx->query(
        "SELECT id, pagetitle, alias FROM {$sc}
         WHERE parent = {$container} AND published = 1 AND deleted = 0 AND isfolder = 0 AND id != {$id}
         ORDER BY id DESC LIMIT 4"
    );
    if ($stmt) $related = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$o  = '<article class="tz-vd">';
$o .= '<header class="tz-vd__hero">';
$o .= '<div class="tz-vd__hero-top">';
$o .= '<span class="tz-vd__badge"><i class="fa fa-briefcase" aria-hidden="true"></i> Вакансия</span>';
$o .= '<a href="/vacancy" class="tz-vd__back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Все вакансии</a>';
$o .= '</div>';
$o .= '<h1 class="tz-vd__title">' . $esc($title) . '</h1>';
$o .= '<ul class="tz-vd__meta">';
$o .= '<li><i class="fa fa-clock-o" aria-hidden="true"></i><span>' . $esc($cl) . '</span></li>';
$o .= '<li><i class="fa fa-map-marker" aria-hidden="true"></i><span>' . $esc($city) . '</span></li>';
$o .= '<li><i class="fa fa-calendar" aria-hidden="true"></i><span>' . $esc($start) . '</span></li>';
$o .= '</ul>';
if (trim(strip_tags($salary)) !== '') {
    $o .= '<div class="tz-vd__salary"><i class="fa fa-rub" aria-hidden="true"></i><div class="tz-vd__salary-text">' . $salary . '</div></div>';
}
$o .= '</header>';

$o .= '<div class="tz-vd__layout">';
$o .= '<div class="tz-vd__main">';

if ($sections) {
    foreach ($sections as $i => $sec) {
        $num = str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT);
        $o .= '<section class="tz-vd__section">';
        $o .= '<div class="tz-vd__section-head">';
        $o .= '<span class="tz-vd__section-num">' . $num . '</span>';
        $o .= '<h2 class="tz-vd__section-title"><i class="fa ' . $esc($sec['icon']) . '" aria-hidden="true"></i> ' . $esc($sec['title']) . '</h2>';
        $o .= '</div>';
        $o .= '<div class="tz-vd__section-body tz-prose">' . $sec['html'] . '</div>';
        $o .= '</section>';
    }
} else {
    $o .= '<p class="tz-vd__empty">Подробное описание вакансии уточняйте у менеджера.</p>';
}

$o .= '</div>';

$o .= '<aside class="tz-vd__aside">';
$o .= '<div class="tz-vd__card">';
$o .= '<h2 class="tz-vd__card-title">Откликнуться</h2>';
$o .= '<p class="tz-vd__card-lead">Позвоните или напишите — поможем с трудоустройством</p>';
if ($contacts !== '') {
    $o .= '<div class="tz-vd__contacts tz-prose">' . $contacts . '</div>';
}
$o .= '<a href="tel:+79637112724" class="tz-btn tz-btn--glow tz-btn--block"><i class="fa fa-phone" aria-hidden="true"></i> 8 (963) 711-27-24</a>';
$o .= '<a href="mailto:sputnik.personal@yandex.ru" class="tz-btn tz-btn--line tz-btn--block"><i class="fa fa-envelope" aria-hidden="true"></i> Написать на почту</a>';
$o .= '</div>';
$o .= '</aside>';
$o .= '</div>';

if ($related) {
    $o .= '<section class="tz-vd__related">';
    $o .= '<div class="tz-vd__related-head">';
    $o .= '<h2 class="tz-vd__related-title">Другие вакансии</h2>';
    $o .= '<a href="/vacancy" class="tz-link-arrow">Все позиции <i class="fa fa-arrow-right" aria-hidden="true"></i></a>';
    $o .= '</div>';
    $o .= '<div class="tz-vd__related-grid">';
    foreach ($related as $item) {
        $link = '/vacancy/' . rawurlencode($item['alias']);
        $o .= '<a class="tz-vd__related-item" href="' . $link . '">';
        $o .= '<span class="tz-vd__related-name">' . $esc($item['pagetitle']) . '</span>';
        $o .= '<span class="tz-vd__related-go"><i class="fa fa-arrow-right" aria-hidden="true"></i></span>';
        $o .= '</a>';
    }
    $o .= '</div></section>';
}

$o .= '</article>';
return $o;
