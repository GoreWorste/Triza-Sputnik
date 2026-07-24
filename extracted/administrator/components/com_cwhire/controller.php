<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/


// No direct access
defined('_JEXEC') or die;

class CwhireController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable	If true, the view output will be cached
	 * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$this->refreshUpdateSite();
		require_once JPATH_COMPONENT.'/helpers/cwhire.php';


		$view		= JFactory::getApplication()->input->getCmd('view', 'joblistings');
        JFactory::getApplication()->input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}


	public function refreshUpdateSite()
	{
		// Extra query for Joomla 3.1 onwards
		$extra_query = null;
		$params = JComponentHelper::getParams('com_cwhire');
		$downloadid = $params->get('updateid');
		$app = JApplication::getInstance('site');
		// if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $this->downloadid))
		// {
		// 	$extra_query = 'dlid=' . $this->downloadid;
		// }
		if ($downloadid != "") {
			$extra_query = 'dlid=' . $downloadid;
		}
		// Setup update site array for storing in database
		$update_site = array(
			'name' => 'CW-hire Update Site',
			'extra_query'          => $extra_query
		);
		// For joomla versions < 3.1
		if (version_compare(JVERSION, '3.1', 'lt'))
		{
			unset($update_site['extra_query']);
		}
		$db = JFactory::getDBO();
		// Get current extension ID
		$extension_id = $this->getExtensionId();
		if (!$extension_id)
		{
			return;
		}
		// Get the update sites for current extension
		$query = $db->getQuery(true)
		->select($db->qn('update_site_id'))
		->from($db->qn('#__update_sites_extensions'))
		->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
		$db->setQuery($query);
		$updateSiteIDs = $db->loadColumn(0);
			// Loop through all update sites
			foreach ($updateSiteIDs as $id)
			{
				$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__update_sites'))
				->where($db->qn('update_site_id') . ' = ' . $db->q($id));
				$db->setQuery($query);
				$aSite = $db->loadObject();
				// Does the name and location match?
				if (($aSite->name == $update_site['name']))
				{
					// Do we have the extra_query property (J 3.2+) and does it match?
					if (property_exists($aSite, 'extra_query'))
					{
						if ($aSite->extra_query == $update_site['extra_query'])
						{
							continue;
						}
					}
					else
					{
						// Joomla! 3.1 or earlier. Updates may or may not work.
						continue;
					}
				}
				$update_site['update_site_id'] = $id;
				$newSite = (object) $update_site;
				$db->updateObject('#__update_sites', $newSite, 'update_site_id', true);
			}
		// }
	}
	/**
	* Get extension Id
	*
	* @params void
	*
	* @return  extension id
	*
	* @since 1.1.7
	*
	*/
	public function getExtensionId()
	{
		$db = JFactory::getDBO();
		// Get current extension ID
		$query = $db->getQuery(true)
		->select($db->qn('extension_id'))
		->from($db->qn('#__extensions'))
		->where($db->qn('type') . ' = ' . $db->q('component'))
		->where($db->qn('element') . ' = ' . $db->q('com_cwhire'));
		$db->setQuery($query);
		$extension_id = $db->loadResult();
		if (empty($extension_id))
		{
			return 0;
		}
		else
		{
			return $extension_id;
		}
	}

}
