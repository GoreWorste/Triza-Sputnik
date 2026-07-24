<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
  * @package    mod_cwhire_latest
  * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
  * @link http://createweb.de
  * @license GNU General Public License version 3, or later
 */
class modCwhireLatestHelper
{
    /**
     * Retrieves the hello message
     *
     * @param array $params An object containing the module parameters
     * @access public
     */


    public function getListQuery( $params )
    {
      $moduleParams=json_decode($params);
      // $jobcategory=$moduleParams->com_jobok_category;
      // $jobcategory=$moduleParams->com_jobok_category;
      $jobcategories=$moduleParams->com_jobok_categories;
      // $tags = $params->get('tags');
      // $hello = modCwhireLatestHelper::getListoutput( $params );
      $querylimit=$moduleParams->list_limit;

      // Create a new query object.
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);

      $query->select(array('id', 'jobcategory', 'title', 'state', 'contract', 'location', 'refname', 'start_date', 'image', 'tasks', 'profile', 'description', 'shortdesc'));
      $query->from('#__cwhire_');
      $query->where('state = \'1\'');
      $query->where('homepage = \'1\'');

      if (JLanguageMultilang::isEnabled())
      {
        $query->where('language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
      }

      $jobcatstring = join(',',$jobcategories);
      // $query->where("jobcategory IN ".implode("','",$jobcategories)."");
      if (count($jobcategories) > 0) {
        if (count($jobcategories) == 1) {
          if ($jobcategories[0] != 0) {
            $query->where('jobcategory = '.$jobcatstring.'');
          }
        }
        else {
          $query->where('jobcategory IN ('.$jobcatstring.')');
        }
      }
      // else {
      //   $query->where('jobcategory IN '.$jobcatstring.'');
      // }
      // $query->where('jobcategory IN '.$jobcatstring.'');
      $query->order('created DESC');

      $db->setQuery($query,0,$querylimit);

      // Load the results as a list of stdClass objects.
      $result = $db->loadObjectList();

      return $result;
    }


    public function __construct($config = array()) {
//         if (empty($config['filter_fields'])) {
//             $config['filter_fields'] = array(
//                                 'id', 'a.id',
// 		'jobcategory', 'a.jobcategory',
//                 'title', 'a.title',
//             );
//         }
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
//         $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
//         $this->setState('list.limit', $limit);
//
//         $limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);
//         $this->setState('list.start', $limitstart);

// 	// Load the filter state.
// 	$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
// 	$this->setState('filter.search', $search);
//
// 	//Filter (dropdown) jobcategory
// 	$jobcategory = $this->getUserStateFromRequest($this->context.'.filter.jobcategory', 'filter_jobcategory', '', 'string');
// 	$this->setState('filter.jobcategory', $jobcategory);
//
//
// 	if(empty($ordering)) {
// 		$ordering = 'a.ordering';
// 	}

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    public function getResult($params) {

	  $moduleParams=json_decode($params);
	  // $jobcategory=$moduleParams->com_jobok_category;
    $jobcategories=$moduleParams->com_jobok_categories;
	  $querylimit=$moduleParams->list_limit;

        // Create a new query object.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

	$query->select(array('id', 'jobcategory', 'title', 'state'));
	$query->from('#__cwhire_');
// 	$query->where('state = \'1\'');
	// $query->where('(jobcategory LIKE '.$jobcategory.')');

  $jobcatstring = join(',',$jobcategories);
  // $query->where("jobcategory IN ".implode("','",$jobcategories)."");
  if (count($jobcategories) > 0) {
      if (count($jobcategories) == 1) {
        if ($jobcategories[0] != 0) {
          $query->where('jobcategory = '.$jobcatstring.'');
        }

      }
      else {
        $query->where('jobcategory IN ('.$jobcatstring.')');
      }
  }

// 	$query->order('ordering ASC');

	$db->setQuery($query);

	// Load the results as a list of stdClass objects.
	$results = $db->loadObjectList();
        return $results;
    }
}
?>
