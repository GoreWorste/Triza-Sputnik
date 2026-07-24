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
class CwhireControllerJobcategory extends JControllerForm
{

    function __construct() {
        $this->view_list = 'categories';
        parent::__construct();
    }

}
