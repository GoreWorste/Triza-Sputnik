<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/


// no direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_cwhire'))
{
	// throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
	$this->_mainframe->enqueueMessage('JERROR_ALERTNOAUTHOR', 'error');
}

// Include dependencies
jimport('joomla.application.component.controller');

$controller	= JControllerLegacy::getInstance('Cwhire');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
