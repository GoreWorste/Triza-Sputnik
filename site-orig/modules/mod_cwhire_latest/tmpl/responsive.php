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
$cols = $params->get('columns');
if ($cols != "") {
  $columns = " ".$cols;
} else {
  $columns = $cols;
}
?>
<ul class="jobokay_latest_responsive" style="list-style-type: none;">

<?php
if ($entries) {
    foreach ($entries as $listentry)
    {
      $startdate = date('d.m.Y', strtotime($listentry->start_date));

      // $search = array("<ul>","</ul>","<li>");
      // $shortdesc_1 = str_replace($search, "", $listentry->tasks);
      //
      // $search2 = array("</li>","</ li>","< / li>","< /li>");
      // $shortdesc_3 = trim(str_replace($search2, ",", $shortdesc_1));

      $shortdesc_2 = strip_tags($listentry->tasks);
      $shortdesc_3 = $listentry->description;
      $shortdesc_4 = $listentry->shortdesc;

      // $subend = -1;
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
// 	      if(!(int)$listentry->start_date) { $startdate = JText::_('MOD_CWHIRE_LATEST_ASAP'); }
      if(!(int)$listentry->start_date || $listentry->start_date == '1970-01-01') { $startdate = JText::_('MOD_CWHIRE_LATEST_ASAP'); }

      echo "<li class=\"joboffer".$columns."\">";
      if ($listentry->image) {
        echo "<img class=\"jobimage\" src=\"".$listentry->image."\">";
      }
      echo "<h4><a class=\"jobtitle\" href=\"index.php?option=com_cwhire/".$listentry->id."?view=joblisting\">".$listentry->title."</a></h4>
      <div class=\"jobokay_latest_header\">".$contracts['title'][$listentry->contract]." ".JText::_('MOD_CWHIRE_LATEST_IN')." ".$listentry->location."</div>
      <div class=\"jobokay_latest_details\">".$shortdesc_3."
      </div>
      <div class=\"jobokay_latest_footer\">
      <div class=\"jobokay_latest_specifics\">";
								// echo JText::_('MOD_CWHIRE_LATEST_FROM')." "; if(!(int)$listentry->start_date || $listentry->start_date == '1970-01-01') echo JText::_('MOD_CWHIRE_LATEST_ASAP'); else echo date('d.m.Y', strtotime($listentry->start_date));
                // echo " in ".$listentry->location;
      echo "</div>
      <div class=\"jobokay_latest_readmore\"><a class=\"readmore\" href=\"index.php?option=com_cwhire/".$listentry->id."?view=joblisting\">". JText::_('MOD_CWHIRE_LATEST_READMORE')
      ."</a></div>
      </div>
      </li>";

    }
  }
  else {
      $moretext = JText::_('MOD_CWHIRE_LATEST_NOMOREOFFERS');
  }
  if ($show_moreoffers == 1) :
?>
<li>
  <a href="index.php?option=com_cwhire&view=joblistings"><?php echo $moretext; ?>
  </a>
</li>
<?php endif; ?>
</ul>
