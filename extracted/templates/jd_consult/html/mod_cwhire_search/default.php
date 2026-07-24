<?php
/**
  * @package    mod_cwhire_search
  * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
  * @link http://createweb.de
  * @license GNU General Public License version 3, or later
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// echo $hello;
$catstate = '';
$locstate = '';

$jobcategoryOptions = $helper[0];
$joblocationOptions = $helper[1];
?>

<div id="jobokay_search_container"><form action=" <?php echo JRoute::_('index.php?option=com_cwhire&view=joblistings') ?>" method="post" name="jobsearchForm" id="adminForm">
<div id="filter-bar" class="btn-toolbar module-filter-bar">
  <label for="filter_search" class="jobsearch-label"> <?php echo JText::_('COM_CWHIRE_SEARCH_LABEL') ?></label>
  <div class="btn-group pull-left">
    <select name="filter_jobcategory" class="inputbox">
    <option value=""> - <?php echo JText::_('COM_CWHIRE_SEARCH_ALL_INDUSTRIES') ?> - </option>
    <?php echo JHtml::_('select.options', $jobcategoryOptions, 'value', 'text', $catstate) ?>
    </select>
  </div>
  <div class="btn-group pull-left">
    <select name="filter_joblocation" class="inputbox">
      <option value=""> - <?php echo JText::_('COM_CWHIRE_SEARCH_ALL_LOCATIONS') ?> - </option>
      <?php echo JHtml::_('select.options', $joblocationOptions, 'value', 'text', $locstate) ?>
    </select>
  </div>
  <div class="filter-search btn-group pull-left">
    <input type="text" name="filter_search" id="filter_search" placeholder=" <?php echo JText::_('COM_CWHIRE_SEARCH_FILTER') ?>" value="" />
  </div>
  <div class="btn-group pull-left" id="jobokay_search_buttons">
    <button class="btn hasTooltip" id="jobokay_search_submit" type="submit" title=" <?php echo JText::_('JSEARCH_FILTER_SUBMIT') ?>"><i class="icon-search"></i></button>
    <button class="btn hasTooltip" id="jobokay_search_clear" type="button" title=" <?php echo JText::_('JSEARCH_FILTER_CLEAR') ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
  </div>
</div>
</form>
</div><br>
