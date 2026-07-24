<?php
/**
 * @package    com_cwhire
 * @copyright Copyright (c)2017-2021 Createweb - Rainer Haage
 * @link http://createweb.de
 * @license GNU General Public License version 3, or later
*/
// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('/administrator/components/com_cwhire/assets/css/jobokay.css');
// $document->addScriptDeclaration("
//     jQuery('#importdesc').click( function() {
//         alert('An inline JavaScript Declaration');
//     });
// ");

?>
<script type="text/javascript">
js = jQuery.noConflict();
js(document).ready(function(){
  jQuery('#importdesc').click( function() {
    // 	    alert('An inline JavaScript Declaration');
    var autodescription = "<?php echo JText::_('COM_CWHIRE_FORM_META_DESC_JOBOFFER') ." ". JText::_('COM_CWHIRE_FORM_META_DESC_AS') ?> "+jQuery('#jform_title').val()+" <?php echo JText::_('COM_CWHIRE_FORM_META_DESC_LOCATION') ?> "+jQuery('#jform_location').val()+" <?php echo JText::_('COM_CWHIRE_FORM_META_DESC_CONTRACT') ?> "+jQuery('#jform_contract').val();
    jQuery('#jform_shortdesc').val(autodescription);
  });
});

Joomla.submitbutton = function(task)
{
  if(task == 'joblisting.cancel'){
    Joomla.submitform(task, document.getElementById('joblisting-form'));
  }
  else{

    if (task != 'joblisting.cancel' && document.formvalidator.isValid(document.id('joblisting-form'))) {
      Joomla.submitform(task, document.getElementById('joblisting-form'));
    }
    else {
      alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
    }
  }
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_cwhire&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="joblisting-form" class="form-validate">

    <div class="form">

      <div class="control-group">
        <div class="control-label"><?php echo $this->form->getLabel('refname'); ?></div>
        <div class="controls"><?php echo $this->form->getInput('refname'); ?></div>
      </div>

      <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'config')); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'config', empty($this->item->id) ? JText::_('COM_CWHIRE_FORM_LBL_JOBLISTING_CONFIG') : JText::_('COM_CWHIRE_FORM_LBL_JOBLISTING_CONFIG')); ?>

      <div class="row-fluid form-horizontal">

        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
          <div class="controls"><?php echo $this->form->getInput('id'); ?></div>
        </div>
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
          <div class="controls"><?php echo $this->form->getInput('state'); ?></div>
        </div>
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('homepage'); ?></div>
          <div class="controls"><?php echo $this->form->getInput('homepage'); ?></div>
        </div>
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('jobcategory'); ?></div>
          <div class="controls"><?php echo $this->form->getInput('jobcategory'); ?></div>
        </div>

        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('location'); ?></div>
          <div class="controls"><?php echo $this->form->getInput('location'); ?></div>
        </div>
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('contract'); ?></div>
          <div class="controls"><?php echo $this->form->getInput('contract'); ?></div>
        </div>
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('start_date'); ?></div>
          <div class="controls"><?php echo $this->form->getInput('start_date'); ?></div>
        </div>
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('language'); ?></div>
          <div class="controls"><?php echo $this->form->getInput('language'); ?></div>
        </div>
        <hr />
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('created'); ?></div>
          <br />
          <div class="controls"><?php echo $this->form->getInput('created'); ?></div>
        </div>
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
          <br />
          <div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
        </div>
    </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'contents', JText::_('COM_CWHIRE_FORM_LBL_JOBLISTING_CONTENT')); ?>

      <div class="row-fluid form-vertical">
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
          <div class="controls"><?php echo $this->form->getInput('title'); ?></div>
        </div>
       <!--- <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('shortdesc'); ?></div>
          <div class="controls"><?php echo $this->form->getInput('shortdesc'); ?> <input type="button" id="importdesc" value="<?php echo JText::_('COM_CWHIRE_FORM_LBL_JOBLISTING_SHORTDESC_BUTTON') ?>" description="<?php echo JText::_('COM_CWHIRE_FORM_LBL_JOBLISTING_DESC_BUTTON') ?>"></div>
        </div>   --->
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
          <div class="controls"><?php echo $this->form->getInput('image'); ?></div>
        </div>
        <hr />
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
          <br />
          <div class="controls"><?php echo $this->form->getInput('description'); ?></div>
        </div>
        <hr />
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('tasks'); ?></div>
          <br />
          <div class="controls"><?php echo $this->form->getInput('tasks'); ?></div>
        </div>
        <hr />
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('profile'); ?></div>
          <br />
          <div class="controls"><?php echo $this->form->getInput('profile'); ?></div>
        </div>
        <hr />
        <div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('perspective'); ?></div>
          <br />
          <div class="controls"><?php echo $this->form->getInput('perspective'); ?></div>
        </div>
        <hr />
	<!---	<div class="control-group">
          <div class="control-label"><?php echo $this->form->getLabel('contactinfo'); ?></div>
          <br />
          <div class="controls"><?php echo $this->form->getInput('contactinfo'); ?></div>
        </div>  ---->
    </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>
      <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
