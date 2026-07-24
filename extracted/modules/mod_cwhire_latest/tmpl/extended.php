<?php // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<?php //echo $hello;
/**
  * @package    mod_cwhire_latest
  * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
  * @link http://createweb.de
  * @license GNU General Public License version 3, or later
 */

$language = JFactory::getLanguage();
$language->load('com_cwhire');

$com_params = JComponentHelper::getParams('com_cwhire');
$contracts = json_decode( $com_params->get('contract-forms'),true);

$moretext = JText::_('MOD_CWHIRE_LATEST_MOREOFFERS');

$show_moreoffers = $params->get('show_moreoffers');
$char_limit = $params->get('char_limit');
?>
<ul class="jobokay_latest_full" style="list-style-type: none;">

<?php
if ($entries) {
    foreach ($entries as $listentry) :
?>
<div class="itemlayer" id="itemlayer_<?php echo $listentry->id; ?>" itemtype="http://schema.org/JobPosting" itemscope="">
  <div class="itemlist_header" id="itemlist_header_<?php echo $listentry->id; ?>">
    <div class="itemlist_title">
      <h3><?php echo $listentry->title; ?></h3>
    </div>
    <div class="itemlist_textstr">
      <?php echo JText::_('MOD_CWHIRE_LATEST_IN'); ?> <span itemtype="http://schema.org/Place" itemscope="" itemprop="jobLocation"><?php echo $listentry->location; ?></span>
    </div>
    <div class="itemlist_textstr">
      <?php
      echo $contracts['title'][$listentry->contract];
      ?>
    </div>
    <div class="itemlist_textstr">
      <?php
      echo JText::_('MOD_CWHIRE_LATEST_START_DATE')." ";
      if(!(int)$listentry->start_date || $listentry->start_date <= '1970-01-01') echo JText::_('COM_CWHIRE_LIST_IMMEDIATELY'); else echo date('d.m.Y', strtotime($listentry->start_date)); ?>
      </div>
      <div class="itemlist_textstr" style="clear: right;">
        <?php echo JText::_('COM_CWHIRE_JOBLISTING_REFID')." ". $listentry->refname; ?>
      </div>
    </div>
    <br>
    <div class="itemlist_descriptionblock" id="itemlist_descriptionblock_<?php echo $listentry->id; ?>">
      <?php

      $shortdesc_2 = strip_tags($listentry->tasks);

      if ($char_limit != 0) {
        // $rest = substr("abcdef", -3, 1);
        $subend = $char_limit;
        $shortdesc = substr($shortdesc_2,0,$subend);
      }
      else {
        $shortdesc = $shortdesc_2;
      }
      if (strlen($shortdesc) < strlen($shortdesc_2) ) {
        $shortdesc .= '...';
      }
      echo $shortdesc;
      ?>
      <br>
      <a href="<?php echo JRoute::_('index.php?option=com_cwhire&view=joblisting&id=' . (int)$listentry->id); ?>"><?php echo JText::_('COM_CWHIRE_LIST_APPLICATION'); ?></a>
      <br>
    </div>
  </div>
  <?php endforeach;
  }
  else {
      $moretext = JText::_('MOD_CWHIRE_LATEST_NOMOREOFFERS');
  }
  if ($show_moreoffers == 1) :
?>
  <li>
    <br>
    <a href="index.php?option=com_cwhire&view=joblistings"><?php echo $moretext; ?>...
    </a>
  </li>
  <?php endif; ?>
</ul>
