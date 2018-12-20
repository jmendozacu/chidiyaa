<?php

if (Mage::helper('core')->isModuleOutputEnabled('TM_CDN')) {
    Mage::helper('tmcore')->requireOnce('TM/Cache/Block/Page/Html/Header/TMCDN.php');
} else {
    class TM_Cache_Block_Page_Html_HeaderAbstract extends Mage_Page_Block_Html_Header
    {

    }
}