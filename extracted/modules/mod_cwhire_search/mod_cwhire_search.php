<?php
/**
 * @package    mod_cwhire_search
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

	//Following variables used more than once
// 	$this->sortColumn       = $this->state->get('list.ordering');
// 	$this->sortDirection    = $this->state->get('list.direction');
// 	$this->searchterms      = $this->state->get('filter.search');

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

// $jobcategories = $params->get('jobcategory', '1');

$helper = modCwhireSearchHelper::getSearchfields( $params );
// $searchhelper = new modCwhireSearchHelper;
// $hello = $searchhelper->getHello( $jobcategories );


require( JModuleHelper::getLayoutPath( 'mod_cwhire_search' ) );

$doc =& JFactory::getDocument();
$com_params = JComponentHelper::getParams('com_cwhire');
$use_comp_css = $com_params->get('usecss');
if ($use_comp_css == 1) {
  $doc->addStyleSheet(JURI::base(true) . '/components/com_cwhire/assets/css/cwhire.css', 'text/css' );
  $doc->addStyleSheet(JURI::base(true) . '/components/com_cwhire/assets/css/jobokay.css', 'text/css' );
}
?>
