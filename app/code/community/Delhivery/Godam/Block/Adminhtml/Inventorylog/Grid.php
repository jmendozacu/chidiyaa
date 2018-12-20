<?php
/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Godam
 * @copyright  Copyright (c) 2010-2011 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 * @purpose    Waybills Column Grid  
 */
class Delhivery_Godam_Block_Adminhtml_Inventorylog_Grid extends Delhivery_Godam_Block_Adminhtml_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('inventorylogGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);		
    }
	public function getGridUrl()
	{
	return $this->getUrl('*/*/grid', array('_current'=>true));
	}
	protected function _prepareCollection() {
		mage::log("Delhivery_Godam_Block_Adminhtml_Godam_Grid::_prepareCollection called");
		$tableName = Mage::getSingleton("core/resource")->getTableName('sales_flat_order');
		$collection = Mage::getModel('godam/inventorylog')->getCollection();
		//addFieldToFilter('main_table.status',1);	
		//$collection->getSelect()->join($this->getTable('sales/order'), "faqcat.orderid =main_table.orderincid", array('faqcat.status'));
		//$collection->getSelect()->join( array('cgroup'=>$tableName), 'main_table.orderincid = cgroup.increment_id', array('cgroup.status'));	
		//echo $collection->getSelect();die;
		$this->setCollection($collection);
		//$this->setDefaultFilter(array('main_table.godamstate'=>1));
		return parent::_prepareCollection();
	}

    protected function _prepareColumns() {
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('godam')->__('ID'),
            'align' => 'center',
            'width' => '30px',
            'index' => 'entity_id',
        ));
        $this->addColumn('sku', array(
            'header' => Mage::helper('godam')->__('Sku'),
            'index' => 'sku',
        ));
        $this->addColumn('qty', array(
            'header' => Mage::helper('godam')->__('Qty'),
            'index' => 'qty',
        ));		
        $this->addColumn('created_time', array(
            'header' => Mage::helper('godam')->__('Created Time'),
            'index' => 'created_time',
        ));	
        $this->addColumn('update_time', array(
            'header' => Mage::helper('godam')->__('Update Time'),
            'index' => 'update_time',
        ));				
		
		
        //$this->addExportType('*/*/exportCsv', Mage::helper('godam')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('godam')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');


        return $this;
    }

   

}