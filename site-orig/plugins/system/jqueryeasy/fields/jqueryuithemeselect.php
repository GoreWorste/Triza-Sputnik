<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// no direct access
defined( '_JEXEC' ) or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

JFormHelper::loadFieldClass('dynamicsingleselectjqe');

class JFormFieldjQueryUIThemeSelect extends JFormFieldDynamicSingleSelectJQE
{
	public $type = 'jQueryUIThemeSelect';

	protected function getOptions()
	{
		$options = array();

		$lang = JFactory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);

		$path = '/plugins/system/jqueryeasy/fields/themes/images';
		
		$options[] = array('none', JText::_('JNONE'), '', JURI::root(true).$path.'/none.png');
		
		$options[] = array('custom', JText::_('PLG_SYSTEM_JQUERYEASY_VALUE_CUSTOMLOCAL'), '', JURI::root(true).$path.'/custom.png');
		
		$options[] = array('base', 'Base', '', JURI::root(true).$path.'/base.png');
		$options[] = array('black-tie', 'Black Tie', '', JURI::root(true).$path.'/black_tie.png');
		$options[] = array('blitzer', 'Blitzer', '', JURI::root(true).$path.'/blitzer.png');
		$options[] = array('cupertino', 'Cupertino', '', JURI::root(true).$path.'/cupertino.png');
		$options[] = array('dark-hive', 'Dark Hive', '', JURI::root(true).$path.'/dark_hive.png');
		$options[] = array('dot-luv', 'Dot Luv', '', JURI::root(true).$path.'/dot_luv.png');
		$options[] = array('eggplant', 'Eggplant', '', JURI::root(true).$path.'/eggplant.png');
		$options[] = array('excite-bike', 'Excite Bike', '', JURI::root(true).$path.'/excite_bike.png');
		$options[] = array('flick', 'Flick', '', JURI::root(true).$path.'/flick.png');
		$options[] = array('hot-sneaks', 'Hot Sneaks', '', JURI::root(true).$path.'/hot_sneaks.png');
		$options[] = array('humanity', 'Humanity', '', JURI::root(true).$path.'/humanity.png');
		$options[] = array('le-frog', 'Le Frog', '', JURI::root(true).$path.'/le_frog.png');
		$options[] = array('mint-choc', 'Mint Choc', '', JURI::root(true).$path.'/mint_choco.png');
		$options[] = array('overcast', 'Overcast', '', JURI::root(true).$path.'/overcast.png');
		$options[] = array('pepper-grinder', 'Pepper Grinder', '', JURI::root(true).$path.'/pepper_grinder.png');
		$options[] = array('redmond', 'Redmond', '', JURI::root(true).$path.'/windoze.png');
		$options[] = array('smoothness', 'Smoothness', '', JURI::root(true).$path.'/smoothness.png');
		$options[] = array('south-street', 'South Street', '', JURI::root(true).$path.'/south_street.png');
		$options[] = array('start', 'Start', '', JURI::root(true).$path.'/start_menu.png');
		$options[] = array('sunny', 'Sunny', '', JURI::root(true).$path.'/sunny.png');
		$options[] = array('swanky-purse', 'Swanky Purse', '', JURI::root(true).$path.'/swanky_purse.png');
		$options[] = array('trontastic', 'Trontastic', '', JURI::root(true).$path.'/trontastic.png');
		$options[] = array('ui-darkness', 'UI Darkness', '', JURI::root(true).$path.'/ui_dark.png');
		$options[] = array('ui-lightness', 'UI Lightness', '', JURI::root(true).$path.'/ui_light.png');
		$options[] = array('vader', 'Vader', '', JURI::root(true).$path.'/black_matte.png');

		return $options;
	}

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->width = 95;
			$this->height = 95;
		}

		return $return;
	}
}
?>