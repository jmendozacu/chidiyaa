<?php

class TM_Crawler_Block_Adminhtml_Crawler_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('tmcrawlerGrid');
        $this->setDefaultSort('crawler_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('tmcrawler/crawler')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
        // echo $collection->getSelect();
        // return $collection;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('crawler_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'index'  => 'crawler_id',
            'width' => '50px',
            'type'  => 'number'
        ));

        $this->addColumn('identifier', array(
            'header' => Mage::helper('cms')->__('Identifier'),
            'index'  => 'identifier'
        ));

        $states = Mage::getModel('tmcrawler/system_config_source_state');
        $this->addColumn('state', array(
            'header' => Mage::helper('tmcrawler')->__('State'),
            'index'  => 'state',
            'type'   => 'options',
            'options' => $states->getOptions(),
            'renderer' => 'tmcrawler/adminhtml_crawler_grid_renderer_state'
        ));

        $this->addColumn('crawled_urls', array(
            'header' => Mage::helper('tmcrawler')->__('Crawled Urls'),
            'index' => 'crawled_urls',
            'width' => '50px',
            'type'  => 'number'
        ));

        $types = Mage::getModel('tmcrawler/system_config_source_type');
        $this->addColumn('type', array(
            'header' => Mage::helper('tmcrawler')->__('Type'),
            'index'  => 'type',
            'type'   => 'options',
            'options'  => $types->getOptions(),
            'renderer' => 'tmcrawler/adminhtml_widget_grid_column_renderer_options',
            'sortable' => false,
            'filter_condition_callback' => array($this, '_filterTypeCondition')
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_ids', array(
                'header'     => Mage::helper('cms')->__('Store View'),
                'index'      => 'store_ids',
                'type'       => 'store',
                'renderer'   => 'tmcrawler/adminhtml_widget_grid_column_renderer_store',
                'store_all'  => false,
                'store_view' => true,
                'sortable'   => false,
                'filter_condition_callback' => array($this, '_filterStoreCondition')
            ));
        }

        $this->addColumn('last_activity_at', array(
            'header' => Mage::helper('tmcrawler')->__('Last Activity'),
            'index'  => 'last_activity_at',
            'type'   => 'datetime'
        ));

        $this->addColumn('status', array(
            'header'  => Mage::helper('cms')->__('Status'),
            'index'   => 'status',
            'type'    => 'options',
            'options' => array(
                0 => Mage::helper('cms')->__('Disabled'),
                1 => Mage::helper('cms')->__('Enabled')
            )
        ));

        return parent::_prepareColumns();
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
    }

    protected function _filterTypeCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addTypeFilter($value);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('crawler_id' => $row->getId()));
    }
}
