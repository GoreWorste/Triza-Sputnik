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

// $app = JFactory::getApplication();
// $params = $app->getParams();


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

class JFormFieldContractForms extends JFormFieldList
{
        /**
         * The form field type.
         *
         * @var         string
         * @since       1.6
         */
        protected $type = 'ContractForms';

        /**
         * Method to get the field options.
         *
         * @return      array   The field option objects.
         * @since       1.6
         */
        public function getOptions()
        {
          $params = JComponentHelper::getParams('com_cwhire');
          $contracts = json_decode( $params->get('contract-forms'),true);
                $options = array();
                foreach( $contracts['title'] as $idx => $contract ) {
                  //  echo "<br>idx: ". $idx ." Title: ".$contract;
                   $options[$idx] = $contract;
                  //  print_r($contract);
                }
                return $options;
        }
}
