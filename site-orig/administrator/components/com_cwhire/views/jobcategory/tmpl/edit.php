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
$document->addStyleSheet('components/com_cwhire/assets/css/jobokay.css');
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function(){

    });

    Joomla.submitbutton = function(task)
    {
        if(task == 'jobcategory.cancel'){
            Joomla.submitform(task, document.getElementById('jobcategory-form'));
        }
        else{

            if (task != 'jobcategory.cancel' && document.formvalidator.isValid(document.id('jobcategory-form'))) {
                Joomla.submitform(task, document.getElementById('jobcategory-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_cwhire&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="jobcategory-form" class="form-validate">
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <fieldset class="adminform">

                			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
			</div>
      <div class="control-group">
        <div class="control-label"><?php echo $this->form->getLabel('recipient'); ?></div>
        <div class="controls"><?php echo $this->form->getInput('recipient'); ?></div>
      </div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
			</div>
      <div class="control-group">
        <div class="control-label"><?php echo $this->form->getLabel('language'); ?></div>
        <div class="controls"><?php echo $this->form->getInput('language'); ?></div>
      </div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
			</div>


            </fieldset>
        </div>



        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>

    </div>
</form>
