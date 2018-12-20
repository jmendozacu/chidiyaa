<?php
class Softprodigy_Message_Block_Adminhtml_Grid1 extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
     $this->_controller = 'adminhtml_email';
     $this->_blockGroup = 'message';
     $this->_headerText = 'Customers';
     $this->_addButtonLabel = 'Add a contact';
     parent::__construct();
     }
     protected function _prepareLayout()
	{
		$this->setChild( 'grid',
			$this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid',
			$this->_controller . '.grid')->setSaveParametersInSession(true) );
		return parent::_prepareLayout();
	}
}
