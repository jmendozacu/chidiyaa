<?php

class TM_Cache_Block_Adminhtml_Log_Grid extends TM_Cache_Block_Adminhtml_Log_Grid_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('tmcacheLogGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _filterByCrawler($collection)
    {
        $collection->addFieldToFilter('crawler_id', 0);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('tmcache/log')->getCollection();
        $this->_filterByCrawler($collection);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'index'  => 'entity_id',
            'width' => 50,
            'type'  => 'number'
        ));

        $this->addFullActionNameColumn();
        $this->addRequestColumn();
        $this->addAdditionalParametersColumn();
        $this->addStoreColumn();
        $this->addCustomerGroupColumn();

        $this->addColumn('created_at', array(
            'header' => Mage::helper('reports')->__('Created At'),
            'index'  => 'created_at',
            'type'   => 'datetime'
        ));

        $this->addColumn('result', array(
            'header'  => Mage::helper('tmcache')->__('Result'),
            'index'   => 'is_hit',
            'type'    => 'options',
            'width'   => 60,
            'options' => array(
                0 => Mage::helper('tmcache')->__('Miss'),
                1 => Mage::helper('tmcache')->__('Hit')
            )
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
