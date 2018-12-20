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
 * SimiSalestracking Api Orders Server Model
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagtrackApi
 * @author      Softprodigy Developer
 */
class Softprodigy_MagtrackApi_Model_Api_Orders extends Softprodigy_MagtrackApi_Model_Api_Abstract
{
    
    public function apiIndex($params){
		$settings = Mage::getModel('softprodigy_magtrackapi/settings');
        if(isset($params['order_id']) && $params['order_id'] != "") {
			$order = Mage::getModel('sales/order')->load($params['order_id']);
			$items = $order->getAllVisibleItems();
			$data = array();
			$i=0;
			foreach($items as $item) {
				$product_data = $item->getData();
				$data[$i]['product_id'] = $product_data['item_id'];
				$data[$i]['order_id'] = $product_data['order_id'];
				$data[$i]['product_name'] = $product_data['name'];
				$data[$i]['qty_ordered'] = $product_data['qty_ordered'];
				$data[$i]['row_total'] = Mage::helper('core')->currency($product_data['base_row_total'], true, false);
				$i++;
			}
			$result = array(
				'order_detail'          => $data
			);
			return $result;
		} else {
			$collection = Mage::getResourceModel('sales/order_collection');
			if(isset($params['store_id']) && $params['store_id'] != "") {
				$collection->addAttributeToFilter('store_id', $params['store_id']);
			}
			if(isset($params['last_timestamp']) && $params['last_timestamp'] != "") {
				$params['last_timestamp'] = $settings->getSetting('last_timestamp_orders');
				$params['current_timestamp'] = date('Y-m-d H:i:s');
				$collection->addAttributeToFilter("updated_at", array(
					'from'=>gmdate("Y-m-d H:i:s", strtotime($params['last_timestamp'])),
					'to'=>gmdate("Y-m-d H:i:s", strtotime($params['current_timestamp']))
					));
			}
			$collection->setOrder('entity_id');
			$collection->getSelect()->limit(500);
			if(isset($params['search']) && $params['search'] == 1) {
				if(isset($params['id']) && $params['id'] != "") {
					$collection->addAttributeToFilter('increment_id',$params['id']);
				} elseif(isset($params['customer_name']) && $params['customer_name'] != "") {
					$collection->addAttributeToFilter('customer_firstname',array('like' => $params['customer_name'].'%'));
				}
			} else {
				$settings->saveSetting(date('Y-m-d H:i:s'), 'last_timestamp_orders');
			}
			$data = $this->convertOrderData($collection, $params['group']);
			$result = array(
				'data'          => $data
			);
			return $result;
		}
    }
    
}
