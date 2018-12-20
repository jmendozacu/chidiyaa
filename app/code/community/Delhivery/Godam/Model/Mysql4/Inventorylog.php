<?php
/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Godam
 * @copyright  Copyright (c) 2014 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 * @purpose    Define Mysql resource for Waybills
 */
class Delhivery_Godam_Model_Mysql4_Inventorylog extends Mage_Core_Model_Mysql4_Abstract
{
     /**
	 * construct mysql resource model for waybills table and set primary key
	 */	
    public function _construct()
    {    
        // Note that the godam_id refers to the key field in your database table.
        $this->_init('godam/inventorylog', 'entity_id');
    }
}