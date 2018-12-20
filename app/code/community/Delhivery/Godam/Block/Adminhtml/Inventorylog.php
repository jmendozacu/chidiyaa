<?php
/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Godam
 * @copyright  Copyright (c) 2010-2011 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 * @purpose    Mail Block file for waybills section 
 */
class Delhivery_Godam_Block_Adminhtml_Inventorylog extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_inventorylog';
        $this->_blockGroup = 'godam';
        $this->_headerText = Mage::helper('godam')->__('Delhivery Godam');        
        parent::__construct();
		$this->_removeButton('add');
    }
}
     