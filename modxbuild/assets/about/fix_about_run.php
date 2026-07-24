<?php
define('MODX_API_MODE', true);
require dirname(__DIR__, 2) . '/index.php';
$id = 98;
$res = $modx->getObject('modResource', $id);
if (!$res) {
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(500);
    echo 'resource not found';
    exit;
}
$content = file_get_contents(__DIR__ . '/about-content.html');
if ($content === false) {
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(500);
    echo 'content file missing';
    exit;
}
$res->set('content', $content);
if (!$res->save()) {
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(500);
    echo 'save failed';
    exit;
}
$modx->cacheManager->refresh();
header('Content-Type: text/plain; charset=utf-8');
echo 'ok bytes=' . strlen($content);
