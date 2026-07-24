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
?>

<ul class="jobokay_latest_list" style="list-style-type: none;">
<?php
if ($entries) {
    foreach ($entries as $listentry)
    {
      $startdate = date('d.m.Y', strtotime($listentry->start_date));
// 	      if(!(int)$listentry->start_date) { $startdate = JText::_('MOD_CWHIRE_LATEST_ASAP'); }
      if(!(int)$listentry->start_date || $listentry->start_date == '1970-01-01') { $startdate = JText::_('MOD_CWHIRE_LATEST_ASAP'); }
      $bubbleId = "bubble_".$listentry->jobcategory."_".$listentry->id;
      echo "<li><a href=\"index.php?option=com_cwhire/".$listentry->id."?view=joblisting\" onMouseover=\"showBubble".$bubbleId."()\" onMouseout=\"hideBubble".$bubbleId."()\">".$listentry->title."</a>
      <div class=\"mod_jobokay_latest_bubble\" id=\"".$bubbleId."\" style=\"display:none;\">".$contracts['title'][$listentry->contract]." ".JText::_('MOD_CWHIRE_LATEST_IN')." ".$listentry->location." ".JText::_('MOD_CWHIRE_LATEST_START')." ".$startdate."</div></li>";
      echo "<script>\n
      function showBubble".$bubbleId."() {
        jQuery('#".$bubbleId."').css({ display : 'block' });
      }
      function hideBubble".$bubbleId."() {
        jQuery('#".$bubbleId."').css({ display : 'none' });
      }

      </script>";
    }
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
