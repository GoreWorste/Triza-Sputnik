<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

// JLog::addLogger(
//     array(
// 	// Sets file name
// 	'text_file' => 'com_cwhire.log.php'
//     ),
//     // Sets messages of all log levels to be sent to the file
//     JLog::ALL,
//     // The log category/categories which should be recorded in this file
//     // In this case, it's just the one category from our extension, still
//     // we need to put it inside an array
//     array('com_cwhire')
// );

// Execute the task.
$controller	= JControllerLegacy::getInstance('Cwhire');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
