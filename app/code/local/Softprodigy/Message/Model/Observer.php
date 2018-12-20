<?php
class Softprodigy_Message_Model_Observer
{
	public function checkOrder($observer)
	{
		$invoice = $observer->getEvent()->getInvoice(); 
		$order = $invoice->getOrder(); 
		$O_id = $order -> getIncrementId();
		$orderd = Mage::getModel('sales/order')->loadByIncrementId($O_id);
		$shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($orderd)->load();
		if($shipmentCollection->count())
		{
			foreach ($shipmentCollection as $shipment){
				foreach($shipment->getAllTracks() as $tracknum)
				{
					$tracks[]=$tracknum->getNumber();
				}
			}
			echo '<pre>';
			print_r($tracks);
			$i = 0;
			foreach ($tracks as $track) {
				if($i == 0) 
					$html = "<a href= '".Mage::helper('shipping')->getTrackingPopupUrlBySalesModel($order)."'>".$track."</a>";
				else
					$html = "<a href= '".Mage::helper('shipping')->getTrackingPopupUrlBySalesModel($order)."'>".$track."</a>, ".$html;
			 }
			//echo $html ;  
			//$order = $shipment->getOrder();
			//echo  Mage::helper('shipping')->getTrackingPopupUrlBySalesModel($order);
			//echo '<pre>';
			//print_r($shipment);
			//die;
			
			//$order = $shipment->getOrder();

			//do something with order - get the increment id:
			//echo $order->getIncrementId().'----'.$order->getCustomerId();
			$customer = Mage::getModel('customer/customer')->load(75368);
			$customer->getId();
			$token = $customer->getToken();
			if(!empty($token))
			{
				if(empty($html))
					$fmsg = "Your Order '".$order->getIncrementId()."' is shipped.";
				else
					$fmsg = "Your Order '".$order->getIncrementId()."' is shipped. You can track order by tracking id(s) - ".$html." .";
				
				echo '<pre>';
				echo $fmsg;	
						
				$tokentype = $customer->gettokentype(); 
				if(($tokentype) == 'iphone')
				{
					$appkey[]= $token;	
					Mage::helper('message')->Pushnotification($appkey,$fmsg);
				}
				else if(($tokentype) == 'android')
				{
					$andkey[]= $token;
					Mage::helper('message')->AndroidPushnotification($andkey,$fmsg);
				}
				
			}
		}
		//die;	
	}
	private function sendOrder($order, $type, $msg = 'order')
	{
		/** @var Mage_Sales_Model_Order $order */
		$mobile = $order->getBillingAddress()->getTelephone();

		if(empty($mobile))
		{
			return false;
		}

		$customer = Mage::getModel('customer/customer')
				->setWebsiteId($order->getStore()->getWebsiteId())
				->load($order->getCustomerId());
		$text = Mage::getModel('message/text');

		$text->init($type, $mobile);

		if($text->sendCustomerOrder($customer, $order))
		{
			//die('cust');
			return true;
		}
		return false;
	}
	public function sendAccMsg($observer)
	{
		$this->sendOrder
				(
					$observer->getEvent()->getOrder(),
					Softprodigy_Message_Model_Text::ORDER_PLACE_EVENT,
					'Order place Event'
				);
	}
	public function sendAccShip($observer)
	{
		//echo "ship";die;
		$shipment = $observer->getEvent()->getShipment();
		$order = $shipment->getOrder();
		$this->sendOrder
				(
					$order,
					Softprodigy_Message_Model_Text::ORDER_SHIP_EVENT,
					'Order ship Event'
				);
	}
	public function sendAccComp($observer)
	{
			$order = $observer->getEvent()->getOrder();
		if($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
		$this->sendOrder
				(
					$observer->getEvent()->getOrder(),
					Softprodigy_Message_Model_Text::ORDER_COMPLETE_EVENT,
					'Order complete Event'
				);
			}
	}
}
