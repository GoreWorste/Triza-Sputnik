<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/

// No direct access
defined('_JEXEC') or die;

/**
 * Cwhire helper.
 */
class CwhireHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($vName = '')
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_CWHIRE_TITLE_JOBLISTINGS'),
			'index.php?option=com_cwhire&view=joblistings',
			$vName == 'joblistings'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_CWHIRE_TITLE_CATEGORIES'),
			'index.php?option=com_cwhire&view=categories',
			$vName == 'categories'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_cwhire';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}


}
