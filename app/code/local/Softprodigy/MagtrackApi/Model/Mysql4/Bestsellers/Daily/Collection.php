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
 * @package     Softprodigy_MagTrack
 */

/**
 * softprodigy_magtrackapi Resource Collection Model
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagTrack
 * @author      Softprodigy Developer
 */
class Softprodigy_MagtrackApi_Model_Mysql4_Bestsellers_Daily_Collection extends 
Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('softprodigy_magtrackapi/bestsellers_daily');
    }
    
    /**
     * Set collection page start and records to show
     *
     * @param integer $pageNum
     * @param integer $pageSize
     * @return Mage_Core_Model_Mysql4_Collection_Abstract
     */
    public function setPage($pageNum, $pageSize)
    {
        $this->setCurPage($pageNum)
            ->setPageSize($pageSize);
        return $this;
    }
}
