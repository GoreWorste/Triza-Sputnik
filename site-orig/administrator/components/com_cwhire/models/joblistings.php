<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of Cwhire records.
 */
class CwhireModeljoblistings extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                                'id', 'a.id',
		'jobcategory', 'jobcategories.name',
                'refname', 'a.refname',
                'title', 'a.title',
                'location', 'a.location',
                'contract', 'a.contract',
                'start_date', 'a.start_date',
                'image', 'a.image',
                'description', 'a.description',
                'tasks', 'a.tasks',
                'profile', 'a.profile',
                'perspective', 'a.perspective',
                'contactinfo', 'a.contactinfo',
                'language', 'a.language',
                'state', 'a.state',
                'ordering', 'a.ordering',
                'created_by', 'a.created_by',
                'created', 'a.created',

            );
            if (JLanguageAssociations::isEnabled())
            {
              $config['filter_fields'][] = 'association';
            }
        }

        parent::__construct($config);
    }


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);


		//Filtering start_date
		$this->setState('filter.start_date.from', $app->getUserStateFromRequest($this->context.'.filter.start_date.from', 'filter_from_start_date', '', 'string'));
		$this->setState('filter.start_date.to', $app->getUserStateFromRequest($this->context.'.filter.start_date.to', 'filter_to_start_date', '', 'string'));

    //Filter (dropdown) jobcategory
    $jobcategory = $this->getUserStateFromRequest($this->context.'.filter.jobcategory', 'filter_jobcategory', '', 'string');
    $this->setState('filter.jobcategory', $jobcategory);

    $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
    $this->setState('filter.language', $language);


		// Load the parameters.
		$params = JComponentHelper::getParams('com_cwhire');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.refname', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__cwhire_` AS a');

    // Join over the language
		$query->select('l.title AS language_title, l.image AS language_image')
			->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON l.lang_code = a.language');
      
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		$query->select('jobcategories.name AS categoryname, jobcategories.recipient AS recipient');
		$query->join('LEFT', '#__cwhire_categories AS jobcategories ON jobcategories.id = a.jobcategory');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
		    $query->where('a.state = '.(int) $published);
		} else if ($published === '') {
		    $query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
          $query->where('( jobcategories.name LIKE '.$search.'  OR a.refname LIKE '.$search.'  OR  a.title LIKE '.$search.'  OR  a.location LIKE '.$search.'  OR  a.start_date LIKE '.$search.'  OR  a.description LIKE '.$search.'  OR  a.tasks LIKE '.$search.'  OR  a.profile LIKE '.$search.'  OR  a.perspective LIKE '.$search.' )');
			}
		}

    // Filter jobcategory
    $jobcategory= $db->escape($this->getState('filter.jobcategory'));
    if (!empty($jobcategory)) {
            $query->where('(jobcategory='.$jobcategory.')');
    }

		//Filtering start_date
		$filter_start_date_from = $this->state->get("filter.start_date.from");
		if ($filter_start_date_from) {
			$query->where("a.start_date >= '".$db->escape($filter_start_date_from)."'");
		}
		$filter_start_date_to = $this->state->get("filter.start_date.to");
		if ($filter_start_date_to) {
			$query->where("a.start_date <= '".$db->escape($filter_start_date_to)."'");
		}

    // Filter on the language.
    if ($language = $this->getState('filter.language'))
    {
      $query->where('a.language = ' . $db->quote($language));
    }


		// Add the list ordering clause.
    $orderCol	= $this->state->get('list.ordering');
    $orderDirn	= $this->state->get('list.direction');
    if ($orderCol && $orderDirn) {
        $query->order($db->escape($orderCol.' '.$orderDirn));
    }

		return $query;
	}

public	function approve($cid, $publish) {
		error_log("togglehome: ".$cid." state: ".print_r( $publish, 1 ), 0);
		if (count( $cid ))
		{
		    JArrayHelper::toInteger($cid);
		    $cids = implode( ',', $cid );
		    $query = 'UPDATE #__cwhire_'
			  . ' SET homepage = '.(int) $publish
			  . ' WHERE id IN ( '.$cids.' )';
			  $this->_db->setQuery( $query );
			if (!$this->_db->query()) {
			    $this->setError($this->_db->getErrorMsg());
			    return false;
			}
		  }
		  return true;
	}
}
