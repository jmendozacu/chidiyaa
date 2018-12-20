<?php
/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Godam
 * @copyright  Copyright (c) 2014 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 * @purpose    Model Class for Waybills  
 */
class Delhivery_Godam_Model_Godam extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('godam/godam');
    }

    /**
    * Function to Update current waybill status from Delhivery Server
    */		
	public function updateFromGodam($requeststring) {

		mage::log("Delhivery_Godam_Model_Godam::updateFromGodam called");

		$token = Mage::getStoreConfig('godam/godam/api_token');
		$clientstore = urlencode(Mage::getStoreConfig('godam/godam/client_store'));
		//$clientfc =  urlencode(Mage::getStoreConfig('godam/godam/client_fc'));		
		$apiversion =  urlencode(Mage::getStoreConfig('godam/godam/api_version'));
		$url =  Mage::getStoreConfig('godam/godam/api_url');
		//echo $clientstore;die;
		//return "in model update funciton";
		if($clientstore && $token && $url)
		{
			//explode by ";" to get all orders
			//expected format 100000067:77-W2452T-TF,78-logitechcord;100000066:70-W2452T-TF,71-logitechcord
			//mage::log($requeststring);			
			$orders = explode(";",$requeststring);
			mage::log($orders);
			if(sizeof($orders))
			{
				foreach($orders as $order)
				{
					//explode by ":" to get seprate sub order string from order id
					$orderstring = explode(":",$order);
					$orderid = $orderstring[0];
					mage::log("Order ID $orderid");
					mage::log("Suborder ". $orderstring[1]);
					//seprate all sub orders
					$suborderids = explode(",",$orderstring[1]);
					
					//get all sub order status update
					if(sizeof($suborderids))
					{
						foreach($suborderids as $suborder) 
						{
							mage::log("SubOrder ID $suborder");
							$model = Mage::getModel('godam/godam')->loadByOrderSuborderId($orderid, $suborder);		
							if($model->godamstate == 2): // check only if state is submitted
							$url =  Mage::getStoreConfig('godam/godam/api_url');
							$url .= "oms/api/detail/$model->orderincid/$suborder/?client_store=".$clientstore."&version=".$apiversion;
							$result = Mage::helper('godam')->Executecurl($url,'','');
							mage::log($url);
							$result = json_decode($result);
							mage::log('----------Results Starts----------');
							mage::log($result);
							mage::log('-----------Results Ends-----------');
							//die();
							if($result->OrderLine->status):
								$model->setData('godamstatus',$result->OrderLine->status);
								$model->setData('awb',$result->OrderLine->waybill);
								$model->setData('courier',$result->OrderLine->courier);
								$model->setData('courierstatus',$result->OrderLine->courier_status);
								$model->setData('courier_last_scan_location',$result->OrderLine->courier_last_scan_location);
								$model->setData('courier_lsd',$result->OrderLine->courier_lsd);
								$model->save();
								$msg = "$order Updated Successfully<br />";
								$reqstatus = "Success";
							else:
								$msg = "No update found for $order";
								$reqstatus = "Success";
							endif;
							else:
								$msg = "$order order# does not exist<br />";
								$reqstatus = "Failed";
							endif;
						}
					}
				}
			}
		}
		else
		{
			$msg = 'Client store is not configured with valid Token, Client Store Name and Gateway URL in module configuration';
			$reqstatus = "Failed";
		}
		$return['status'] = $reqstatus;
		$return['remark'] = $msg;				
		return json_encode($return);
    }
    /**
    * Function to Update current waybill status from Delhivery Server
    */		
	public function submitGodam($order, $suborder,$godamId) {
		//mage::log("Submitting order to Godam $orders");		
		$model = $this->load($godamId);
		mage::log('submitGodam Id = '.$godamId);
		$token = Mage::getStoreConfig('godam/godam/api_token');
		$clientstore = urlencode(Mage::getStoreConfig('godam/godam/client_store'));
		//$clientfc =  urlencode(Mage::getStoreConfig('godam/godam/client_fc'));
		$supplierid =  urlencode(Mage::getStoreConfig('godam/godam/supplier_id'));
		$url =  Mage::getStoreConfig('godam/godam/api_url');
		$apiversion =  urlencode(Mage::getStoreConfig('godam/godam/api_version'));		
		$url .= "oms/api/create/?client_store=".$clientstore."&version=".$apiversion;	
		//echo $clientstore;die;
		//return "in model update funciton";
		mage::log($url);
		if($clientstore && $token && $url)
		{		
			$params = array();
			$package_data = $this->getPackageData($order, $suborder);
			$params['data'] = '[' . json_encode($package_data) . ']';
			
			mage::log("-----package_data starts----");
			mage::log($package_data);
			mage::log("-----package_data ends------");
			$result = Mage::helper('godam')->Executecurl($url, 'post', $params);
			$result = (array) json_decode($result);
			mage::log($result);
			if ($result['Request Id']) {
				$model->setData('request_id', $result['Request Id']);
				mage::log("Request Id = ".$result['Request Id']);
				$model->setData('godamstate', '2')->save();
			}
		}
		return;
    }	
	 /**
	 * Function to update order created status from Godam
	 */
	public function changeOrderStatus($requestid, $status) {
		mage::log("Delhivery_Godam_Model_Godam::changeOrderStatus called");
		$model = Mage::getModel('godam/godam')->loadByRequestId($requestid);
		if($model->getRequestId() == $requestid)
		{
			$model->setData('godamstatus',$status);
			$model->save();
			$return['status'] = "Success";
			$return['remark'] = "RequestID $requestid Updated Successfully";	
		}
		else
		{
			$return['status'] = "Failed";
			$return['remark'] = "RequestID $requestid Not found";
		}
		mage::log($return);
		return json_encode($return);
    }	
	 /**
	 * Function to get waybill details if waybill number is supplied
	 */
	public function loadByOrderSuborderId($order, $suborder) {
		mage::log("Delhivery_Godam_Model_Godam::loadByOrderSuborderId called");
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$query = "SELECT godam_id FROM " . $resource->getTableName('godam/godam')." WHERE orderincid = '$order' AND suborderid = '$suborder'";
		$data = $readConnection->fetchOne($query);
		$orderdata = Mage::getModel('godam/godam')->load($data);
        return $orderdata;
    }
	 /**
	 * Function to get waybill details if waybill number is supplied
	 */
	public function getPackageData($orderid, $suborder) {
		mage::log("Delhivery_Godam_Model_Godam::getPackageData called");
		$order = Mage::getModel('sales/order')->load($orderid);
		$supplierid = urlencode(Mage::getStoreConfig('godam/godam/supplier_id'));
		$address = Mage::getModel('sales/order_address')->load($order->shipping_address_id);
		$package_data = array(); // package data feed
		$consignee = array();
		$invoice = array();
		$orderline = array();
		$products=array();
		$methodcode = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' ) ? "COD" :"Pre-Paid";
		
		$advamount = ($order->getPayment()->getMethodInstance()->getCode() != 'cashondelivery' ) ? $order->getGrandTotal() : "00.00";
		$ordered_items = $order->getAllItems();
		$suborderitem = explode("-",$suborder);
		foreach($ordered_items as $item){
			
			if($suborderitem[0] == $item->getItemId())
			{
				$product=array();
				$product_unit_price= $item->getPrice();
				$product_tax_percent= $item->getData('tax_percent');				
				
				$product['prod_num']= $item->getSku();
				$product['prod_name']= $item->getName();
				$product['prod_sku']= $item->getSku();				 
				$mproduct = Mage::getModel('catalog/product')->load($item->getProductId());		 
				$product['prod_desc']= substr($mproduct->getShortDescription(),0,500);
				$mrps = $mproduct->getMsrp();
				$product['prod_qty']= intval($item->getQtyOrdered());
				$qty=intval($item->getQtyOrdered());
				$products[]=$product;
				$rowtotal = $item->getRowTotalInclTax();
				$taxamout = $item->getTaxAmount(); //die("dsfsdf");
				$discount = $item->getDiscountAmount();
				//$codamount = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' ) ? $item->getRowTotal() : "00.00";
			}
		}

		/////////////start: building the package feed/////////////////////
		$package_data['order_id'] = $order->increment_id; // client order number
		$package_data['order_date'] = $order->updated_at;
		$consignee['shipping_name'] = $address->getName(); // consignee name
		$consignee['shipping_add1'] = $address->getStreet(1);
		if($address->getStreet(2))
		$consignee['shipping_add2'] = $address->getStreet(2);
		$consignee['shipping_pin'] = $address->getPostcode();
		$consignee['shipping_city'] = $address->getCity();
		$consignee['email'] = $order->getCustomerEmail();
		if($address->getRegion())
		$consignee['shipping_state'] = $address->getRegion();
		$consignee['shipping_country'] = $address->getCountry();
		if($address->getTelephone())
		$consignee['shipping_ph1'] = $address->getTelephone();
		$extras['shipment_id']='none';
		
		/////////////Add order line data/////////////////////
		$orderline['order_line_id'] = $suborder; // client order number
		$orderline['payment_mode'] = $methodcode;				
		//$orderline['fulfillment_mode'] = 'BS'; 
		$orderline['shipment_id'] = 'None'; 
		$orderline['supplier_id'] = $supplierid;
		$orderline['couriers'] = $order->getShippingDescription();
		$orderline['waybill_number'] = '';
		$orderline['express_delivery'] = 'True';
			// Add invoice data to order line
			$tax_info = $order->getFullTaxInfo();
			if ($order->hasInvoices()) {
				$invIncrementIDs = '';
				$invDate = '';
				foreach ($order->getInvoiceCollection() as $inv) {
					$invIncrementIDs = $inv->getIncrementId();
					$invDate = $inv->getCreatedAt();				
				} 
			}					
			$rowtotalUpdated=$rowtotal+floatval($order->getShippingAmount()/count($order->getAllItems()));
			$invoice['unit_price'] = floatval($product_unit_price);
			$invoice['unit_taxes'] =  floatval($product_unit_price*($product_tax_percent)/100);		
			$invoice['total_price'] = floatval($product_unit_price);//floatval($rowtotalUpdated-$discount); 
			$invoice['total_cst'] = floatval(0.00);
			$invoice['total_vat'] = floatval(0.00); 						
			$invoice['gross_value'] = floatval($product_unit_price*$qty);
			$invoice['shipping_price'] = floatval($order->getShippingAmount()/count($order->getAllItems()));				
			$invoice['net_amount'] = floatval($invoice['gross_value']-$discount);
			$invoice['total_taxes'] = 0;//floatval($taxamout);
			$codamount = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' ) ? ($rowtotalUpdated-$discount): "00.00";
			$invoice['cod_amount'] = floatval($product_unit_price);//floatval($codamount); // client order number			
			$invoice['discount'] = floatval($discount); 
			$invoice['vat_percentage'] =floatval(0.00);
			$invoice['cst_percentage'] =floatval(0.00); 
			$invoice['tax_percentage'] =floatval($product_tax_percent);
			$invoice['advance_payment'] = floatval($advamount);				
			$invoice['round_off'] = floatval(0.00); 
			$invoice['mrp'] = floatval($mrps);
			$invoice['invoice_number'] = ($invIncrementIDs) ? $invIncrementIDs : NULL; 
			$invoice['invoice_date'] = ($invDate) ? $invDate : NULL;										
			mage::log("Invoice Data");
			mage::log($invoice);
			$orderline['Invoice'] = $invoice;
			$orderline['Extra'] = $extras;
			$orderline['Products'] = $products;					
			$package_data['OrderLine'] = $orderline;
			$package_data['consignee'] = $consignee;			
			mage::log($package_data);// echo"<pre>";print_r($package_data); die;
			return $package_data;
    }	
	 /**
	 * Function to get waybill details if waybill number is supplied
	 */	
	public function loadByOrderId($id)
    {
        //mage::log("Loading order id");
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$query = "SELECT godam_id FROM " . $resource->getTableName('godam/godam')." WHERE orderincid = $id";
		$data = $readConnection->fetchOne($query);
		$orderdata = Mage::getModel('godam/godam')->load($data);
        return $orderdata;
    }
	 /**
	 * Function to get waybill details if waybill number is supplied
	 */	
	public function loadByRequestId($id)
    {
        //mage::log("Loading order id");
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$query = "SELECT godam_id FROM " . $resource->getTableName('godam/godam')." WHERE request_id = $id";
		mage::log($query);
		$data = $readConnection->fetchOne($query);
		$orderdata = Mage::getModel('godam/godam')->load($data);
        return $orderdata;
    }	
	 /**
	 * Function to get Complete order shipment if Godam Status is shipped
	 */	
	
	public function completeShipment($orderIncrementId,$shipmentTrackingNumber,$shipmentCarrierTitle)
	{
		/**
		 * It can be an alphanumeric string, but definitely unique.
		 */
		//$orderIncrementId = 'SPECIFIC_ORDER_INCREMENT_ID';
	 
		/**
		 * Provide the Shipment Tracking Number,
		 * which will be sent out by any warehouse to Magento
		 */
		//$shipmentTrackingNumber = 'SPECIFIC_TRACKING_NUMBER';
	 
		/**
		 * This can be blank also.
		 */
		$customerEmailComments = "Order has been shipped with  $shipmentTrackingNumber";
	 
		$order = Mage::getModel('sales/order')
					 ->loadByIncrementId($orderIncrementId);
	// echo $order->getId();die;
		if (!$order->getId()) {
			Mage::throwException("Order does not exist, for the Shipment process to complete");
		}
	 
		if ($order->canShip()) {
			try {
				$shipment = Mage::getModel('sales/service_order', $order)
								->prepareShipment($this->_getItemQtys($order));
	 
				/**
				 * Carrier Codes can be like "ups" / "fedex" / "custom",
				 * but they need to be active from the System Configuration area.
				 * These variables can be provided custom-value, but it is always
				 * suggested to use Order values
				 */
				$shipmentCarrierCode = 'dlastmile';
				//$shipmentCarrierTitle = 'Delhivery';
	 
				$arrTracking = array(
					'carrier_code' => isset($shipmentCarrierCode) ? $shipmentCarrierCode : $order->getShippingCarrier()->getCarrierCode(),
					'title' => isset($shipmentCarrierTitle) ? $shipmentCarrierTitle : $order->getShippingCarrier()->getConfigData('title'),
					'number' => $shipmentTrackingNumber,
				);
	 
				$track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
				$shipment->addTrack($track);
	 
				// Register Shipment
				$shipment->register();
	 			//print_r($shipment);die;
				// Save the Shipment
				$this->_saveShipment($shipment, $order, $customerEmailComments,$arrTracking);
	 
				// Finally, Save the Order
				$this->_saveOrder($order);
			} catch (Exception $e) {
				throw $e;
			}
		}
	}
	 
	/**
	 * Get the Quantities shipped for the Order, based on an item-level
	 * This method can also be modified, to have the Partial Shipment functionality in place
	 *
	 * @param $order Mage_Sales_Model_Order
	 * @return array
	 */
	protected function _getItemQtys(Mage_Sales_Model_Order $order)
	{
		$qty = array();
	 
		foreach ($order->getAllItems() as $_eachItem) {
			if ($_eachItem->getParentItemId()) {
				$qty[$_eachItem->getParentItemId()] = $_eachItem->getQtyOrdered();
			} else {
				$qty[$_eachItem->getId()] = $_eachItem->getQtyOrdered();
			}
		}
	 
		return $qty;
	}
	 
	/**
	 * Saves the Shipment changes in the Order
	 *
	 * @param $shipment Mage_Sales_Model_Order_Shipment
	 * @param $order Mage_Sales_Model_Order
	 * @param $customerEmailComments string
	 */
	protected function _saveShipment(Mage_Sales_Model_Order_Shipment $shipment, Mage_Sales_Model_Order $order, $customerEmailComments = '',$tracking)
	{
		/*$shipment->getOrder()->setIsInProcess(true);
		$transactionSave = Mage::getModel('core/resource_transaction')
							   ->addObject($shipment)
							   ->addObject($shipment->getOrder())
							   ->save();
	echo 'Dinesh';die;
		$emailSentStatus = $shipment->getData('email_sent');
		
		if (!is_null($customerEmail) && !$emailSentStatus) {
			$shipment->sendEmail(true, $customerEmailComments);
			$shipment->setEmailSent(true);
		}*/
		//print_r($tracking); echo $tracking['title'];die;
		 $order = Mage::getModel("sales/order")->loadByIncrementId($order->getIncrementId());
		
	
					try {
						if($order->canShip()) {
							//Create shipment
							$shipmentid = Mage::getModel('sales/order_shipment_api')
											->create($order->getIncrementId(), array());
							//Add tracking information
							$ship = Mage::getModel('sales/order_shipment_api')
											->addTrack($tracking, array());       
						}
					}catch (Mage_Core_Exception $e) {
					// print_r($e);
					}
			
		return $this;
	}
 
	/**
	 * Saves the Order, to complete the full life-cycle of the Order
	 * Order status will now show as Complete
	 *
	 * @param $order Mage_Sales_Model_Order
	 */
	protected function _saveOrder(Mage_Sales_Model_Order $order)
	{
		$order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
		$order->setData('status', Mage_Sales_Model_Order::STATE_COMPLETE);
	 
		$order->save();
	 
		return $this;
	}			
}
