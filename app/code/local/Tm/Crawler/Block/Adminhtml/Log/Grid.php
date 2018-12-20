<?php

class TM_Crawler_Block_Adminhtml_Log_Grid extends TM_Cache_Block_Adminhtml_Log_Grid
{
    protected function _filterByCrawler($collection)
    {
        $collection->addFieldToFilter('crawler_id', array('gt' => 0));
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->removeColumn('customer_group_id');

        $crawlers = Mage::getResourceModel('tmcrawler/crawler_collection')
            ->load()
            ->toOptionHash();
        $this->addColumn('crawler_id', array(
            'header'  => Mage::helper('tmcrawler')->__('Crawler'),
            'width'   => '120',
            'index'   => 'crawler_id',
            'type'    => 'options',
            'options' =>$crawlers
        ));
        return $this;
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
    }
}
