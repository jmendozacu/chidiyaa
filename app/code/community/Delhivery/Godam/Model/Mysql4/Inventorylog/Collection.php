<?php
/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Godam
 * @copyright  Copyright (c) 2014 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 * @purpose    Define Mysql collection for waybills 
 */
class Delhivery_Godam_Model_Mysql4_Inventorylog_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

     /**
	 * construct mysql collection object for pincode
	 */
    public function _construct() {
        parent::_construct();
        $this->_init('godam/inventorylog');
    }
}