<?php
/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Godam
 * @copyright  Copyright (c) 2010-2011 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 * @purpose    Waybills Column Grid  
 */
class Delhivery_Godam_Block_Adminhtml_Godam_Grid extends Delhivery_Godam_Block_Adminhtml_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('godamGrid');
        $this->setDefaultSort('godam_id');
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
		$collection = Mage::getModel('godam/godam')->getCollection();
		//addFieldToFilter('main_table.status',1);	
		//$collection->getSelect()->join($this->getTable('sales/order'), "faqcat.orderid =main_table.orderincid", array('faqcat.status'));
		$collection->getSelect()->join( array('cgroup'=>$tableName), 'main_table.orderincid = cgroup.increment_id', array('cgroup.status'));	
		//echo $collection->getSelect();die;
		$this->setCollection($collection);
		$this->setDefaultFilter(array('main_table.godamstate'=>1));
		return parent::_prepareCollection();
	}

    protected function _prepareColumns() {
        $this->addColumn('godam_id', array(
            'header' => Mage::helper('godam')->__('ID'),
            'align' => 'center',
            'width' => '30px',
            'index' => 'godam_id',
        ));
        $this->addColumn('orderincid', array(
            'header' => Mage::helper('godam')->__('Order#'),
            'index' => 'orderincid',
        ));
        $this->addColumn('suborderid', array(
            'header' => Mage::helper('godam')->__('SubOrder#'),
            'index' => 'suborderid',
        ));		
        $this->addColumn('godamstate', array(
            'header' => Mage::helper('godam')->__('State'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'godamstate',
            'type' => 'options',
            'options' => array(
                1 => 'Not Submitted',
                2 => 'Submitted',
            ),
        ));
        $this->addColumn('request_id', array(
            'header' => Mage::helper('godam')->__('Request ID'),
            'index' => 'request_id',
        ));	
        $this->addColumn('godamstatus', array(
            'header' => Mage::helper('godam')->__('Godam Status'),
            'index' => 'godamstatus',
        ));				
        $this->addColumn('awb', array(
            'header' => Mage::helper('godam')->__('AWB#'),
            'index' => 'awb',
        ));
        $this->addColumn('courier', array(
            'header' => Mage::helper('godam')->__('Courier'),
            'index' => 'courier',
        ));
		 $this->addColumn('courier_last_scan_location', array(
            'header' => Mage::helper('godam')->__('Last Scan Location'),
            'index' => 'courier_last_scan_location',
        ));
		 $this->addColumn('courier_lsd', array(
            'header' => Mage::helper('godam')->__('Courier LSD'),
            'index' => 'courier_lsd',
        ));		
        $this->addColumn('courierstatus', array(
            'header' => Mage::helper('godam')->__('Courier Status'),
            'index' => 'courierstatus',
        ));
		
							
		$this->addColumn('status', array(
            'header' => Mage::helper('godam')->__('Order Status'),
          'index' => 'status',
		
			
			
        ));
		
		
        //$this->addExportType('*/*/exportCsv', Mage::helper('godam')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('godam')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('godam_id');
        $this->getMassactionBlock()->setFormFieldName('godam');

        $this->getMassactionBlock()->addItem('manifest', array(
            'label' => Mage::helper('godam')->__('Submit to Godam'),
            'url' => $this->getUrl('*/*/submitGodam')
        ));	
        $this->getMassactionBlock()->addItem('update', array(
            'label' => Mage::helper('godam')->__('Get Update From Godam'),
            'url' => $this->getUrl('*/*/updateFromGodam')
        ));	

        return $this;
    }

   

}