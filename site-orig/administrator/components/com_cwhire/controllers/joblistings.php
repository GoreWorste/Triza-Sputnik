<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');


/**
 * Joblistings list controller class.
 */
class CwhireControllerJoblistings extends JControllerAdmin
{

	public function __construct($config = array())
	{
	    parent::__construct($config);

	    $this->registerTask('unapprove', 'approve');
	}

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'joblisting', $prefix = 'CwhireModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}


	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}



    /**
     * Method to toggle the featured setting of a list of articles.
     *
     * @return  void
     * @since   1.6
     */
    public function approve()
    {
    	error_log("approve: ", 0);
        // Initialise variables.
        $user   = JFactory::getUser();
				$jinput = JFactory::getApplication()->input;
				$ids = $jinput->get('cid', array(), 'array');
        $values = array('approve' => 1, 'unapprove' => 0);
        $task   = $this->getTask();
        $value  = JArrayHelper::getValue($values, $task, 0, 'int');

        if (empty($ids)) {

						$this->_mainframe->enqueueMessage('JERROR_NO_ITEMS_SELECTED', 'warning');
        }
        else {
            // Get the model.
            $model = $this->getModel('joblistings');

            // Publish the items.
            if (!$model->approve($ids, $value)) {

								$this->_mainframe->enqueueMessage($model->getError(), 'warning');
            }
        }

        $redirectTo = JRoute::_('index.php?option='.$jinput->get('option'));
        $this->setRedirect($redirectTo);
    }

    public function publish()
    {
    	$task = $this->getTask();
    	// error_log("publish triggered with task: ".print_r( $task, 1 ), 0);

		$input = JFactory::getApplication()->input;
// 		$pks = $input->post->get('cid', array(), 'array');
		$ids    = $input->get('cid', array(), '', 'array');
		$task   = $this->getTask();
		if (empty($ids)) {

			$this->_mainframe->enqueueMessage('JERROR_NO_ITEMS_SELECTED', 'warning');
		}
		else {
			// Get the model.
			$model = $this->getModel('joblistings');
		}
    	return parent::publish();
    }
}
