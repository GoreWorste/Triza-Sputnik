<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of categories
 */
// class JFormFieldCustom_field extends JFormField
// {
// 	/**
// 	 * The form field type.
// 	 *
// 	 * @var		string
// 	 * @since	1.6
// 	 */
// 	protected $type = 'text';
//
// 	/**
// 	 * Method to get the field input markup.
// 	 *
// 	 * @return	string	The field input markup.
// 	 * @since	1.6
// 	 */
// 	protected function getInput()
// 	{
// 		// Initialize variables.
// 		$html = array();
//
// 		return implode($html);
// 	}
// }

class JFormFieldJobcategory extends JFormFieldList
{
        /**
         * The form field type.
         *
         * @var         string
         * @since       1.6
         */
        protected $type = 'jobcategory';

        /**
         * Method to get the field options.
         *
         * @return      array   The field option objects.
         * @since       1.6
         */
        public function getOptions()
        {
                // Initialize variables.
                $options = array();

                $db     = JFactory::getDbo();
                $query  = $db->getQuery(true);

                $query->select('id As value, name As text');
                $query->from('#__cwhire_categories AS a');
                $query->order('a.name');
                $query->where('state = 1');

                // Get the options.
                $db->setQuery($query);

                $options = $db->loadObjectList();

                // Check for a database error.
                if ($db->getErrorNum()) {
                        // JError::raiseWarning(500, $db->getErrorMsg());
                        $this->_mainframe->enqueueMessage($db->getErrorMsg(), 'error');
                }

                return $options;
        }
}
