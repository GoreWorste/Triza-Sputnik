<?php
/**
 * pageProse — unified inner HTML for tz-prose block.
 */
$r = $modx->resource;
if (!$r) return '';

$alias = (string)$r->get('alias');
$blogId = (int)$modx->findResource('blog');
$parent = (int)$r->get('parent');
$id = (int)$r->get('id');
$siteStart = (int)$modx->config['site_start'];
$isHome = ($id === $siteStart || $alias === 'home');

if ($blogId && $id === $blogId) {
    return $modx->runSnippet('newsList');
}
if ($blogId && $parent === $blogId) {
    return $modx->runSnippet('newsDetail');
}

$vacId = (int)$modx->findResource('vacancy');
if ($vacId && $id === $vacId) {
    return $modx->runSnippet('jobList');
}

$fileMap = [
    'home' => 'assets/home/home-content.html',
    'about' => 'assets/about/about-content.html',
    'contacts' => 'assets/contacts/contacts-content.html',
    'trainings-and-webinars' => 'assets/trainings/trainings-content.html',
    'rekrutment' => 'assets/services/rekrutment.html',
    'regionalnyy-podbor' => 'assets/services/regionalnyy-podbor.html',
    'autstaffing' => 'assets/services/autstaffing.html',
    'executive-search' => 'assets/services/executive-search.html',
    'khedkhanting' => 'assets/services/khedkhanting.html',
    'domashnij-personal' => 'assets/services/domashnij-personal.html',
    'zapros-na-podbor-personala' => 'assets/services/zapros-na-podbor-personala.html',
];

$formAliases = [
    'about',
    'contacts',
    'trainings-and-webinars',
    'rekrutment',
    'regionalnyy-podbor',
    'autstaffing',
    'executive-search',
    'khedkhanting',
    'domashnij-personal',
    'zapros-na-podbor-personala',
];

$key = $isHome ? 'home' : $alias;
$html = '';

if (isset($fileMap[$key])) {
    $file = MODX_BASE_PATH . $fileMap[$key];
    if (is_readable($file)) {
        $html = (string)file_get_contents($file);
    }
}

if ($html === '') {
    $html = (string)$r->get('content');
}

$checkAlias = $isHome ? 'home' : $alias;
if (in_array($checkAlias, $formAliases, true)) {
    $html .= $modx->runSnippet('staffingForm');
}

return $html;
