<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/

ini_set('display_errors', '1');

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

// $mimetypes = array(
//   "doc"=>"application/msword"
//   ,"docx"=>"application/msword"
//   ,"gif"=>"image/gif"
//   ,"htm"=>"text/html"
//   ,"html"=>"text/html"
//   ,"jpeg"=>"image/jpg"
//   ,"jpg"=>"image/jpg"
//   ,"ods"=>"application/vnd.oasis.opendocument.spreadsheet"
//   ,"odt"=>"application/vnd.oasis.opendocument.text"
//   ,"pdf"=>"application/pdf"
//   ,"png"=>"image/png"
//   ,"ppt"=>"application/vnd.ms-powerpoint"
//   ,"txt"=>"text/plain"
//   ,"xls"=>"application/vnd.ms-excel"
//   ,"xlsx"=>"application/vnd.ms-excel"
//   ,"zip"=>"application/zip"
// );

/**
 * Cwhire model.
 */
class CwhireModelJoblisting extends JModelForm
{

    var $_item = null;
    // public $mimetypes;

    public $mimetypes = array(
      "doc"=>"application/msword"
      ,"docx"=>"application/msword"
      ,"gif"=>"image/gif"
      ,"htm"=>"text/html"
      ,"html"=>"text/html"
      ,"jpeg"=>"image/jpg"
      ,"jpg"=>"image/jpg"
      ,"ods"=>"application/vnd.oasis.opendocument.spreadsheet"
      ,"odt"=>"application/vnd.oasis.opendocument.text"
      ,"pdf"=>"application/pdf"
      ,"png"=>"image/png"
      ,"ppt"=>"application/vnd.ms-powerpoint"
      ,"txt"=>"text/plain"
      ,"xls"=>"application/vnd.ms-excel"
      ,"xlsx"=>"application/vnd.ms-excel"
      ,"zip"=>"application/zip"
    );

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */

   // public function __construct() {
   //   // $mimetypes = array(
   //   //   "doc"=>"application/msword"
   //   //   ,"docx"=>"application/msword"
   //   //   ,"gif"=>"image/gif"
   //   //   ,"htm"=>"text/html"
   //   //   ,"html"=>"text/html"
   //   //   ,"jpeg"=>"image/jpg"
   //   //   ,"jpg"=>"image/jpg"
   //   //   ,"ods"=>"application/vnd.oasis.opendocument.spreadsheet"
   //   //   ,"odt"=>"application/vnd.oasis.opendocument.text"
   //   //   ,"pdf"=>"application/pdf"
   //   //   ,"png"=>"image/png"
   //   //   ,"ppt"=>"application/vnd.ms-powerpoint"
   //   //   ,"txt"=>"text/plain"
   //   //   ,"xls"=>"application/vnd.ms-excel"
   //   //   ,"xlsx"=>"application/vnd.ms-excel"
   //   //   ,"zip"=>"application/zip"
   //   // );
   // }

	protected function populateState()
	{
    // global $mimetypes;
		$app = JFactory::getApplication('com_cwhire');

		// Load state from the request userState on edit or from the passed variable on default
        if (JFactory::getApplication()->input->get('layout') == 'edit') {
            $id = JFactory::getApplication()->getUserState('com_cwhire.edit.joblisting.id');
        } else {
            $id = JFactory::getApplication()->input->get('id');
            JFactory::getApplication()->setUserState('com_cwhire.edit.joblisting.id', $id);
        }
		$this->setState('joblisting.id', $id);

		// Load the parameters.
		$params = $app->getParams();
        $params_array = $params->toArray();
        if(isset($params_array['item_id'])){
            $this->setState('joblisting.id', $params_array['item_id']);
        }
		$this->setState('params', $params);

    // $this->mimetypes = array(
    //   "doc"=>"application/msword"
    //   ,"docx"=>"application/msword"
    //   ,"gif"=>"image/gif"
    //   ,"htm"=>"text/html"
    //   ,"html"=>"text/html"
    //   ,"jpeg"=>"image/jpg"
    //   ,"jpg"=>"image/jpg"
    //   ,"ods"=>"application/vnd.oasis.opendocument.spreadsheet"
    //   ,"odt"=>"application/vnd.oasis.opendocument.text"
    //   ,"pdf"=>"application/pdf"
    //   ,"png"=>"image/png"
    //   ,"ppt"=>"application/vnd.ms-powerpoint"
    //   ,"txt"=>"text/plain"
    //   ,"xls"=>"application/vnd.ms-excel"
    //   ,"xlsx"=>"application/vnd.ms-excel"
    //   ,"zip"=>"application/zip"
    // );

    // $this->mimetypes = $mimetypes;

	}

  public function getExtensions($typeids) {
    $c = 0;
    $retarray = array();
    foreach ($this->mimetypes as $key => $value) {
      if (is_array($typeids)) {
        if (in_array($c, $typeids)) {
          array_push($retarray, $key);
        }
      }
      $c++;
    }
    // $retarray = $typeids;
    return $retarray;
  }

  public function getMimetypes($typeids) {
    $c = 0;
    $retarray = array();
    // error_log("mime types: ".print_r($this->mimetypes, 1), 0);
    foreach ($this->mimetypes as $key => $value) {
      if (is_array($typeids)) {
        if (in_array($c, $typeids)) {
          array_push($retarray, $value);
        }
      }
      $c++;
    }
    // $retarray = $typeids;
    return $retarray;
  }


	/**
	 * Method to get an ojbect.
	 *
	 * @param	integer	The id of the object to get.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getData($id = null)
	{
		if ($this->_item === null)
		{
			$this->_item = false;

			if (empty($id)) {
				$id = $this->getState('joblisting.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id))
			{
				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published) {
						return $this->_item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties = $table->getProperties(1);
				$this->_item = JArrayHelper::toObject($properties, 'JObject');
        // $this->_item ->description = trim($this->_item ->fulltext) != '' ? $this->_item ->introtext . "<hr id=\"system-readmore\" />" . $this->_item ->fulltext : $this->_item ->introtext;
			} elseif ($error = $table->getError()) {
				$this->setError($error);
			}
		}

		return $this->_item;
	}

	public function getTable($type = 'Joblisting', $prefix = 'CwhireTable', $config = array())
	{
        $this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
        return JTable::getInstance($type, $prefix, $config);
	}


	/**
	 * Method to check in an item.
	 *
	 * @param	integer		The id of the row to check out.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int)$this->getState('joblisting.id');

		if ($id) {

			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
            if (method_exists($table, 'checkin')) {
                if (!$table->checkin($id)) {
                    $this->setError($table->getError());
                    return false;
                }
            }
		}

		return true;
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param	integer		The id of the row to check out.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int)$this->getState('joblisting.id');

		if ($id) {

			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = JFactory::getUser();

			// Attempt to check the row out.
            if (method_exists($table, 'checkout')) {
                if (!$table->checkout($user->get('id'), $id)) {
                    $this->setError($table->getError());
                    return false;
                }
            }
		}

		return true;
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
     *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_cwhire.joblisting', 'joblisting', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		$data = $this->getData();

        return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array		The form data.
	 * @return	mixed		The user id on success, false on failure.
	 * @since	1.6
	 */
	public function save($data)
	{
		$id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('joblisting.id');
        $state = (!empty($data['state'])) ? 1 : 0;
        $user = JFactory::getUser();

        if($id) {
            //Check the user can edit this item
            $authorised = $user->authorise('core.edit', 'com_cwhire') || $authorised = $user->authorise('core.edit.own', 'com_cwhire');
            if($user->authorise('core.edit.state', 'com_cwhire') !== true && $state == 1){ //The user cannot edit the state of the item.
                $data['state'] = 0;
            }
        } else {
            //Check the user can create new items in this section
            $authorised = $user->authorise('core.create', 'com_cwhire');
            if($user->authorise('core.edit.state', 'com_cwhire') !== true && $state == 1){ //The user cannot edit the state of the item.
                $data['state'] = 0;
            }
        }

        if ($authorised !== true) {

            JFactory::getApplication()->enqueueMessage('JERROR_ALERTNOAUTHOR', 'error');
            return false;
        }

        $table = $this->getTable();
        if ($table->save($data) === true) {
            return $id;
        } else {
            return false;
        }

	}

     function delete($data)
    {
        $id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('joblisting.id');
        if(JFactory::getUser()->authorise('core.delete', 'com_cwhire') !== true){

            JFactory::getApplication()->enqueueMessage('JERROR_ALERTNOAUTHOR', 'error');
            return false;
        }
        $table = $this->getTable();
        if ($table->delete($data['id']) === true) {
            return $id;
        } else {
            return false;
        }

        return true;
    }

    function getCategoryName($id){
      // JLog::add(JText::_('getCategoryName: '.$id), JLog::INFO, 'com_cwhire');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('title')
            ->from('#__cwhire_categories')
            ->where('id = ' . $id);
        $db->setQuery($query);
        return $db->loadObject();
    }

    function getRecipient($id){
        // JLog::add(JText::_('getRecipient: '.$id), JLog::INFO, 'com_cwhire');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('recipient')
            ->from('#__cwhire_categories')
            ->where('id = ' . $id);
        $db->setQuery($query);
        return $db->loadObject();
    }

}
