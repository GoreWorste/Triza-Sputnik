<?php
/**
 * vacancyDefaults — plugin (OnBeforeDocFormSave): if a resource is created/saved
 * inside the "Вакансии" container (or its "Архив" folder), force the "vacancy"
 * template so the vacancy fields + detail rendering work without manual setup.
 */
if (empty($resource)) return;
$containerId = (int)$modx->findResource('vacancy');
if (!$containerId) return;

$parent = (int)$resource->get('parent');
$isVac = ($parent === $containerId);
if (!$isVac && $parent) {
    $p = $modx->getObject('modResource', $parent);
    if ($p && $p->get('alias') === 'arhiv' && (int)$p->get('parent') === $containerId) {
        $isVac = true;
    }
}
if ($isVac) {
    $vtpl = $modx->getObject('modTemplate', ['templatename' => 'vacancy']);
    if ($vtpl && (int)$resource->get('template') !== (int)$vtpl->get('id')) {
        $resource->set('template', (int)$vtpl->get('id'));
    }
}
