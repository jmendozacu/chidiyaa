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
 * Bestsellers Block
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagtrackApi
 * @author      Softprodigy Developer
 */
class Softprodigy_MagtrackApi_Block_Bestsellers extends Mage_Core_Block_Template {
    
    /**
     * get time to show how many last days
     */
    public function getTitleTime(){
        return Mage::helper('softprodigy_magtrackapi')->getBestsellersTitleTime();
    }
    
    /**
     * get time of Bestsellers updated
     * return string datetime db
     */
    public function getUpdatedTime(){
        $time = Mage::getSingleton('adminhtml/session')->getSalestrackingTimeRefreshBestsellers();
        if($time == ''){
            $time = Mage::getModel('softprodigy_magtrackapi/settings')->getSetting('time_refresh_bestsellers');
        }
        if($time == ''){
            return '';
        }
        return Mage::helper('core')->formatDate($time, 'medium', true);
    }
    
    /**
     * check bestseller is old
     */
    public function isOld(){
        $order_ids = Mage::getResourceModel('softprodigy_magtrackapi/bestsellers_orderchange')->getOrderIds();
        if(!empty($order_ids)){
            return true;
        }else{
            return false;
        }
    }
    
}
