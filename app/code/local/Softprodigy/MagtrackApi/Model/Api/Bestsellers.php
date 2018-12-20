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
 * SimiSalestracking Api Dashboard Server Model
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagtrackApi
 * @author      Softprodigy Developer
 */
class Softprodigy_MagtrackApi_Model_Api_Bestsellers extends Softprodigy_MagtrackApi_Model_Api_Abstract
{

    public function apiIndex($params){
		if(isset($params['store_id']) && $params['store_id'] != "") {
			$collection = Mage::getResourceModel('sales/report_bestsellers_collection')
					->setModel('catalog/product')
					->addStoreFilter($params['store_id']);
			$data = array();
			$i=0;
			foreach($collection as $bestseller) {
				$data['data'][$i]['product_name'] = $bestseller->getProductName();
				$data['data'][$i]['price'] = Mage::helper('core')->currency($bestseller->getProductPrice(), true, false);
				$data['data'][$i]['ordered_qty'] = (int)$bestseller->getQtyOrdered();
				$i++;
			}
			return $data;
		}
    }
}
