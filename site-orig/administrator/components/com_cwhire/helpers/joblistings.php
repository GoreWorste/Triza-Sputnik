<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/
// no direct access
defined('_JEXEC') or die;

abstract class JHtmlJoblistings
{
    /**
     * @param   int $value  The state value
     * @param   int $i
     */
    static function approve($value = 0, $i)
    {
        // Array of image, task, title, action

        $states = array(
            0   => array('publish_r.png',    'joblistings.approve',  'Show',   'Toggle to publish on homepage'),
            1   => array('icon-16-allow.png',        'joblistings.unapprove',    'Hide',     'Toggle to unpublish on homepage'),
        );
        $state  = JArrayHelper::getValue($states, (int) $value, $states[1]);
//         error_log("helper togglehome: ".$i." state: ".print_r( $state, 1 ), 0);
        $html   = JHtml::_('image', 'admin/'.$state[0], JText::_($state[2]), NULL, true);
        //if ($canChange) {
            $html   = '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'
                    . $html.'</a>';
        //}

        return $html;
    }
}
