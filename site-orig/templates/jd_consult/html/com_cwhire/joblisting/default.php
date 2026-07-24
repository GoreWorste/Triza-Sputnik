<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/
// no direct access
defined('_JEXEC') or die;


$use_captcha=$this->params->get('use_captcha');

if ($use_captcha == 1) {
  JPluginHelper::importPlugin('captcha');
  // $dispatcher = JDispatcher::getInstance();
  $dispatcher = JEventDispatcher::getInstance();
  $dispatcher->trigger('onInit','dynamic_recaptcha_1');
  $recaptcha = $dispatcher->trigger('onDisplay', array(null, 'dynamic_recaptcha_1', 'class=""'));
}

// jimport( 'joomla.form.form' );
// $cform   =& JForm::getInstance('captchaform',JPATH_COMPONENT.'/models/forms/captcha.xml');

require_once JPATH_COMPONENT.'/helpers/cwhire.php';
require_once JPATH_COMPONENT.'/models/joblisting.php';

$helper = new JoblistingHelper();
$used_fields = $helper->usedfields();

$required_fields = $helper->mustfields();

$use_custom = $this->params->get('use_custom');
$custom_fields = NULL;
if ($use_custom == 1) {
  $custom_fields = $this->params->get('custom_fields');
  error_log(print_r($custom_fields, 1), 0);
}

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_cwhire', JPATH_ADMINISTRATOR);
$mailerror = false;
$errortext = "";

$privpol_id=$this->params->get('documentpp');

$model = $this->getModel('Joblisting', 'CwhireModel');
$filetypes_ids = $this->params->get('attachment-types');
$filetypes = $model->getExtensions($filetypes_ids);
$allowed_types = $model->getMimetypes($filetypes_ids);
$filetypes_string = implode(", ", $filetypes);
$require_pp = $this->params->get('requirepp');
$contracts = json_decode( $this->params->get('contract-forms'),true);
$showref =  $this->params->get('show_reference');
$attachment_types =  $this->params->get('appliform_atype');

// $required_fields = array('appliform_fname','appliform_sname','appliform_street','appliform_plz','appliform_address','appliform_mail');



$form_action = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$vitalog = "";
$testilog = "";

$document = JFactory::getDocument();
if ($this->item->shortdesc != "") {
    $document->setDescription($this->item->shortdesc);
}
?>

<script>
    var textarea_clicked = false;
    var vita_count = 1;
    var testi_count = 1;
    var appliform_vita = '<?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_VITA') ?>';
    var appliform_testi = '<?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_TESTIMONY') ?>';

    jQuery(document).ready( function() {
      var appliform_params = {
        appliform : '#application_form',
        titleVita : appliform_vita,
        titleTesti : appliform_testi
      }
      jQuery('#application_form').appform(appliform_params);

    });
    var formHidden = true;
</script>

<?php
if (isset($_GET['sent'])) :
  if (isset($_GET['statusmessage'])) {
    // JLog::add(JText::_('statusmessage: '.$_GET['statusmessage']), JLog::INFO, 'com_jobokay');
    echo "<mailresult>".$_GET['statusmessage']."</mailresult>";
  }
  else {
    echo "<mailresult>".JText::_("COM_CWHIRE_MAIL_SEND_ERROR")."</mailresult>";
  }

?>
<?php else: ?>

<?php if ($mailerror == false) : ?>
<?php if ($this->item) : ?>
  <div id="cwhire_joblication">
    <a href="index.php?option=com_cwhire&view=joblistings"><b><?php echo JText::_('COM_CWHIRE_JOBLISTING_BACKTOLIST') ?></b></a><br><br>
    <div class="item_fields">
		<?php if($this->item->image): ?>
		    <img class="item_image" src="<?php echo $this->item->image; ?>">
		<?php endif; ?>
    <?php

      $pp_style = "";
      $pp_value_string = "";
      if ($require_pp == 0) {
        $pp_style = " style=\"display:none;\"";
        $pp_value_string = " checked=checked";
      }
    ?>
	
    <div class="item_header" id="item_header_<?php echo $this->item->id; ?>">
      <div class="itemlist_title">
          <h3 class="jobtitle"><?php echo $this->item->title; ?></h3>
      </div>

      <div class="item_header_textstr">
    <!-- 								<a href="<?php echo JRoute::_('index.php?option=com_cwhire&view=joblisting&id=' . (int)$this->item->id); ?>">in <?php echo $this->item->location; ?></a> -->
        <?php echo JText::_('COM_CWHIRE_IN'); ?> <span itemtype="http://schema.org/Place" itemscope="" itemprop="jobLocation"><?php echo $this->item->location; ?></span>
      </div>
      <div class="item_header_textstr">
        <?php
        echo $contracts['title'][$this->item->contract];
        ?>
      </div>
      <div class="item_header_textstr">
        <?php echo JText::_('COM_CWHIRE_START_DATE').' '; if(!(int)$this->item->start_date || $this->item->start_date <= '1970-01-01') echo JText::_('COM_CWHIRE_LIST_IMMEDIATELY'); else echo date('d.m.Y', strtotime($this->item->start_date)); ?>
      </div>
      <div class="item_header_textstr" style="clear: right;">
        <?php
        if ($showref == 1) {
          echo JText::_('COM_CWHIRE_JOBLISTING_REFID')." ". $this->item->refname;
        }
        ?>
      </div>
    </div>

		<div class="jobdescription"><b><?php echo JText::_('COM_CWHIRE_FORM_LBL_JOBLISTING_DESCRIPTION') ?></b><br /> <?php echo $this->item->description; ?></div>
		<div class="jobtasks"><b><?php echo JText::_('COM_CWHIRE_FORM_LBL_JOBLISTING_TASKS') ?></b><br /> <?php echo $this->item->tasks; ?></div>
		<div class="jobprofile"><b><?php echo JText::_('COM_CWHIRE_FORM_LBL_JOBLISTING_PROFILE') ?></b><br /> <?php echo $this->item->profile; ?></div>
		<?php if($this->item->perspective): ?>
		    <div class="jobperspective"><b><?php echo JText::_('COM_CWHIRE_JOBLISTING_PERSPECTIVE') ?>:</b><br /> <?php echo $this->item->perspective; ?></div>
		<?php endif; ?>
		<div class="job_contactinfo"><?php echo JText::_('COM_CWHIRE_STANDART_TEXT') ?></div>
	<!---	<div class="job_contactinfo"><?php echo $this->item->contactinfo; ?></div>  --->
    <?php if (false): ?>
		<div id="formhider"><?php echo JText::_('COM_CWHIRE_OPEN_AFORM') ?>
      <i class="fa fa-caret-right" aria-hidden="true"></i></div>
		<form class="item_form" id="application_form" name="application_form" enctype="multipart/form-data" method="post" action="<?php echo $form_action ?>">
		    <br>

        <?php if (in_array("appliform_title", $used_fields)): ?>
		    <p class="formlabel"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_GENDER') ?>:</p>
		    <select id="appliform_title" name="appliform_title" <?php if (in_array("appliform_title", $required_fields)) { echo " required=\"true\""; } ?>>
    			<option value="<?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_MRS') ?>"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_MRS') ?></option>
    			<option value="<?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_MR') ?>"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_MR') ?></option>
		    </select> <?php if (in_array("appliform_title", $required_fields)) { echo " *"; } ?>
        <br>
      <?php endif; ?>

        <?php if (in_array("appliform_fname", $used_fields) && in_array("appliform_sname", $used_fields)): ?>
		    <p class="formlabel"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_NAME') ?>:</p>
      <?php elseif (in_array("appliform_fname", $used_fields) || in_array("appliform_sname", $used_fields)): ?>
        <p class="formlabel"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_SIMPLENAME') ?>:</p>
        <?php endif; ?>

        <?php if (in_array("appliform_fname", $used_fields)): ?>
		    <input type="text" class="appliform_field" id="appliform_fname" name="appliform_fname" <?php if (in_array("appliform_fname", $required_fields)) { echo " required=\"true\""; } ?>></input> <?php if (in_array("appliform_fname", $required_fields)) { echo " *"; } ?>
        <?php
        if (!in_array("appliform_sname", $used_fields)) {
          echo "<br>";
        }
        endif;
        ?>

        <?php if (in_array("appliform_sname", $used_fields)): ?>
		    <input type="text" class="appliform_field" id="appliform_sname"  name="appliform_sname" <?php if (in_array("appliform_sname", $required_fields)) { echo " required=\"true\""; } ?>></input><?php if (in_array("appliform_sname", $required_fields)) { echo " *"; } ?>
        <br>
        <?php endif; ?>

        <?php if (in_array("appliform_street", $used_fields)): ?>
		    <p class="formlabel"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_STREET') ?>:</p>
		    <input type="text" class="appliform_field" id="appliform_street" name="appliform_street" <?php if (in_array("appliform_street", $required_fields)) { echo " required=\"true\""; } ?>></input><?php if (in_array("appliform_street", $required_fields)) { echo " *"; } ?><br>
        <?php endif; ?>

        <?php if (in_array("appliform_country", $used_fields) || in_array("appliform_plz", $used_fields) || in_array("appliform_address", $used_fields)):
            $address_string = "";
          if (in_array("appliform_country", $used_fields)) {
            $address_string = JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_COUNTRY');
          };
          if (in_array("appliform_plz", $used_fields)) {
            if (in_array("appliform_country", $used_fields)) {
              $address_string .= ", ";
            };
            $address_string .= JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_ZIP');
          };
          if (in_array("appliform_address", $used_fields)) {
            if (in_array("appliform_country", $used_fields) || in_array("appliform_plz", $used_fields)) {
              $address_string .= ", ";
            };
            $address_string .= JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_CITY');
          };

        ?>
        <p class="formlabel"><?php echo $address_string ?>:</p>
        <?php endif; ?>

        <?php if (in_array("appliform_country", $used_fields)): ?>

		    <input type="text" class="appliform_field" id="appliform_country"  name="appliform_country" value="" <?php if (in_array("appliform_country", $required_fields)) { echo " required=\"true\""; } ?>></input><?php if (in_array("appliform_country", $required_fields)) { echo " *"; } ?>
        <?php
        if (
          !in_array("appliform_plz", $used_fields) && !in_array("appliform_address", $used_fields)
        ) {
          echo "<br>";
        }
        endif;
        ?>

        <?php if (in_array("appliform_plz", $used_fields)): ?>
		    <input type="text" class="appliform_field" id="appliform_plz" name="appliform_plz" <?php if (in_array("appliform_plz", $required_fields)) { echo " required=\"true\""; } ?>></input><?php if (in_array("appliform_plz", $required_fields)) { echo " *"; } ?>
        <?php
        if (
          (!in_array("appliform_address", $used_fields) )
        ) {
          echo "<br>";
        }
        endif;
        ?>

        <?php if (in_array("appliform_address", $used_fields)): ?>
		    <input type="text" class="appliform_field" id="appliform_address" name="appliform_address" <?php if (in_array("appliform_address", $required_fields)) { echo " required=\"true\""; } ?>></input><?php if (in_array("appliform_address", $required_fields)) { echo " *"; } ?>
        <br>
        <?php
        // if (!in_array("appliform_country", $used_fields) || !in_array("appliform_plz", $used_fields)) {
        //   echo "<br>";
        // }
        endif;
        ?>

        <?php if (in_array("appliform_phone", $used_fields)): ?>
		    <p class="formlabel"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_PHONE') ?>:</p>
		    <input type="text" class="appliform_field" id="appliform_phone"  name="appliform_phone" <?php if (in_array("appliform_phone", $required_fields)) { echo " required=\"true\""; } ?>></input><?php if (in_array("appliform_phone", $required_fields)) { echo " *"; } ?><br>
        <?php endif; ?>

        <?php if (in_array("appliform_mail", $used_fields)): ?>
		    <p class="formlabel"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_MAIL') ?>:</p>
		    <input type="text" class="appliform_field" id="appliform_mail"  name="appliform_mail" <?php if (in_array("appliform_mail", $required_fields)) { echo " required=\"true\""; } ?>></input><?php if (in_array("appliform_mail", $required_fields)) { echo " *"; } ?><br>
        <?php endif; ?>
        <?php
        if (isset($custom_fields)) {
          foreach ($custom_fields as $key => $field) {
            if (isset($field->use_field) && $field->title != "" && $field->fieldname != "") {
              $requiretext = "";
              $requiremark = "";
              if (isset($field->must_field)) {
                $requiretext =' required="true"';
                $requiremark = " *";
              }
              echo '<p class="formlabel">'.$field->title.':</p>
              <input type="text" class="appliform_field" id="'.$field->fieldname.'"  name="'.$field->fieldname.'"'.$requiretext.'></input>'.$requiremark.'<br>';
            }
          }
        }
        ?>

    <?php echo JText::_('COM_CWHIRE_JOBLISTING_REQUIRED_FIELD_NOTICE') ?>
<!--		    <p class="formlabel"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_WWW') ?>:</p>
    <input type="text" class="appliform_field" id="appliform_www"  name="appliform_www"></input><br>-->

    <?php if (in_array("appliform_attachments", $used_fields)): ?>
    <hr>
    <b><?php echo JText::_('COM_CWHIRE_AFORM_TITLE_ATTACHEMENTS') ?></b>

    <?php if ($attachment_types == 3): ?>
      <br><br>
	    <input type="radio" name="uploadtype" id="uploadtype1" value="upld" checked>
	    <span class="formlabel_right" id="formlabel_upload"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_UPLOAD') ?></span>
	    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	    <input type="radio" name="uploadtype" id="uploadtype2" value="lnk">
	    <span class="formlabel_right" id="formlabel_link"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_EXTLINK') ?></span>
      <br><br>
    <?php elseif ($attachment_types == 1): ?>
      <input type="hidden" name="uploadtype" id="uploadtype" value="upld"><br><br>
    <?php elseif ($attachment_types == 2): ?>
      <input type="hidden" name="uploadtype" id="uploadtype" value="lnk">
      <br>
    <?php endif; ?>



    <?php if (in_array($attachment_types, [1,3])): ?>
    <span id="form_uploadgroup">
    <?php if (in_array("appliform_vita", $used_fields)): ?>
    <div id="form_uploadgroup_vita">
    <p class="formlabel"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_VITA') ?> 1:</p>
    <input type="file" class="appliform_upload appliform_vita" id="appliform_vita_1"  name="appliform_vita[]" size="40"  multiple="multiple" vita_count="1"></input>
    <!--<input type="button" id="button_clr_1" name="button_clr" value="X"></input>-->
    </div><br>
    <?php endif; ?>
    <?php if (in_array("appliform_testi", $used_fields)): ?>
    <div id="form_uploadgroup_testi">
    <p class="formlabel"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_TESTIMONY') ?> 1:</p>
    <input type="file" class="appliform_upload appliform_testi" id="appliform_testi_1" name="appliform_testi[]" size="40"  multiple="multiple" testi_count="1"></input>
    </div><br>
    <?php endif; ?>
    </span>
    <?php echo JText::_('COM_CWHIRE_JOBLISTING_ALLOWED_FILETYPES').": ". $filetypes_string ?>
    <br>
    <?php endif; ?>
    <?php
      if (in_array($attachment_types, [2,3])):
      $linkhider = "";
      if (in_array($attachment_types, [3])) {
        $linkhider = ' style="display: none;"';
      }
    ?>
    <span id="form_linkgroup"<?php echo $linkhider; ?> >
      <br>
    <p class="formlabel"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_EXTLINK') ?>:</p>
    <input type="text" class="appliform_field_long" id="appliform_extlink"  name="appliform_extlink" value="http://"></input><br>
    </span>
    <?php endif; ?>

    <hr>
    <?php
    else :
      echo "<br>";
    endif;
    ?>

    <p class="formlabel"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_APPLICATION') ?>:</p>
    <textarea class="appliform_textarea" id="application_text"  name="application_text" <?php if (in_array("appliform_textarea", $required_fields)) { echo " required=\"true\""; } ?>><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_DEFAULTTEXT_EXTRAINFO') ?></textarea><?php if (in_array("application_text", $required_fields)) { echo " *"; } ?><br>
<!-- 		    <a href="index.php?option=com_content&view=article&id=<?php echo $privpol_id; ?>">Datenschutzerkl&auml;rung</a> -->
    <span id="privacyfields" <?php echo $pp_style; ?>>
	    <input type="checkbox"<?php echo $pp_value_string; ?> id="privacy_policy_accepted" name="privacy_policy_accepted" /> <?php echo JText::_('COM_CWHIRE_MAIL_ACCEPT_PP_PREFIX') ?>
	    <a onclick="window.open(this.href,'','scrollbars=yes,resizable=yes,location=no,menubar=no,status=no,toolbar=no,left='+(screen.availWidth/2-400)+',top='+(screen.availHeight/2-400)+',width=800,height=800');return false;"
	    href="index.php?option=com_content&amp;view=article&amp;id=<?php echo $privpol_id; ?>"><?php echo JText::_('COM_CWHIRE_MAIL_PP') ?></a><br>
    </span>

    <?php if ($use_captcha == 1) { ?>
    <span id="form_captchagroup">
    <p class="formlabel"><?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_LABEL_RECAPTCHA') ?>:</p>
    <?php echo (isset($recaptcha[0])) ? $recaptcha[0] : ''; ?>
    <br>
    </span>
    <?php } ?>

    <input type="hidden" id="task"  name="task" value="joblisting.send"></input>
    <!-- <input type="submit" id="button_submit" name="button_submit" value="<?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_SUBMIT') ?>" onclick="javascript: checkMail(); return false;"> -->
    <input type="submit" id="button_submit" name="button_submit" value="<?php echo JText::_('COM_CWHIRE_AFORM_JOBLISTING_SUBMIT') ?>">

		</form>
		<a id="formanchor"></a>
		    <br><br>
		    <div class="progress">
			<div class="bar"></div >
			<div class="percent"> </div >

		    </div>
		    <div id="status"></div>
            <?php endif; ?>
    </div>
  </div>

    <script>
    function checkMail() {
      // console.log('checkMail...');
      // var required_fields = new Array('<?php echo implode(',', $required_fields); ?>');
      var incomplete = false;
      var ppempty = false;
      var warningtext = '';

      Joomla.submitbutton = function( task ) {
        // console.log('task: '+task);
        form = document.getElementById("application_form");
        Joomla.submitform(task, form);

    	};




      <?php if ($use_captcha == 1) { ?>
      captchacheck = jQuery('.g-recaptcha-response').val();

      if (captchacheck == '') {
        if (incomplete == true) {
          warningtext += '\n\n';
        }
        warningtext += '<?php echo JText::_('COM_CWHIRE_CAPTCHA_NOBOT'); ?>';
        incomplete = true;
      }
      <?php } ?>

      if( !jQuery('#privacy_policy_accepted').is(':checked')) {
        ppempty = true;
        if (incomplete == true) {
          warningtext += '\n\n';
        }
        warningtext += '<?php echo JText::_('COM_CWHIRE_MAIL_MISSING_PRIVACY'); ?>';
        incomplete = true;

      }


      jQuery('.appliform_vita').each( function() {

        if( jQuery(this).val().length != 0 ) {
          var vitafile = jQuery(this).val();
          if (!isImage(vitafile)) {
            if (incomplete == true) {
              warningtext += '\n\n';
            }
            currid = jQuery(this).attr('vita_count');
            warningtext += '<?php echo JText::_('COM_CWHIRE_MAIL_YOUR_CV'); ?> '+currid+' <?php echo JText::_('COM_CWHIRE_MAIL_INVALID_FILE'); ?>';
            incomplete = true;
            jQuery(this).addClass('warning');
            jQuery(this).blur(function()
            {
              if( jQuery(this).val().length != 0 ) {
                jQuery(this).removeClass('warning');
              }
            });
          }
        }

      });

      jQuery('.appliform_testi').each( function() {

        if( jQuery(this).val().length != 0 ) {
          var testifile = jQuery(this).val();
          if (!isImage(testifile)) {
            if (incomplete == true) {
              warningtext += '\n\n';
            }
            currid = jQuery(this).attr('testi_count');
            warningtext += '<?php echo JText::_('COM_CWHIRE_MAIL_YOUR_TESTI'); ?> '+currid+' <?php echo JText::_('COM_CWHIRE_MAIL_INVALID_FILE'); ?>';
            incomplete = true;
            jQuery(this).addClass('warning');
            jQuery(this).blur(function()
            {
              if( jQuery(this).val().length != 0 ) {
                jQuery(this).removeClass('warning');
              }
            });
          }
        }

      });



      if (incomplete == false) {
        // Joomla.submitbutton('joblisting.send');
        return true;
      }
      else {
        alert(warningtext);
        return false;
      }
      return true;
    }

	function getExtension(filename) {
	    var parts = filename.split('.');
	    return parts[parts.length - 1];
	}

	function isImage(filename) {
	    var ext = getExtension(filename).toLowerCase();
    var filetypes = <?php echo json_encode($filetypes); ?>;
      if (jQuery.inArray( ext, filetypes ) != -1) {
        return true;
      }
	    return false;
	}
    </script>

<?php
else:
    echo JText::_('COM_CWHIRE_ITEM_NOT_LOADED');
endif;
endif;
endif;
?>
