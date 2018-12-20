<?php

class TM_Crawler_Model_Resource_Report_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('tmcrawler/report');
        $this->setItemObjectClass('Varien_Object');
    }
}
