<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

abstract class baformsHelper 
{

    public static function getAcymailingLists()
    {
        $html = '';
        $checkAcymailing = self::checkAcymailing();
        if (!empty($checkAcymailing)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('listid, name ')
                ->from('#__acymailing_list');
            $db->setQuery($query);
            $list = $db->loadObjectList();
            foreach ($list as $value) {
                $html .= '<option value="'.$value->listid.'">'.$value->name.'</option>';
            }
        }


        return $html;
    }

    public static function checkAcymailing()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('extension_id')
            ->from('#__extensions')
            ->where('element = '.$db->quote('com_acymailing'));
        $db->setQuery($query);
        $id = $db->loadResult();

        return $id;
    }

    public static function aboutUs()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("manifest_cache");
        $query->from("#__extensions");
        $query->where("type=" .$db->quote('component'))
            ->where('element=' .$db->quote('com_baforms'));
        $db->setQuery($query);
        $about = $db->loadResult();
        $about = json_decode($about);
        return $about;
    }

    public static function defaultCheckboxes($name, $form)
    {
        $input = $form->getField($name);
        $test = $form->getValue($name);
        if ($test == null) {
            $class = !empty($input->class) ? ' class="' . $input->class . '"' : '';
            $value = !empty($input->default) ? $input->default : '1';
            $checked = $input->checked || !empty($value) ? ' checked' : '';
            return '<input type="checkbox" name="' . $input->name . '" id="' . $input->id . '" value="'
            . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"' . $class . $checked . ' />';
        } else {
            return $form->getInput($name);
        }
    }
    
    public static function checkUpdate($version)
    {
        return true;
    }

    public static function drawFieldEditor($items)
    {
        $str = '<tr><th class="form-title"><a href="#" data-shortcode="[submission ID]">';
        $str .= JText::_('SUBMISSION_ID').'</a></th><td></td></tr>';
        $str .= '<tr><th class="form-title"><a href="#" data-shortcode="[page title]">';
        $str .= JText::_('PAGE_TITLE').'</a></th><td></td></tr>';
        $str .= '<tr><th class="form-title"><a href="#" data-shortcode="[page URL]">';
        $str .= JText::_('PAGE_URL').'</a></th><td></td></tr>';
        foreach ($items as $item) {
            $settings = explode('_-_', $item->settings);
            if ($settings[0] != 'button') {
                $type = $settings[2];
                if ($type != 'image' && $type != 'htmltext' && $type != 'map' && $type !='terms'){
                    $settings = explode(';', $settings[3]);
                    if (!isset($settings[2])) {
                        $settings[2] = '';
                    }
                    $name = self::checkItems($settings[0], $type, $settings[2]);
                    $str .= '<tr><th class="form-title"><a href="#" data-shortcode="[field ID='.$item->id.']">'.$name;
                    $str .= '</a></th><td>'. $item->id. '</td></tr>';
                }
            }
        }
        return $str;
    }

    public static function checkItems($item, $type, $place)
    {
        if ($item != '') {
            return $item;
        } else {
            if ($type == 'textarea') {
                if ($place != '') {
                    return $place;
                } else {
                    return 'Textarea';
                }
            }
            if ($type == 'textInput') {
                if ($place != '') {
                    return $place;
                } else {
                    return 'TextInput';
                }
            }
            if ($type == 'chekInline') {
                return 'ChekInline';
            }
            if ($type == 'checkMultiple') {
                return 'CheckMultiple';
            }
            if ($type == 'radioInline') {
                return 'RadioInline';
            }
            if ($type == 'radioMultiple') {
                return 'RadioMultiple';
            }
            if ($type == 'dropdown') {
                return 'Dropdown';
            }
            if ($type == 'selectMultiple') {
                return 'SelectMultiple';
            }
            if ($type == 'date') {
                return 'Date';
            }
            if ($type == 'slider') {
                return 'Slider';
            }
            if ($type == 'email') {
                if ($place != '') {
                    return $place;
                } else {
                    return 'Email';
                }
            }
            if ($type == 'address') {
                if ($place != '') {
                    return $place;
                } else {
                    return 'Address';
                }
            }
        }
    }
    
    public static function getContentsCurl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        
        return $data;
    }
    
    public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
            JText::_('Forms'),
			'index.php?option=com_baforms&view=forms',
			$vName == 'forms'
		);
        JHtmlSidebar::addEntry(
            JText::_('Submissions'),
			'index.php?option=com_baforms&view=submissions',
			$vName == 'Submissions'
		);
    }
}