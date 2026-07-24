<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/

// No direct access
defined('_JEXEC') or die;

/**
 * @param	array	A named array
 * @return	array
 */
function CwhireBuildRoute(&$query)
{
	$segments = array();

	if (isset($query['task'])) {
		$segments[] = implode('/',explode('.',$query['task']));
		unset($query['task']);
	}
	if (isset($query['id'])) {
		$segments[] = $query['id'];
		unset($query['id']);
	}

	return $segments;
}

/**
 * @param	array	A named array
 * @param	array
 *
 * Formats:
 *
 * index.php?/cwhire/task/id/Itemid
 *
 * index.php?/cwhire/id/Itemid
 */
function CwhireParseRoute($segments)
{
	$vars = array();

	// view is always the first element of the array
	$count = count($segments);

    if ($count)
	{
		$count--;
		$segment = array_pop($segments) ;
		if (is_numeric($segment)) {
			$vars['id'] = $segment;
		}
        else{
            $count--;
            $vars['task'] = array_pop($segments) . '.' . $segment;
        }
	}

	if ($count)
	{
        $vars['task'] = implode('.',$segments);
	}
	return $vars;
}
