<?php
/**
 * @author     createweb - rainer haage
 * @link       http://www.createweb.de
 * @copyright  Copyright (C) 2017-2021 createweb - rainer haage. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


require( JModuleHelper::getLayoutPath( 'mod_cw_fotorama' ) );

$doc =& JFactory::getDocument();
$load_fotorama_js = $params->get('usejs');
$use_mod_css = $params->get('usecss');
$load_jquery = $params->get('load_jquery');

if ($load_jquery == 1) {
  $doc->addScript(JURI::base(true) . '/modules/mod_cw_fotorama/assets/js/jquery.min.js', 'text/javascript' );
}
if ($load_fotorama_js == 1) {
  $doc->addScript(JURI::base(true) . '/modules/mod_cw_fotorama/assets/js/fotorama.js', 'text/javascript' );
}
if ($use_mod_css == 1) {
  $doc->addStyleSheet(JURI::base(true) . '/modules/mod_cw_fotorama/assets/css/fotorama.css', 'text/css' );
}
?>
