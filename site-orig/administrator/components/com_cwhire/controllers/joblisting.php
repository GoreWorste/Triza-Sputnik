<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');




/**
 * Joblisting controller class.
 */
class CwhireControllerJoblisting extends JControllerForm
{

    function __construct() {
        $this->view_list = 'joblistings';
        parent::__construct();
    }


    public function trash() {
    	$jinput = JFactory::getApplication()->input;
    	return parent::trash();
    }

    public function publish()
    {
    	$task = $this->getTask();
    	// error_log("publish triggered with task: ".print_r( $task, 1 ), 0)

    	return parent::publish();

    }
}
