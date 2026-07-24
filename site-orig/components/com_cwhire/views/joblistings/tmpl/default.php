<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/
// no direct access
defined('_JEXEC') or die;

//Get jobcategory options
JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');
$jobcategories = JFormHelper::loadFieldType('Jobcategory', false);
$jobcategoryOptions=$jobcategories->getOptions(); // works only if you set your field getOptions on public!!

$contracts = json_decode( $this->params->get('contract-forms'),true);
$showref =  $this->params->get('show_reference');
$list_hidedesc = $this->params->get('list_hidedesc');
$char_limit = $this->params->get('char_limit');
// //Find the backtrace
// $backtrace = debug_backtrace();
// //print the bad data, and the calling function
// print_r("Data output from calling function \"" . $backtrace[1]['function'] . "\" is: <pre>" . $jobcategoryOptions . "</pre>");


$joblocations = JFormHelper::loadFieldType('Joblocation', false);
$joblocationOptions=$joblocations->getOptions(); // works only if you set your field getOptions on public!!
?>

<script>
    // jQuery(document).ready( function() {
	  //   jQuery('.itemlist_descriptionblock').hide();
    // });
</script>
<div class="well" id="jobsearch_well">
<form action="<?php echo JRoute::_('index.php?option=com_cwhire&view=joblistings'); ?>" method="post" name="jobokSearchForm" id="jobokSearchForm">
<div id="jobokay_search_container">
<div id="filter-bar" class="btn-toolbar">
	<div class="btn-group pull-left">

	      <select name="filter_jobcategory" class="inputbox" onchange="this.form.submit()">
		      <option value=""> - <?php echo JText::_('COM_CWHIRE_SEARCH_ALL_INDUSTRIES') ?> - </option>
		      <?php echo JHtml::_('select.options', $jobcategoryOptions, 'value', 'text', $this->state->get('filter.jobcategory'));?>
	      </select>
	</div>
	<div class="btn-group pull-left">

				<select name="filter_joblocation" class="inputbox" onchange="this.form.submit()">
					<option value=""> - <?php echo JText::_('COM_CWHIRE_SEARCH_ALL_LOCATIONS') ?> - </option>
					<?php echo JHtml::_('select.options', $joblocationOptions, 'value', 'text', $this->state->get('filter.joblocation'));?>
				</select>
	</div>
	<div class="filter-search btn-group pull-left">
		<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_CWHIRE_SEARCH_FILTER');?></label>
		<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_CWHIRE_SEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
	</div>
	<div class="btn-group pull-left" id="jobsearch_buttons">
		<button class="btn hasTooltip" type="submit" id="jobokay_search_submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
		<button class="btn hasTooltip" type="button" id="jobokay_search_clear" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
	</div>


</div>
</div>
</form>
</div>
<br>
<div class="items">
    <div class="items_list">

<?php $show = false; ?>
        <?php foreach ($this->items as $item) : ?>


				<?php
					if($item->state == 1 || ($item->state == 0 && JFactory::getUser()->authorise('core.edit.own',' com_cwhire'))):
						$show = true;
						?>
						<div class="itemlayer" id="itemlayer_<?php echo $item->id; ?>" itemtype="http://schema.org/JobPosting" itemscope="">
							<div class="itemlist_header" id="itemlist_header_<?php echo $item->id; ?>">
								<div class="itemlist_title">
									<h3><?php echo $item->title; ?></h3>
								</div>
								<div class="itemlist_textstr">
									<!-- 								<a href="<?php echo JRoute::_('index.php?option=com_cwhire&view=joblisting&id=' . (int)$item->id); ?>">in <?php echo $item->location; ?></a> -->
									<?php echo JText::_('COM_CWHIRE_IN'); ?> <span itemtype="http://schema.org/Place" itemscope="" itemprop="jobLocation"><?php echo $item->location; ?></span>
								</div>
								<div class="itemlist_textstr">
									<?php
									echo $contracts['title'][$item->contract];
									?>
								</div>
								<div class="itemlist_textstr">
									<?php
									echo JText::_('COM_CWHIRE_START_DATE')." ";
								if(!(int)$item->start_date || $item->start_date <= '1970-01-01') echo JText::_('COM_CWHIRE_LIST_IMMEDIATELY'); else echo date('d.m.Y', strtotime($item->start_date)); ?> 
									</div>
									<div class="itemlist_textstr" style="clear: right;">
										<?php
										if ($showref == 1) {
											echo JText::_('COM_CWHIRE_JOBLISTING_REFID')." ". $item->refname;
										}
										?>
									</div>
								</div>
								<br>
								<div class="itemlist_descriptionblock" id="itemlist_descriptionblock_<?php echo $item->id; ?>">
									<?php
									if ($list_hidedesc != 1) {
										$search = array("<ul>","</ul>","<li>");
										$shortdesc_1 = str_replace($search, "", $item->tasks);
										$search2 = array("</li>","</ li>","< / li>","< /li>");
										$shortdesc_2 = str_replace($search2, ",", $shortdesc_1);
										$shortdesc_3 = substr(trim($shortdesc_2),0,-1);


										$shortdesc_4 = strip_tags($shortdesc_3);
										$shortdesc = $shortdesc_4;
							      if ($char_limit != 0) {
							        $shortdesc = substr($shortdesc_4,0,$char_limit);
							      }
							      if (strlen($shortdesc) < strlen($shortdesc_4) ) {
							        $shortdesc .= '...';
							      }
										echo $shortdesc."
										\n<br>";
									}

									?>

									<a href="<?php echo JRoute::_('index.php?option=com_cwhire&view=joblisting&id=' . (int)$item->id); ?>"><?php echo JText::_('COM_CWHIRE_LIST_APPLICATION'); ?></a>
									<br>
								</div>
							</div>
						<hr>
						<script>

// 						    jQuery('#itemlayer_<?php echo $item->id; ?>').mouseover( function() {
// // 							  var thediv = jQuery(this).next('.itemlist_descriptionblock');
// // 							  alert(thediv.attr('class'));
// // 							  jQuery('#itemlist_descriptionblock_<?php echo $item->id; ?>').css({  'height': 'auto' });
// // 							  jQuery('#itemlist_descriptionblock_<?php echo $item->id; ?>').stop().delay(100).slideDown(500);
// 							  jQuery('#itemlist_descriptionblock_<?php echo $item->id; ?>').slideDown(500);
// 						    });
//
// // 						    jQuery('#itemlist_descriptionblock_<?php echo $item->id; ?>').mouseout( function() {
// 						    jQuery('.items_list').mouseover( function() {
// // 							  var thediv = jQuery(this).next('.itemlist_descriptionblock');
// // 							  alert(thediv.attr('class'));
// // 							  jQuery('#itemlist_descriptionblock_<?php echo $item->id; ?>').css({  'height': '1px' });
// 							  jQuery('#itemlist_descriptionblock_<?php echo $item->id; ?>').slideUp(500);
// 						    });

// 						    var downtimer;
// // 						    var uptimer;
// 						    jQuery('#itemlayer_<?php echo $item->id; ?>').hover(
// 						      function () {
// 							downtimer = setTimeout( function() {
// 							    jQuery('.itemlist_descriptionblock').slideUp(600);
// 							    jQuery('#itemlist_descriptionblock_<?php echo $item->id; ?>').slideDown(600);
// 							}, 300);
// 						      },
// 						      function () {
// 							clearTimeout(downtimer);
// 							uptimer = setTimeout( function() {
// 							    jQuery('#itemlist_descriptionblock_<?php echo $item->id; ?>').slideUp(600);
// 							}, 5000);
// 						      }
// 						    );





						</script>
					<?php endif; ?>

	<?php endforeach; ?>

    </div>
        <?php
        if (!$show):
            echo JText::_('COM_CWHIRE_NO_ITEMS');
        endif;
        ?>
</div>



<?php if ($show): ?>
    <div class="pagination">
        <p class="counter">
            <?php echo $this->pagination->getPagesCounter(); ?>
        </p>
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
<?php endif; ?>
