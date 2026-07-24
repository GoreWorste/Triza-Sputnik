<?php
/**
 * aboutContent — lightweight about page body (file override).
 */
$r = $modx->resource;
if (!$r || $r->get('alias') !== 'about') return '';

$file = MODX_BASE_PATH . 'assets/about/about-content.html';
if (is_readable($file)) {
    return file_get_contents($file);
}

return (string)$r->get('content');
