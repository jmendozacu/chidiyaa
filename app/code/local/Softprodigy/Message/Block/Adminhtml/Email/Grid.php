<?php
class Softprodigy_Message_Block_Adminhtml_Email_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
   public function __construct()
	{
		//die('jhfgzdhf');
		parent::__construct();
		$this->setId('id');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		//$this->setUseAjax(true);
		
		//var_dump(Mage::getSingelton('rentalorder/customer_edit_tab_sentitems'));
	}
protected function _prepareCollection()
	{
		$resource = Mage::getSingleton('core/resource');
       $collection = Mage::getResourceModel('customer/customer_collection')
					->addNameToSelect()
					->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
					->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
					->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
					->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
					->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');


	   // $collection->removeFieldToSelect('main_table.item_id');
	  // $collection->removeAttributeToSelect('item_id');
	 // print_r($collection);die;
		$this->setCollection($collection);
        return parent::_prepareCollection();
        
    }
    	 protected function _prepareColumns()
	 {
		 $this->addColumn('entity_id', array(
							'header'    => Mage::helper('adminhtml')->__('Id'),
							'index'     => 'entity_id',
							'width'     => '25',
							'align'		=>  'left',
		));
		$this->addColumn('name', array(
							'header'    => Mage::helper('adminhtml')->__('First Name'),
							'index'     => 'name',
							'width'     => '100',
							'align'		=>  'left',
		));
		$this->addColumn('email', array(
							'header'    => Mage::helper('adminhtml')->__('Email'),
							'index'     => 'email',
							'width'     => '100',
							'align'		=>  'left',
		));
		$this->addColumn('billing_telephone', array(
							'header'    => Mage::helper('adminhtml')->__('Telephone Number'),
							'index'     => 'billing_telephone',
							'width'     => '100',
							'align'		=>  'left',
		));
		$link= Mage::helper('adminhtml')->getUrl('*/*/send/') .'id/$entity_id';
		$this->addColumn('action_edit', array(
        'header'   => $this->helper('adminhtml')->__('Action'),
        'width'    => 15,
        'sortable' => false,
        'filter'   => false,
        'type'     => 'action',
        'actions'  => array(
            array(
                'url'     => $link,
                'caption' => $this->helper('adminhtml')->__('Send Email'),
            ),
        )
    ));
		 return parent::_prepareColumns();
	}
	protected function _prepareMassaction()
		{
		$this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('id');
        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('message')->__('send email'),
             'url'  => $this->getUrl('*/*/massDelete' , array('' => '')),
             'confirm' => Mage::helper('message')->__('Are you sure?')
        ));
 
return $this;
}
	
	
}
