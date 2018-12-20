<?php

class TM_Crawler_Block_Adminhtml_Report_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('tmcrawlerReportGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _filterByCrawler($collection)
    {
        $collection->addFieldToFilter('crawler_id', array('gt' => 0));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('tmcrawler/report_collection');
        $this->_filterByCrawler($collection);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'index'  => 'entity_id',
            'width' => '50px',
            'type'  => 'number'
        ));

        $this->addColumn('url', array(
            'header' => Mage::helper('tmcache')->__('Url'),
            'index'  => 'url'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('cms')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => false,
                'store_view'    => true,
                'sortable'      => false
            ));
        }

        $crawlers = Mage::getResourceModel('tmcrawler/crawler_collection')
            ->load()
            ->toOptionHash();
        $this->addColumn('crawler_id', array(
            'header'  => Mage::helper('tmcrawler')->__('Crawler'),
            'index'   => 'crawler_id',
            'type'    => 'options',
            'options' => $crawlers
        ));

        $this->addColumn('http_code', array(
            'header' => Mage::helper('tmcrawler')->__('Http Code'),
            'index'  => 'http_code',
            'type'   => 'number'
        ));

        $this->addColumn('total_time', array(
            'header' => Mage::helper('tmcrawler')->__('Response Time'),
            'index'  => 'total_time',
            'type'   => 'number'
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('reports')->__('Created At'),
            'index'  => 'created_at',
            'type'   => 'datetime'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
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
