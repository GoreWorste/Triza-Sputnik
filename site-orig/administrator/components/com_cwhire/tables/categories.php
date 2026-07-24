<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/
// No direct access
defined('_JEXEC') or die;


class CwhireTableCategories extends JTable
{
// 	var $id 		= null;
// 	var $message 		= null;
// 	var $published 		= 0;

	/**
	* @param database A database
        connector object */
	function __construct(&$db)
	{
		parent::__construct( '#__cwhire_categories', 'id', $db );
	}

}

?>
