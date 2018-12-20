<?php
class Softprodigy_Message_Model_Text extends Mage_Core_Model_Abstract
{
	const ORDER_PLACE_EVENT = 1; // sales_order_place_after
	const ORDER_SHIP_EVENT = 2; // order ship
	const ORDER_COMPLETE_EVENT = 3; // order compete
	private $_enabled;
	private $_template;
	private $_recipient;
	private $_sender;
	private $_parsed;
	private $_textCost = 1;
	private $_pathData = array
	(
		self::ORDER_PLACE_EVENT => array
		(
			'enabled' => 'message/general/order_placed',
			'template' => 'message/general/order_create_event_template'
		),
		self::ORDER_SHIP_EVENT => array
		(
			'enabled' => 'message/general/order_shipped',
			'template' => 'message/general/order_ship_event_template'
		),
		self::ORDER_COMPLETE_EVENT => array
		(
			'enabled' => 'message/general/order_complete',
			'template' => 'message/general/order_complete_template'
		),
	);

	public function init($type, $recipient)
	{
		
		$this->_enabled = Mage::getStoreConfigFlag($this->_pathData[$type]['enabled']);
		$this->_template = Mage::getStoreConfig($this->_pathData[$type]['template']);
		 $this->_recipient = preg_replace('/\s+/', '', $recipient);

		$this->_sender = Mage::app()->getStore()->getName();

		if(empty($this->_sender))
		{
			$this->_sender = Mage::app()->getStore()->getName();
		}

		if(strlen($this->_sender))
		{
			$this->_sender = substr($this->_sender, 0, 11);
		}
	}

	public function parseTemplate($input)
	{
		//die('template');
		if(!empty($input['order']))
		{
			/** @var Mage_Sales_Model_Order $order */
			$order = $input['order'];
		}

		$data = array
		(
			'customer.name' => $order->getBillingAddress()->getName(),
			'customer.forename' => $order->getBillingAddress()->getFirstname(),
			'customer.surname' => $order->getBillingAddress()->getLastname(),
			'customer.email' => $order->getBillingAddress()->getEmail(),
			'store.name' => Mage::getStoreConfig('general/store_information/name'),
			'store.email' => Mage::getStoreConfig('trans_email/ident_general/email'),
			'store.phone' => Mage::getStoreConfig('general/store_information/phone')
		);

		if(isset($order))
		{
			$core = Mage::helper('core');
			$data['order.increment'] = $order->getIncrementId();
			$data['order.id'] = $order->getId();
			$data['order.total'] = $core->currency($order->getGrandTotal(), true, false);
			$data['order.discount'] = $core->currency($order->getDiscountAmount(), true, false);
			$data['order.shipping'] = $core->currency($order->getShippingAmount(), true, false);
			$data['payment.title'] = $order->getPayment()->getMethodInstance()->getTitle();
			if($order->getTrackingNumbers())
			{
			$data['shipment.tracking'] = implode("\r\n", $order->getTrackingNumbers());
			}
			else
			{
			$data['shipment.tracking'] = 'N/A';
			}
		}

		$pattern = '/{{(.*?)[\|\|.*?]?}}/';

		$this->_parsed = preg_replace_callback($pattern, function($match) use ($data)
		{
			$match = explode('||',$match[1]);

			return isset($data[$match[0]]) ? $data[$match[0]] : $data[$match[1]] ;
		}, $this->_template);

		return $this;
	}

	public function sendCustomerOrder($customer, $order)
	{
		return $this->send(array('customer' => $customer, 'order' => $order));
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function send($data)
	{
		
		if(!$this->_enabled)
		{
			return false;
		}
			if(empty($this->_parsed))
		{
			$this->parseTemplate($data);
			
		}


		if(Mage::getStoreConfig('message/general/status') == 1)
		{
		require "Services/Twilio.php";
		$AccountSid = Mage::getStoreConfig('message/general/accountSid');
		$AuthToken = Mage::getStoreConfig('message/general/authToken');
		$client = new Services_Twilio($AccountSid, $AuthToken);
		$sms = $client->account->messages->sendMessage(
		// Step 6: Change the 'From' number below to be a valid Twilio number
		// that you've purchased, or the (deprecated) Sandbox number
		"+1 (925) 392-5088",
		// the number we are sending to - Any phone number
		$this->_recipient,
		// the sms body
		$this->_parsed
		);
		//echo "ship";die;
		// Display a confirmation message on the screen
		//echo "Sent message to $name";die;
		return true;
		}
		if(Mage::getStoreConfig('message/general/status_gateway') == 1)
			{
							//echo $this->_recipient;die('in');
			$user=Mage::getStoreConfig('message/general/user');
			$sender_id=Mage::getStoreConfig('message/general/sender_id');;           // sender id
			$mob_no = $this->_recipient;       //123, 456 being recepients number
			$pwd=Mage::getStoreConfig('message/general/password');               //your account password
			$msg=$this->_parsed;       //your message
			$str = trim(str_replace(' ', '%20', $msg));
			// to replace the space in message with  ‘%20’
			$url="http://login.smsgatewayhub.com/smsapi/pushsms.aspx?user=$user&pwd=$pwd&to=$mob_no&sid=$sender_id&msg=$str&fl=0&gwid=2";
			// create a new cURL resource
			// $url;die;
			$ch = curl_init();
			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// grab URL and pass it to the browser
			curl_exec($ch);
			// close cURL resource, and free up system resources
			curl_close($ch);
			}
	}
}
