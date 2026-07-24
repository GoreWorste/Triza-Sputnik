<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');


class BaformsControllerForm extends JControllerForm
{
    public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

    public function payupl()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->payupl();
        }
    }

    public function barion()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->barion();
        }
    }

    public function checkCaptcha()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        $model = $this->getModel('form');
        if ($model->checkCaptchaAnswer($id)) {
            $str = 'enabled';
        } else {
            $str = '';
        }
        echo $str;
        exit;
    }

    public function getToken()
    {
        $model = $this->getModel('form');
        $model->setToken();
        exit;
    }    

    public function saveContinue()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->saveContinue();
        }
        exit;
    }

    public function yandex()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->yandex();
        }
    }

    public function ccavenue()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->ccavenue();
        }
    }

    public function mollie()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->mollie();
        }
    }

    public function stripe()
    {
        $data = $_POST;
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->save($data);
        }
    }

    public function stripeCharges()
    {
        $model = $this->getModel('form');
        $model->stripeCharges();
    }
    
    public function payu()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->payu();
        }
    }

    public function payubiz()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->payubiz();
        }
    }

    public function webmoney()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->webmoney();
        }
    }
    
    public function skrill()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->skrill();
        }
    }
    
    public function twoCheckout()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->twoCheckout();
        }
    }
    
    public function paypal()
    {
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->paypal();
        }
    }
    
    public function save($key = NULL, $urlVar = NULL)
    {
        $data = $_POST;
        $app = JFactory::getApplication();
        $id = $app->input->get('form_id', 0, 'int');
        if (!empty($id)) {
            $model = $this->getModel('form');
            $model->save($data);
        }        
    }
}