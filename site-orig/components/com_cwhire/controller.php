<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/

// ini_set('display_errors', '0');

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

$app = JFactory::getApplication();
$params = $app->getParams();

$usecss = $params->get('usecss');
$doc = JFactory::getDocument();

if ($usecss == 1) {
  $doc->addStyleSheet(JURI::base(true) . '/components/com_cwhire/assets/css/bootstrap.min.css', 'text/css' );
  $doc->addStyleSheet(JURI::base(true) . '/components/com_cwhire/assets/css/cwhire.css', 'text/css' );
  $doc->addStyleSheet(JURI::base(true) . '/components/com_cwhire/assets/css/font-awesome.min.css', 'text/css' );
}
// $useawesome = 1;
// if ($useawesome == 1) {
//   $doc->addStyleSheet(JURI::base(true) . '/components/com_cwhire/assets/css/font-awesome.min.css', 'text/css' );
// }

class CwhireController extends JControllerLegacy
{

}
