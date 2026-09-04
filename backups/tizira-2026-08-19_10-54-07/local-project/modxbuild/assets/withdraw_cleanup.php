<?php
/**
 * One-shot server cleanup during project withdrawal.
 * Uploaded temporarily by withdraw_project.py, then removed.
 */
$expected = '{{TOKEN}}';
if (($_GET['token'] ?? '') !== $expected) {
    http_response_code(403);
    exit('forbidden');
}

define('MODX_API_MODE', true);
require dirname(__DIR__) . '/index.php';
$modx->getService('error', 'error.modError');

$prefix = $modx->getOption('table_prefix');
foreach (['triza_vacancies', 'triza_categories'] as $table) {
    $modx->exec("DROP TABLE IF EXISTS `{$prefix}{$table}`");
}

foreach ($modx->getCollection('modResource', ['deleted' => 1]) as $resource) {
    $resource->remove(true);
}

$modx->cacheManager->refresh();
header('Content-Type: text/plain; charset=utf-8');
echo "cleanup ok\n";
@unlink(__FILE__);
