<?php
/**
 * contactsContent — contacts page body (file override).
 */
$r = $modx->resource;
if (!$r || $r->get('alias') !== 'contacts') return '';

$file = MODX_BASE_PATH . 'assets/contacts/contacts-content.html';
if (is_readable($file)) {
    return file_get_contents($file);
}

return (string)$r->get('content');
