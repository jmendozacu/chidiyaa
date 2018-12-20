<?php

class TM_Cache_Block_Adminhtml_System_Config_Form_Field_Lifetime extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('url', array(
            'label' => Mage::helper('tmcache')->__('module/controller/action'),
            'style' => 'width:160px',
        ));
        $this->addColumn('lifetime', array(
            'label' => Mage::helper('tmcache')->__('Lifetime, sec'),
            'style' => 'width:80px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add');
        parent::__construct();
    }
}
