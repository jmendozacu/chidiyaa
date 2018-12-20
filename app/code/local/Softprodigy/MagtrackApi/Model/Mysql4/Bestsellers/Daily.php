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
class Softprodigy_MagtrackApi_Model_Mysql4_Bestsellers_Daily extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_mywrite = false;
    
    public function _construct()
    {
        $this->_init('softprodigy_magtrackapi/bestsellers_daily', 'id');
    }
    
    /**
     * clear all entries when param is null
     * @param array $in_date date Etc/UTC
     */
    public function clear($in_date = array()){
        if(!$this->_mywrite){
            $this->_mywrite = $this->_getWriteAdapter();
            $this->_mywrite->beginTransaction();
        }
        if(empty($in_date)){
            //delete all
            $this->_mywrite->delete($this->getTable('softprodigy_magtrackapi/bestsellers_daily'));
            $this->_mywrite->commit();
        }else{
            //delete in
            if(is_array($in_date)){
                $where = 'DATE(created_at) in ("'.implode('", "', $in_date).'")';
                $this->_mywrite->delete($this->getTable('softprodigy_magtrackapi/bestsellers_daily'), $where);
                $this->_mywrite->commit();
            }
        }
        return $this;
    }
    
    public function insertDataFromSelect($select, $needClear = false)
    {
    	$writer = $this->_getWriteAdapter();
    	if ($needClear) {
    		// Clear table data
    		$writer->truncate($this->getTable('softprodigy_magtrackapi/bestsellers_daily'));
    	}
    	// Update table
    	$updateSql = $select->insertFromSelect(
    	    $this->getTable('softprodigy_magtrackapi/bestsellers_daily'),
    	    array('created_at', 'updated_at', 'status', 'store_id', 'id', 'product_id', 'product_name', 'sku', 'qty', 'sales'),
    	    true
    	);
    	$writer->query($updateSql);
    	return $this;
    }

    /**
     * insert datas
     * @param mixed $datas multi rows to insert
     * @param array $in_date date to clear
     * @return Softprodigy_softprodigy_magtrackapi_Model_Mysql4_Bestsellers_Daily
     * @throws Exception
     */
    public function write($datas, $in_date = array()){
        if(!$this->_mywrite){
            $this->_mywrite = $this->_getWriteAdapter();
            $this->_mywrite->beginTransaction();
        }
        $this->clear($in_date); //clear data
        try {
            $rows = array();
            foreach ($datas as $data) {
                $rows[] = $data;
                if (count($rows) == 1000) {
                    $this->_mywrite->insertMultiple($this->getTable('softprodigy_magtrackapi/bestsellers_daily'), $rows);
                    $rows = array();
                }
            }
            if (!empty($rows)) {
                $this->_mywrite->insertMultiple($this->getTable('softprodigy_magtrackapi/bestsellers_daily'), $rows);
            }
            $this->_mywrite->commit();
        } catch (Exception $e) {
            $this->_mywrite->rollback();
            throw $e;
        }
        return $this;
    }
}
