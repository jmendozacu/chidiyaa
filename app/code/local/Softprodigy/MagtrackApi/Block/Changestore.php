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
 * Changestore Block
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagtrackApi
 * @author      Softprodigy Developer
 */
class Softprodigy_MagtrackApi_Block_Changestore extends Mage_Core_Block_Template {
    
    
    public function getStoreList(){
        $stores = Mage::app()->getStores(true);
        return $stores;
    }
    
    /**
     * get current store id
     * return int store_id
     */
    public function currentStore(){
        $storeId  =  0;
        if(Mage::getSingleton('adminhtml/session')->getMobileStoreId() != ''){
            $storeId = Mage::getSingleton('adminhtml/session')->getMobileStoreId();
        }
        return $storeId;
    }
}
