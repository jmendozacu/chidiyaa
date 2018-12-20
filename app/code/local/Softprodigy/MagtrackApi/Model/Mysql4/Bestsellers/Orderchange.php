<?php
/**
 * Softprodigy
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Softprodigy.com license that is
 * available through the world-wide-web at this URL:
 * http://www.Softprodigy.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagtrackApi
 */

/**
 * MagtrackApi Bestsellers Mysql4 Model
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagtrackApi
 * @author      Softprodigy Developer
 */
class Softprodigy_MagtrackApi_Model_Mysql4_Bestsellers_Orderchange extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('softprodigy_magtrackapi/bestsellers_orderchange', 'id');
    }
    
    /**
     * clear all entries
     */
    public function clear(){
        $write = $this->_getWriteAdapter();
        $write->truncate($this->getTable('softprodigy_magtrackapi/bestsellers_orderchange'));
        return $this;
        $write->beginTransaction();
        //delete all
        $write->delete($this->getTable('softprodigy_magtrackapi/bestsellers_orderchange'));
        $write->commit();
    }
    
    
    /**
     * get order ids
     * @return 
     */
    public function getOrderIds(){
        $db = $this->_getReadAdapter();
        $sql = "SELECT *  
                FROM ".$this->getTable('softprodigy_magtrackapi/bestsellers_orderchange');
        $order_ids = $db->fetchPairs($sql); //array
        return $order_ids;
    }
}
