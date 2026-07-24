<?php
/**
 * vacancyRouter — plugin (OnPageNotFound): maps /vacancy/<id> to the vacancy
 * container resource, passing the id so jobList renders the detail view.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim(rawurldecode($path), '/');
if (preg_match('~^vacancy/(\d+)$~', $path, $m)) {
    $vacId = (int)$m[1];
    $_GET['vac'] = $vacId;
    $_REQUEST['vac'] = $vacId;
    $container = $modx->findResource('vacancy');
    if ($container) {
        $modx->sendForward($container);
    }
}
