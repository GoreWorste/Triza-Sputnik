<?php
/**
 * newsPageType — blog | article | (empty)
 */
$blogId = (int)$modx->findResource('blog');
if (!$blogId || !$modx->resource) return '';

$id = (int)$modx->resource->get('id');
$parent = (int)$modx->resource->get('parent');

if ($id === $blogId) return 'blog';
if ($parent === $blogId) return 'article';
return '';
