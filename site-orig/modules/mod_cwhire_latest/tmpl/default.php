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
    foreach ($entries as $listentry) {
      $startdate = date('d.m.Y', strtotime($listentry->start_date));

      // $search = array("<ul>","</ul>","<li>");
      // $shortdesc_1 = str_replace($search, "", $listentry->tasks);
      //
      // $search2 = array("</li>","</ li>","< / li>","< /li>");
      // $shortdesc_2 = trim(str_replace($search2, ",", $shortdesc_1));

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
// 	      if(!(int)$listentry->start_date) { $startdate = JText::_('MOD_CWHIRE_LATEST_ASAP'); }
      if(!(int)$listentry->start_date || $listentry->start_date == '1970-01-01') { $startdate = JText::_('MOD_CWHIRE_LATEST_ASAP'); }

      echo "<li class=\"joboffer\">
      <h3><a class=\"jobtitle\" href=\"".JRoute::_('index.php?option=com_cwhire&view=joblisting&id=' . (int)$listentry->id)."\">".$listentry->title."</a></h3>
      <div class=\"jobokay_latest_header\">".$contracts['title'][$listentry->contract]." ".JText::_('MOD_CWHIRE_LATEST_IN')." ".$listentry->location." ".JText::_('MOD_CWHIRE_LATEST_START')." ".$startdate."</div>
      <div class=\"jobokay_latest_details\">".$shortdesc."
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
    <br>
    <a href="<?php echo JRoute::_('index.php?option=com_cwhire&view=joblistings'); ?>"><?php echo $moretext; ?>...
    </a>
  </li>
  <?php endif; ?>
</ul>
