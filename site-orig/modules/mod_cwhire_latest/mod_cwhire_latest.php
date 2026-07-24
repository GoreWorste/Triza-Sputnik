<?php
/**
  * @package    mod_cwhire_latest
  * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
  * @link http://createweb.de
  * @license GNU General Public License version 3, or later
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$doc =& JFactory::getDocument();
	//Following variables used more than once
// 	$this->sortColumn       = $this->state->get('list.ordering');
// 	$this->sortDirection    = $this->state->get('list.direction');
// 	$this->searchterms      = $this->state->get('filter.search');

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';
// $result =  modCwhireLatestHelper::getQuery( $params );
// $hello = modCwhireLatestHelper::getListoutput( $params );
$entries = modCwhireLatestHelper::getListQuery( $params );
// 	  $moduleParams=json_decode($params);
// 	  $jobcategory=$moduleParams->com_jobok_category;
// 	  $querylimit=$moduleParams->list_limit;
// $hello .= "<b>data: ".print_r( $result[0] )."</b><br>";
// require( JModuleHelper::getLayoutPath( 'mod_cwhire_latest' ) );
require JModuleHelper::getLayoutPath('mod_cwhire_latest', $params->get('layout', 'default'));



$usecss = $params->get('usecss');
if ($usecss == 1) {
  $doc->addStyleSheet(JURI::base(true) . '/modules/mod_cwhire_latest/assets/css/main.css', 'text/css' );
}
$com_params = JComponentHelper::getParams('com_cwhire');
$use_comp_css = $com_params->get('usecss');
if ($use_comp_css == 1) {
  $doc->addStyleSheet(JURI::base(true) . '/components/com_cwhire/assets/css/bootstrap.min.css', 'text/css' );
  $doc->addStyleSheet(JURI::base(true) . '/components/com_cwhire/assets/css/cwhire.css', 'text/css' );
  $doc->addStyleSheet(JURI::base(true) . '/components/com_cwhire/assets/css/font-awesome.min.css', 'text/css' );
}
// $useawesome = 1;
// if ($useawesome == 1) {
//   $doc->addStyleSheet(JURI::base(true) . '/components/com_cwhire/assets/css/font-awesome.min.css', 'text/css' );
// }
?>
