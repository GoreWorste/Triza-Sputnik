<?php
/**
 * @author     createweb - rainer haage
 * @link       http://www.createweb.de
 * @copyright  Copyright (C) 2017-2021 createweb - rainer haage. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<?php
// echo $hello;
$catstate = '';
$locstate = '';

$jobcategoryOptions = $helper[0];
$joblocationOptions = $helper[1];
$field_class = $params->get('field_class');
$data_width = $params->get('data_width');
$data_minwidth = $params->get('data_minwidth');
$data_maxwidth = $params->get('data_maxwidth');
$data_height = $params->get('data_height');
$data_minheight = $params->get('data_minheight');
$data_maxheight = $params->get('data_maxheight');
$data_ratio = $params->get('data_ratio');
$data_fit = $params->get('data_fit');
$data_thumbfit = $params->get('data_thumbfit');
$data_startindex = $params->get('data_startindex');
$data_margin = $params->get('data_margin');
$data_glimpse = $params->get('data_glimpse');
$data_nav = $params->get('data_nav');
$data_navposition = $params->get('data_navposition');
$data_navwidth = $params->get('data_navwidth');
$data_thumbwidth = $params->get('data_thumbwidth');
$data_thumbheight = $params->get('data_thumbheight');
$data_thumbborderwidth = $params->get('data_thumbborderwidth');
$data_transition = $params->get('data_transition');
$data_transitionduration = $params->get('data_transitionduration');
$data_allowfullscreen = $params->get('data_allowfullscreen');
$data_autoplay = $params->get('data_autoplay');
$data_autoplay_time = $params->get('data_autoplay_time');
$data_stopautoplayontouch = $params->get('data_stopautoplayontouch');
$data_loop = $params->get('data_loop');
$data_shuffle = $params->get('data_shuffle');
$data_navigation = $params->get('data_navigation');
$data_keyboard = $params->get('data_keyboard');
$data_arrows = $params->get('data_arrows');
$data_click = $params->get('data_click');
$data_swipe = $params->get('data_swipe');
$data_trackpad = $params->get('data_trackpad');
$data_direction = $params->get('data_direction');
$data_lazyload = $params->get('data_lazyload');
$images = $params->get('images');
$image_folder = $params->get('image_folder');

// if ($data_lazyload == true) {
//   echo "<b>Lazyload!</b>";
// }
// else {
//   echo "<b>Lazyload off!</b>";
// }
// $module = JModuleHelper::getModule();

// echo "<b>Navigation: ".$data_navigation[1]."</b>\n";
// echo "<b>ID: '".$module->id."'</b>\n";
if ($params->get('image_source') == 'images' || $params->get('image_source') == 'somedir') {
  $field_class = 'fotorama'.$module->id;
}

switch ($params->get('image_source')) {
  case 'images':
    $field_class = 'fotorama'.$module->id;
    break;
  case 'somedir':
    $field_class = 'fotorama'.$module->id;
    break;
  case 'cfield':
    $field_class = $params->get('field_class')." .field-value";
    break;
  default:
    # code...
    break;
}

if (!isset($field_class)) {
  $field_class = 'futurama';
}
?>
<script type="text/javascript">
(function($)
{
	$(document).ready(function()
	{
    $(function () {
  		$('.<?php echo $field_class; ?>').fotorama({
        <?php
        if (!empty($data_width)) {
          echo "width: '".$data_width."',\n";
        }
        if (!empty($data_minwidth)) {
          echo "minwidth: '".$data_minwidth."',\n";
        }
        if (!empty($data_maxwidth)) {
          echo "maxwidth: '".$data_maxwidth."',\n";
        }
        if (!empty($data_height)) {
          echo "height: '".$data_height."',\n";
        }
        if (!empty($data_minheight)) {
          echo "minheight: '".$data_minheight."',\n";
        }
        if (!empty($data_maxheight)) {
          echo "maxheight: '".$data_maxheight."',\n";
        }
        if (!empty($data_ratio)) {
          echo "ratio: '".$data_ratio."',\n";
        }
        if (!empty($data_fit)) {
          echo "fit: '".$data_fit."',\n";
        }
        if (!empty($data_thumbfit)) {
          echo "thumbfit: '".$data_thumbfit."',\n";
        }
        if (!empty($data_startindex)) {
          echo "startindex: ".$data_startindex.",\n";
        }
        if (!empty($data_margin)) {
          echo "margin: '".$data_margin."',\n";
        }
        if (!empty($data_glimpse)) {
          echo "glimpse: '".$data_glimpse."',\n";
        }
        if (!empty($data_transitionduration)) {
          echo "transitionduration: '".$data_transitionduration."',\n";
        }
        if ($data_loop == 1) {
          echo "loop: true,\n";
        }
        else {
          echo "loop: false,\n";
        }
        if ($data_shuffle == 1) {
          echo "shuffle: true,\n";
        }
        else {
          echo "shuffle: false,\n";
        }
        if ($data_keyboard == 1) {
          echo "keyboard: true,\n";
        }
        else {
          echo "keyboard: false,\n";
        }
        if ($data_arrows == 1) {
          echo "arrows: true,\n";
        }
        else {
          echo "arrows: false,\n";
        }
        if ($data_click == 1) {
          echo "click: true,\n";
        }
        else {
          echo "click: false,\n";
        }
        if ($data_swipe == 1) {
          echo "swipe: true,\n";
        }
        else {
          echo "swipe: false,\n";
        }
        if ($data_trackpad == 1) {
          echo "trackpad: true,\n";
        }
        else {
          echo "trackpad: false,\n";
        }
        if ($data_allowfullscreen == 1) {
          echo "allowfullscreen: true,\n";
        }
        else {
          echo "allowfullscreen: false,\n";
        }
        if ($data_autoplay == 1) {
          if (!empty($data_autoplay_time)) {
            echo "autoplay: '".$data_autoplay_time."',\n";
          }
          else {
            echo "autoplay: true,\n";
          }

        }
        if ($data_stopautoplayontouch == 1) {
          echo "stopautoplayontouch: true,\n";
        }
        else {
          echo "stopautoplayontouch: false,\n";
        }
        echo "nav: '".$data_nav."',\n";
        echo "navposition: '".$data_navposition."',\n";
        if (!empty($data_navwidth)) {
          echo "navwidth: '".$data_navwidth."',\n";
        }
        if (!empty($data_thumbwidth)) {
          echo "thumbwidth: '".$data_thumbwidth."',\n";
        }
        if (!empty($data_thumbheight)) {
          echo "thumbheight: '".$data_thumbheight."',\n";
        }
        if (!empty($data_thumbborderwidth)) {
          echo "thumbborderwidth: '".$data_thumbheight."',\n";
        }
        echo "direction: '".$data_direction."',\n";
        echo "transition: '".$data_transition."'\n";
        ?>
			});
		});
  })
})(jQuery);
</script>
<?php if ($params->get('image_source') == 'images') : ?>
  <div class="fotorama<?php echo $module->id ?>">
    <?php
      foreach ($images as $key => $item) {
        if ($data_lazyload == true) {
          echo '<a href="'.$item->image.'" data-caption="'.$item->image_caption.'"></a>';
        }
        else {
          echo '<img src="'.$item->image.'" data-caption="'.$item->image_caption.'">';
        }
      }
    ?>
  </div>
<?php elseif ($params->get('image_source') == 'somedir') : ?>
  <div class="fotorama<?php echo $module->id ?>">
    <?php
      foreach ($image_folder as $key => $folder) {
        $files = JFolder::files('images/'.$folder, '.', false, true);
        foreach ($files as $key => $image) {
          if ($data_lazyload == true) {
            echo '<a href="'.$image.'" class="lazyload"></a>';
          }
          else {
            echo '<img src="'.$image.'" class="nolazy">';
          }
        }

      }
    ?>
  </div>

<?php endif; ?>
