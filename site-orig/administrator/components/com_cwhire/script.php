<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');
jimport( 'joomla.database.table' );

/**
 * Script file of HelloWorld component.
 *
 * The name of this class is dependent on the component being installed.
 * The class name should have the component's name, directly followed by
 * the text InstallerScript (ex:. com_helloWorldInstallerScript).
 *
 * This class will be called by Joomla!'s installer, if specified in your component's
 * manifest file, and is used for custom automation actions in its installation process.
 *
 * In order to use this automation script, you should reference it in your component's
 * manifest file as follows:
 * <scriptfile>script.php</scriptfile>
 *
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class com_cwhireInstallerScript
{
    /**
     * This method is called after a component is installed.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */

    public function install($parent)
    {

    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($parent)
    {

    }

    /**
     * This method is called after a component is updated.
     *
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function update($parent)
    {
			$params = JComponentHelper::getParams('com_cwhire');
			$params->set('appliform_title', '1');
			$params->set('appliform_fname', '2');
			$params->set('appliform_sname', '2');
			$params->set('appliform_street', '2');
			$params->set('appliform_country', '1');
			$params->set('appliform_plz', '2');
			$params->set('appliform_address', '2');
			$params->set('appliform_phone', '1');
			$params->set('appliform_mail', '1');
			$params->set('appliform_attachments', '1');
			$params->set('appliform_atype', '3');
			$params->set('appliform_vita', '1');
			$params->set('appliform_testi', '1');
			$params->set('use_custom', '0');


			// Save the parameters
			$componentid = JComponentHelper::getComponent('com_cwhire')->id;
			$table = JTable::getInstance('extension');
			$table->load($componentid);
			$table->bind(array('params' => $params->toString()));

			// check for error
			if (!$table->check()) {
			    echo $table->getError();
			    return false;
			}
			// Save to database
			if (!$table->store()) {
			    echo $table->getError();
			    return false;
			}


      echo '<p>' . JText::sprintf('This update provides some new features. Please check the component options!', $parent->get('manifest')->version) . '</p>';
    }

    /**
     * Runs just before any installation action is preformed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent)
    {

    }

    /**
     * Runs right after any installation action is preformed on the component.
     *
     * @param  string    $type   - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $parent)
    {

    }
}
