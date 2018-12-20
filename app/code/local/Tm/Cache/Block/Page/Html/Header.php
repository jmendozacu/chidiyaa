<?php

class TM_Cache_Block_Page_Html_Header extends TM_Cache_Block_Page_Html_HeaderAbstract
{
    public function getWelcome()
    {
        $helper = Mage::helper('tmcache');
        if (Mage::registry('tmcache_render')
            || false === $this->getUsePlaceholder()
            || !$helper->canUseCache($this->getRequest())) {

            return parent::getWelcome();
        }

        return '{{tm_cache block type="page/html_header" name="header" method="getWelcome"}}';
    }

    public function getAdditionalHtml()
    {
        $helper = Mage::helper('tmcache');
        if (Mage::registry('tmcache_render')
            || false === $this->getUsePlaceholder()
            || !$helper->canUseCache($this->getRequest())) {

            return parent::getAdditionalHtml();
        }

        return '{{tm_cache block type="page/html_header" name="header" method="getAdditionalHtml"}}';
    }
}
