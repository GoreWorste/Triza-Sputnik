<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class baformsViewForm extends JViewLegacy
{
    public $email;
    public $sheets;
    public $maps_key;
    public $items;
    public $about;

    public function display ($tpl = null)
    {
        $this->about = baformsHelper::aboutUs();
        $form = $this->get('Form');
        $item = $this->get('Item');
        $this->maps_key = $this->get('MapsKey');
        $this->sheets = $this->get('Sheets');
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));
            return false;
        }
        $this->form = $form;
        $this->item = $item;
        $this->items = $this->get('Baitems');
        $this->addToolBar();
        $doc = JFactory::getDocument();
        $doc->addScript(JUri::root(true) . '/media/jui/js/jquery.min.js');
        $doc->addStyleSheet(JUri::root(true) . '/media/jui/css/jquery.minicolors.css');
        $src = 'https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css';
        $doc->addStyleSheet($src);
        $doc->addScriptDeclaration('var sheets = '.json_encode($this->sheets->tables));
        $app = JFactory::getApplication();
        $input = $app->input;
        if ($input->get('layout') == 'email') {
            $this->email = $this->get('emailInfo');
        }

        parent::display($tpl);
    }

    protected function addToolBar()
    {
        $input = JFactory::getApplication()->input;
        $input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
        JToolBarHelper::title($isNew ? JText::_('FORM_NEW') : JText::_('FORM_EDIT'), 'star');
        JToolBarHelper::apply('form.apply', 'JTOOLBAR_APPLY');
        JToolBarHelper::save('form.save');
        JToolBarHelper::cancel('form.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}