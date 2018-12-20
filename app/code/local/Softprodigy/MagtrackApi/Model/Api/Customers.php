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
class Softprodigy_MagtrackApi_Model_Api_Customers extends Softprodigy_MagtrackApi_Model_Api_Abstract
{
    protected $_newCustomers = array(); //array('id'=>'1');
    protected $_collection = '';
     
     public function apiIndex($params){
		 $settings = Mage::getModel('softprodigy_magtrackapi/settings');
		 if(isset($params['last_time_stamp']) && isset($params['current_time_stamp'])) {
			$params['last_time_stamp'] = $settings->getSetting('last_timestamp_customers');
			$params['current_time_stamp'] = date('Y-m-d H:i:s');
			$fromDate = gmdate("Y-m-d H:i:s", strtotime($params['last_time_stamp'])); 
			$toDate = gmdate("Y-m-d H:i:s", strtotime($params['current_time_stamp'])); 
			if(isset($params['customer_id']) && $params['customer_id'] != "") {
				$customers = Mage::getModel('customer/customer')->getCollection()
					->addNameToSelect()
					->addAttributeToFilter('updated_at', array('from'=>$fromDate, 'to'=>$toDate))
					->addAttributeToFilter('entity_id', $params['customer_id']);
			} else {
				$customers = Mage::getModel('customer/customer')->getCollection()
					->addNameToSelect()
					->addAttributeToFilter('updated_at', array('from'=>$fromDate, 'to'=>$toDate));
				$settings->saveSetting(date('Y-m-d H:i:s'), 'last_timestamp_customers');
			}
		 } else {
			 if(isset($params['customer_id']) && $params['customer_id'] != "") {
				$customers = Mage::getModel('customer/customer')->getCollection()
							->addNameToSelect()
							->addAttributeToFilter('entity_id', $params['customer_id']);
			 } else {
				$customers = Mage::getModel('customer/customer')->getCollection()->addNameToSelect();
				$settings->saveSetting(date('Y-m-d H:i:s'), 'last_timestamp_customers');
			 } 
		 }
		 
		 if(isset($params['search']) && $params['search'] == 1) {
			if(isset($params['id']) && $params['id'] != "") {
				$customers->addAttributeToFilter('entity_id',$params['id']);
			} elseif(isset($params['customer_name']) && $params['customer_name'] != "") {
				$customers->addAttributeToFilter('name',array('like' => $params['customer_name'].'%'));
			}
		 }
		 $data = array();
		 foreach ($customers as $customer) {
			 if(isset($params['customer_id']) && $params['customer_id'] != "") {
				 $data['customer_orders'][] = $this->apiView(array('id'=>$customer->getId(), 'customer_id' => $customer->getId()));
			 } else {
				$data['customers'][] = $this->apiView(array('id'=>$customer->getId()));
			}
		 }
		 
		 return $data;
		 
	 }
    
    
    
    public function apiView($params){
        if(!isset($params['id']) || $params['id'] == ''){
            throw new Exception($this->_helper->__('Not defined param name "id" or value is null'), 21);
        }
        $customer = Mage::getModel('customer/customer')->load($params['id']);
        if($customer->getId() == ''){
            throw new Exception($this->_helper->__('No customer'), 22);
        }
        
        //get next and preview customer ids
        //get next
        $collection = Mage::getModel('customer/customer')->getCollection();
        $next_ids = $pre_ids = array('','');
        if(isset($params['filter'])){
            if($params['filter'] != ''){
                $collection = Mage::getModel('softprodigy_magtrackapi/customers')->getCustomerFilter($params['filter']);
            }
        }
        $collection->getSelect()
            ->where('e.entity_id < ?', $params['id'])
            ->limit(2)
            ->order('e.created_at DESC');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns('entity_id');
        $temp = $collection->getColumnValues('entity_id');
        $next_ids[0] = isset($temp[0])?$temp[0]:'';
        $next_ids[1] = isset($temp[1])?$temp[1]:'';
        //get preview
        $collection = Mage::getModel('customer/customer')->getCollection();
        if(isset($params['filter'])){
            if($params['filter'] != ''){
                $collection = Mage::getModel('softprodigy_magtrackapi/customers')->getCustomerFilter($params['filter']);
            }
        }
        $collection = Mage::getModel('softprodigy_magtrackapi/customers')->getCustomerFilter($params['filter']);
        $collection->getSelect()
            ->where('e.entity_id > ?', $params['id'])
            ->limit(2)
            ->order('e.created_at ASC');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns('entity_id');
        $temp = $collection->getColumnValues('entity_id');
        $pre_ids[0] = isset($temp[0])?$temp[0]:'';
        $pre_ids[1] = isset($temp[1])?$temp[1]:'';
        $orders = Mage::getModel('softprodigy_magtrackapi/customers')->getOrders($params['id']); //get orders list
        
        if(($builling_address = $customer->getDefaultBillingAddress())){
            $b_address = (string)$builling_address->getStreetFull()."\n".(string)$builling_address->getCity().", ".(string)$builling_address->getRegion()."\n".(string)$builling_address->getPostcode()."\n".(string)$builling_address->getCountryModel()->getName();
        }
        
        if(($shipping_address = $customer->getDefaultShippingAddress())){
			$s_address = (string)$shipping_address->getStreetFull()."\n".(string)$shipping_address->getCity().", ".(string)$shipping_address->getRegion()."\n".(string)$shipping_address->getPostcode()."\n".(string)$shipping_address->getCountryModel()->getName();
        }
        $order_item = array();
        foreach($orders as $order){
            $order_item[] = array(
                'date'      =>  Mage::helper('core')->formatDate($order['updated_at'], 'medium', true),
                'increment_id' =>  $order['increment_id'],
                'status'    =>  $order['status'],
                'value'     =>  Mage::helper('core')->currency($order['grand_total'], true, false)
            );
        }
        $customer_model = Mage::getModel('softprodigy_magtrackapi/customers');
        $order_sales = array(
            'lifetime_sales' => Mage::helper('core')->currency($customer_model->getLifetimeSales($customer->getId()), true, false),
            'orders'    =>  $order_item,
            'total'     =>  Mage::helper('core')->currency($this->getTotalValue($orders), true, false)
        );
        if(isset($params['customer_id']) && $params['customer_id'] != "") {
			$data = $order_sales;
		} else {
			if(($shipping_address = $customer->getDefaultShippingAddress())){
				$data = array(
					'id'            =>  (int)$customer->getId(),
					'customer_name' =>  $customer->getName(),
					'customer_email'=>  $customer->getEmail(),
					'created_date'    =>  date('Y-m-d', strtotime($customer->getCreatedAt())),
					'created_time'    =>  date('H:i:s', strtotime($customer->getCreatedAt())),
					'builling_adress'   => $b_address,
					'shipping_adress'   =>  $s_address,
					'telephone'			=>	(string)$shipping_address->getTelephone(),
					'country'			=>	(string)$shipping_address->getCountryModel()->getName(),
					'total_orders'		=>	count($orders)
				);
			} else {
				$data = array(
					'id'            =>  (int)$customer->getId(),
					'customer_name' =>  $customer->getName(),
					'customer_email'=>  $customer->getEmail(),
					'created_date'    =>  date('Y-m-d', strtotime($customer->getCreatedAt())),
					'created_time'    =>  date('H:i:s', strtotime($customer->getCreatedAt())),
					'builling_adress'   => $b_address,
					'shipping_adress'   =>  $s_address,
					'telephone'			=>	'',
					'country'			=>	'',
					'total_orders'		=>	count($orders)
				);
			}
		}
        return $data;
   }
    
    
    /**
     * total value of order list
     */
    public function getTotalValue($orders){
        $total = 0;
        if($orders){
            foreach ($orders as $order){
                $total += $order['grand_total'];
            }
        }
        return $total;
    }
}
