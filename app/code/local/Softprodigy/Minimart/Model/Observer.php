<?php

class Softprodigy_Minimart_Model_Observer 
{
	public function sendNotification(Varien_Event_Observer $observer)
	{
		try 
		{
			/* @var Mage_Sales_Model_Order $order */
			$order = $observer->getOrder();
			$stateProcessing = $order::STATE_PROCESSING;
			// Only trigger when an order enters processing state.
			$email = $order->getCustomerEmail();
			if ($order->getState() == $stateProcessing || $order->getState() == 'complete' || $order->getState() == 'canceled') 
			{
				$user_token = Mage::getSingleton('core/resource')->getTableName('user_token');
				$query = "select * from {$user_token} where customer_email = '".$email."'";
				$data = Mage::getSingleton('core/resource')->getConnection('core_read')->query($query);
				$data = $data->fetch();
				
				if($order->getState() == $stateProcessing)
					$msg = 'Your order with order id -'.$order->getIncrementId().' is in '.$order->getState().' state.';
				if($order->getState() == 'complete') 	
					$msg = 'Your order with order id -'.$order->getIncrementId().' has been completed.';
				if($order->getState() == 'canceled') 	
					$msg = 'Your order with order id -'.$order->getIncrementId().' has been canceled.';	
				
				$msg = urlencode($msg);
				$order_id = urlencode($order->getIncrementId());
				if($data['token'])
				{
					$target_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)."minimart/miniapi/AndroidPushnotification?deviceId=".$data['token']."&msg=$msg&order_id=$order_id";
					$ch = curl_init($target_url);

					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
					curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

					curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

					$response_data = curl_exec($ch);
					
					/*echo '<pre>';
					print_r($response_data);
					die;*/
				}
			}
		} 
		catch (Exception $e) 
		{
			//echo $e->getMessage();
			//exit;
		}
	}	
}	
