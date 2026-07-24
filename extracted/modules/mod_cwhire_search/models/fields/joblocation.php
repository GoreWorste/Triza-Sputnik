<?php
/**
  * @package    mod_cwhire_search
  * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
  * @link http://createweb.de
  * @license GNU General Public License version 3, or later
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');


class JFormFieldJoblocation extends JFormFieldList
{
        /**
         * The form field type.
         *
         * @var         string
         * @since       1.6
         */
        protected $type = 'joblocation';

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

                $query->select('location As value, location As text');
                $query->from('#__cwhire_ AS a');
                $query->order('a.location');
                $query->group('a.location');
                $query->where('state = 1');

                // Get the options.
                $db->setQuery($query);

                $options = $db->loadObjectList();

                // Check for a database error.
                if ($db->getErrorNum()) {
                        JError::raiseWarning(500, $db->getErrorMsg());
                }

                return $options;
        }
}
