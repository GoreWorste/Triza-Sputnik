<?php

/**
 * @package         Convert Forms
 * @version         4.4.10 Free
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Factory;

class JFormFieldTextList extends TextField
{
    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of options.
     */
    protected function getInput()
    {
        $html = '<div data-id="' . $this->id . '" class="input_item_list "><div class="input_items">';

        if (!empty($this->value))
        {
            $values = array_filter((array) $this->value);
    
            foreach ($values as $value)
            {
                $html .= '<div>' . $this->getInputHTML($value) . '</div>';
            }
        }

        $html .= '</div>';
        $html .= '<div id="' . $this->id . '_tmpl" class="input_item_list_tmpl"> ' . $this->getInputHTML() . '</div>';
        $html .= '<button class="btn btn-success add_item"><span class="icon-plus"></span></button></div>';

        static $run;

        if (!$run)
        {
            $this->addMedia();
            $run = true;
        }

        return $html;
    }

    private function getInputHTML($value = '')
    {
        return '
            <input type="text" name="' . $this->name . '[]" id="' . $this->id . '" value="' . $value . '" class="form-control input-xlarge" placeholder="' . $this->hint . '">
            <button class="btn btn-sm btn-danger remove_item"><span class="icon-minus"></button>
        ';
    }

    private function addMedia()
    {
        // Add CSS
        Factory::getDocument()->addStyleDeclaration('
            .input_item_list_tmpl {
                display:none;
            }
            .input_item_list div div {
                margin-bottom:5px;
                display:flex;
                display:-webkit-flex;
                align-items:center;
                -webkit-align-items:center;
            }
            .input_item_list input {
                margin-right:5px;
            }
            .input_item_list *[class^="icon"] {
                margin:0;
                pointer-events: none;
            }
        ');

        // Add Script
        Factory::getDocument()->addScriptDeclaration('
            document.addEventListener("DOMContentLoaded", function(e) {
                var els = document.querySelectorAll(".input_item_list");

                els.forEach(function(el) {

                    var el_tmpl = el.querySelector(".input_item_list_tmpl");
                    el_tmpl.querySelector("input").removeAttribute("value");
                    document.body.appendChild(el_tmpl);

                    el.addEventListener("click", function(e) {
                        e.preventDefault();
                        
                        // Remove item action
                        if (e.target.classList.contains("remove_item")) {
                            var button = e.target;
                            var container = button.closest(".input_items");

                            container.removeChild(button.parentNode);
                        }

                        // Add new item action
                        if (e.target.classList.contains("add_item")) {
                            
                            var el_tmpl = document.getElementById(el.dataset.id + "_tmpl");

                            var cln = el_tmpl.cloneNode(true);

                            cln.removeAttribute("id");

                            cln.classList.remove("input_item_list_tmpl");
                            
                            el.querySelector(".input_items").appendChild(cln);
                        }
                    });
                });
            });
        ');
    }
}