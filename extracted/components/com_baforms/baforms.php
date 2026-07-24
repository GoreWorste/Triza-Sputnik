<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

$params = JComponentHelper::getParams('com_baforms');
define('UPLOADED_BASE', JPATH_ROOT . '/' . $params->get('uploaded_path', 'images'));
$controller = JControllerLegacy::getInstance('baforms');
$controller->execute(JFactory::getApplication()->input->getCmd('task', 'display'));
$controller->redirect();