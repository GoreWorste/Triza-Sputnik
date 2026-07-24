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

if ($alias === 'about') {
    return $modx->runSnippet('aboutContent');
}

if ($alias === 'trainings-and-webinars') {
    return $modx->runSnippet('trainingsContent');
}

if ($alias === 'contacts') {
    return $modx->runSnippet('contactsContent');
}

if ($blogId && $id === $blogId) {
    return $modx->runSnippet('newsList');
}

if ($blogId && $parent === $blogId) {
    return $modx->runSnippet('newsDetail');
}

$siteStart = (int)$modx->config['site_start'];
if ($id === $siteStart || $alias === 'home') {
    $content = (string)$r->get('content');
    $content = str_replace('В 1997 в', 'В 1992 в', $content);
    $content = str_replace('Почти за 20 лет успешной работы', 'За годы успешной работы', $content);
    return $content;
}

return (string)$r->get('content');
