<?php
/**
 * newsDefaults — plugin (OnBeforeDocFormSave): assign blog/news templates in manager.
 */
if ($modx->event->name !== 'OnBeforeDocFormSave' || empty($resource)) return;

$blogTpl = $modx->getObject('modTemplate', array('templatename' => 'blog'));
$newsTpl = $modx->getObject('modTemplate', array('templatename' => 'news'));
if (!$blogTpl || !$newsTpl) return;

$blogId = (int)$modx->findResource('blog');
if (!$blogId) return;

$id = (int)$resource->get('id');
$parent = (int)$resource->get('parent');
$blogTplId = (int)$blogTpl->get('id');
$newsTplId = (int)$newsTpl->get('id');

$target = null;
if ($id === $blogId || ($resource->get('alias') === 'blog' && $parent === 0)) {
    $target = $blogTplId;
} elseif ($parent === $blogId) {
    $target = $newsTplId;
}

if ($target && (int)$resource->get('template') !== $target) {
    $resource->set('template', $target);
}
