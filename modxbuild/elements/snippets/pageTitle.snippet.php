<?php
$mode = 'breadcrumb';
$title = '';
if ($modx->resource) {
    $tv = $modx->resource->getTVValue('title_mode');
    if ($tv !== null && $tv !== '') $mode = $tv;
    $rid = (int)$modx->resource->get('id');
    $siteStart = (int)$modx->config['site_start'];
    $alias = (string)$modx->resource->get('alias');
    if ($rid === $siteStart || $alias === 'home') {
        $mode = 'slider';
    }
    $title = $modx->resource->get('pagetitle');
    $intro = $modx->resource->get('introtext');
    if ($intro !== null && trim($intro) !== '' && $modx->resource->get('parent')) {
        $title = $intro;
    }
}
if ($mode === 'none') return '';
if ($mode === 'slider') return $modx->getChunk('slider');

$isVacancy = false;
$isNews = false;
$isBlog = false;
if ($modx->resource) {
    $tpl = $modx->resource->getOne('Template');
    if ($tpl) {
        $tplName = $tpl->get('templatename');
        if ($tplName === 'vacancy') $isVacancy = true;
        if ($tplName === 'news') $isNews = true;
        if ($tplName === 'blog') $isBlog = true;
    }
    $blogId = (int)$modx->findResource('blog');
    if ($blogId) {
        $rid = (int)$modx->resource->get('id');
        $parent = (int)$modx->resource->get('parent');
        if ($rid === $blogId) $isBlog = true;
        if ($parent === $blogId) $isNews = true;
    }
}

$esc = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$url = function ($id) use ($modx, $esc) {
    $u = $modx->makeUrl($id);
    if ($u === '' || $u === null) return '/';
    if (strpos($u, 'http') === 0 || $u[0] === '/') return $esc($u);
    return $esc('/' . $u);
};

$ancestors = array();
if ($modx->resource) {
    $parent = (int)$modx->resource->get('parent');
    $siteStart = (int)$modx->config['site_start'];
    while ($parent > 0 && $parent !== $siteStart) {
        $p = $modx->getObject('modResource', $parent);
        if (!$p || !$p->get('published')) break;
        $ancestors[] = $p;
        $parent = (int)$p->get('parent');
    }
    $ancestors = array_reverse($ancestors);
}

$t = $esc($title);
$homeUrl = $url($modx->config['site_start']);

$pageheadClass = 'tz-pagehead';
if ($isVacancy) $pageheadClass .= ' tz-pagehead--vacancy';
if ($isNews) $pageheadClass .= ' tz-pagehead--news';
$out  = '<div class="' . $pageheadClass . '">';
$out .= '<div class="tz-wrap">';
$out .= '<nav class="tz-crumb" aria-label="Навигация"><ol class="tz-crumb__list">';
$out .= '<li><a href="' . $homeUrl . '">Главная</a></li>';
foreach ($ancestors as $ancestor) {
    if ((int)$ancestor->get('id') === (int)$modx->resource->get('id')) continue;
    $out .= '<li><a href="' . $url($ancestor->get('id')) . '">' . $esc($ancestor->get('pagetitle')) . '</a></li>';
}
$out .= '<li aria-current="page">' . $t . '</li>';
$out .= '</ol></nav>';
if (!$isVacancy && !$isNews) {
    $out .= '<h1 class="tz-pagehead__h1">' . $t . '</h1>';
}
$out .= '</div></div>';
return $out;
