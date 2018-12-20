<?php

class TM_Crawler_Block_Adminhtml_Crawler_Edit_Tab_Log
    extends TM_Cache_Block_Adminhtml_Log_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * @return TM_Crawler_Model_Crawler
     */
    public function getCrawler()
    {
        return Mage::registry('crawler');
    }

    protected function _filterByCrawler($collection)
    {
        $collection->addFieldToFilter('crawler_id', $this->getCrawler()->getId());
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('customer_group_id');
        return $this;
    }

    public function getTabLabel()
    {
        return Mage::helper('catalog')->__('Log');
    }

    public function getTabTitle()
    {
        return Mage::helper('catalog')->__('Log');
    }

    public function canShowTab()
    {
        return (bool)$this->getCrawler()->getId();
    }

    public function isHidden()
    {
        return !$this->canShowTab();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/log', array('_current'=>true));
    }

    public function getTabUrl()
    {
        return $this->getUrl('*/*/log', array('_current' => true));
    }

    public function getTabClass()
    {
        return 'ajax';
    }

    public function getSkipGenerateContent()
    {
        return true;
    }
}
