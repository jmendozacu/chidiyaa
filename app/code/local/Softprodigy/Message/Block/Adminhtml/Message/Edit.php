<?php

class Softprodigy_Message_Block_Adminhtml_Message_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'message';
        $this->_controller = 'adminhtml_message';
        
        $this->_updateButton('save', 'label', Mage::helper('message')->__("Send SMS"));
        $this->_removeButton('back');

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('web_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'web_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'web_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
       return Mage::helper('message')->__("Send SMS To Any Number ");
    }
}
