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
class CwhireModelJoblistings extends JModelList {

  /**
  * Constructor.
  *
  * @param    array    An optional associative array of configuration settings.
  * @see        JController
  * @since    1.6
  */
  public function __construct($config = array()) {
    if (empty($config['filter_fields'])) {
      $config['filter_fields'] = array(
        'id', 'a.id',
        'jobcategory', 'jobcategories.name',
        'refname', 'a.refname',
        'title', 'a.title',
        'location', 'a.location',
        'contract', 'a.contract',
        'description', 'a.description',
        // 'introtext', 'a.introtext',
        // 'fulltext', 'a.fulltext',
        'tasks', 'a.tasks',
        'profile', 'a.profile',
        'perspective', 'a.perspective',
        'contactinfo', 'a.contactinfo',

      );
    }
    parent::__construct($config);
  }

  /**
  * Method to auto-populate the model state.
  *
  * Note. Calling getState in this method will result in recursion.
  *
  * @since	1.6
  */
  protected function populateState($ordering = null, $direction = null) {

    // Initialise variables.
    $app = JFactory::getApplication();

    // List state information
    $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
    $this->setState('list.limit', $limit);

    $limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);
    $this->setState('list.start', $limitstart);

    // Load the filter state.
    $search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
    $this->setState('filter.search', $search);

    //Filter (dropdown) jobcategory
    $jobcategory = $this->getUserStateFromRequest($this->context.'.filter.jobcategory', 'filter_jobcategory', '', 'string');
    $this->setState('filter.jobcategory', $jobcategory);

    //Filter (dropdown) joblocation
    $joblocation = $this->getUserStateFromRequest($this->context.'.filter.joblocation', 'filter_joblocation', '', 'string');
    $this->setState('filter.joblocation', $joblocation);


    if(empty($ordering)) {
      $ordering = 'a.ordering';
    }

    // List state information.
    parent::populateState($ordering, $direction);
  }

  /**
  * Build an SQL query to load the list data.
  *
  * @return	JDatabaseQuery
  * @since	1.6
  */
  protected function getListQuery() {
    // Create a new query object.
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    // Select the required fields from the table.
    $query->select(
    $this->getState(
    'list.select', 'a.*'
    )
  );

  $query->from('`#__cwhire_` AS a');


  // Join over the users for the checked out user.
  $query->select('uc.name AS editor');
  $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

  // Join over the created by field 'created_by'
  $query->select('created_by.name AS created_by');
  $query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

  $query->select('jobcategories.name AS categoryname, jobcategories.recipient AS recipient');
  $query->join('LEFT', '#__cwhire_categories AS jobcategories ON jobcategories.id = a.jobcategory AND jobcategories.state = 1');


  // Filter by search in title
  $search = $this->getState('filter.search');
  if (!empty($search)) {
    if (stripos($search, 'id:') === 0) {
      $query->where('a.id = '.(int) substr($search, 3));
    } else {
      $search = $db->Quote('%'.$db->escape($search, true).'%');
      $query->where('( jobcategories.name LIKE '.$search.'  OR a.refname LIKE '.$search.'  OR  a.title LIKE '.$search.'  OR  a.location LIKE '.$search.'  OR  a.description LIKE '.$search.'  OR  a.tasks LIKE '.$search.'  OR  a.profile LIKE '.$search.'  OR  a.perspective LIKE '.$search.' )');
    }
  }
  if (JLanguageMultilang::isEnabled())
  {
    $query->where('a.language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
  }
  $query->where('(a.state = 1)');
  $query->where('(jobcategories.state = 1)');

  // Filter joblocation
  $joblocation= $db->escape($this->getState('filter.joblocation'));
  if (!empty($joblocation)) {
    $search = $db->Quote('%'.$joblocation.'%');
    $query->where('(a.location LIKE '.$search.')');
  }

  // Filter jobcategory
  $jobcategory= $db->escape($this->getState('filter.jobcategory'));
  if (!empty($jobcategory)) {
    $query->where('(jobcategory='.$jobcategory.')');
  }

  //Filtering start_date
  $filter_start_date_from = $this->state->get("filter.start_date.from");
  if ($filter_start_date_from) {
    $query->where("a.start_date >= '".$filter_start_date_from."'");
  }
  $filter_start_date_to = $this->state->get("filter.start_date.to");
  if ($filter_start_date_to) {
    $query->where("a.start_date <= '".$filter_start_date_to."'");
  }

  $query->order('a.created DESC');

  return $query;
}

}
