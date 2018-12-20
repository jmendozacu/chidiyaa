<?php

class TM_Crawler_Block_Adminhtml_Crawler_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('crawler_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('tmcrawler')->__('Crawler Information'));
    }
}
