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

class JFormFieldMimetypes extends JFormFieldList
{
        /**
         * The form field type.
         *
         * @var         string
         * @since       1.6
         */
        protected $type = 'Mimetypes';

        /**
         * Method to get the field options.
         *
         * @return      array   The field option objects.
         * @since       1.6
         */
        public function getOptions()
        {
                // Initialize variables.
                // $options = array();
                // $mime_types = array(
                //   "pdf"=>"application/pdf"
                //   ,"exe"=>"application/octet-stream"
                //   ,"zip"=>"application/zip"
                //   ,"docx"=>"application/msword"
                //   ,"doc"=>"application/msword"
                //   ,"xls"=>"application/vnd.ms-excel"
                //   ,"ppt"=>"application/vnd.ms-powerpoint"
                //   ,"gif"=>"image/gif"
                //   ,"png"=>"image/png"
                //   ,"jpeg"=>"image/jpg"
                //   ,"jpg"=>"image/jpg"
                //   ,"mp3"=>"audio/mpeg"
                //   ,"wav"=>"audio/x-wav"
                //   ,"mpeg"=>"video/mpeg"
                //   ,"mpg"=>"video/mpeg"
                //   ,"mpe"=>"video/mpeg"
                //   ,"mov"=>"video/quicktime"
                //   ,"avi"=>"video/x-msvideo"
                //   ,"3gp"=>"video/3gpp"
                //   ,"css"=>"text/css"
                //   ,"jsc"=>"application/javascript"
                //   ,"js"=>"application/javascript"
                //   ,"php"=>"text/html"
                //   ,"htm"=>"text/html"
                //   ,"html"=>"text/html"
                //   ,"doc"=>"application/msword"
                //   ,"docx"=>"application/msword"
                //   ,"xls"=>"application/vnd.ms-excel"
                //   ,"xlsx"=>"application/vnd.ms-excel"
                // );
                $mime_types = array(
                  // array("value"=>"doc", "text"=>"doc");
                  // array("value"=>"docx", "text"=>"docx");
                  // array("value"=>"ods", "text"=>"ods");
                  // array("value"=>"odt", "text"=>"odt");
                  "doc"=>"doc"
                  ,"docx"=>"docx"
                  ,"gif"=>"gif"
                  ,"htm"=>"htm"
                  ,"html"=>"html"
                  ,"jpeg"=>"jpeg"
                  ,"jpg"=>"jpg"
                  ,"ods"=>"ods"
                  ,"odt"=>"odt"
                  ,"pdf"=>"pdf"
                  ,"png"=>"png"
                  ,"ppt"=>"ppt"
                  ,"txt"=>"txt"
                  ,"xls"=>"xls"
                  ,"xlsx"=>"xlsx"
                  ,"zip"=>"zip"
                );
                sort($mime_types);
                // $options=array_flip($mime_types);
                $options = $mime_types;
                return $options;
        }
}
