<?php
/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Godam
 * @copyright  Copyright (c) 2014 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 * @purpose    Model Class for Waybills  
 */
class Delhivery_Godam_Model_Inventorylog extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('godam/inventorylog');
    }
}