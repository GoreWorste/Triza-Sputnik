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
                JLog::add(JText::_('joblocation getOptions'), JLog::INFO, 'com_cwhire');
                // Initialize variables.
                $options = array();

                $db     = JFactory::getDbo();
                $query  = $db->getQuery(true);

                $query->select('location As value, location As text');
                $query->from('#__cwhire_ AS a');
                if (JLanguageMultilang::isEnabled())
                {
                  $query->where('a.language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
                }
                $query->where('state = 1');
                $query->order('a.location');
                $query->group('a.location');


                // Get the options.
                $db->setQuery($query);

                $options = $db->loadObjectList();

                // Check for a database error.
                if ($db->getErrorNum()) {

                        JFactory::getApplication()->enqueueMessage($db->getErrorMsg(), 'error');
                }

                return $options;
        }
}
