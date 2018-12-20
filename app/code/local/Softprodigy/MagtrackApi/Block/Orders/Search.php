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
 * Orders Search Block
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagtrackApi
 * @author      Softprodigy Developer
 */
class Softprodigy_MagtrackApi_Block_Orders_Search extends Softprodigy_MagtrackApi_Block_Orders {
    
    protected $_sql = ''; //sql
    
    /**
     * get number of orders
     */
    public function numOrder(){
        $collection = Mage::registry('orders_search');
        return $collection->getSize();
    }
    
    public function getBestProductName(){
        $pro_id = Mage::app()->getRequest()->getPost('pro_id');
        if(Mage::getSingleton('adminhtml/session')->getCurrentTab()=='bestsellers' || $pro_id){
            return Mage::getSingleton('adminhtml/session')->getBestProductName();
        }else{
            return '';
        }
    }
}
