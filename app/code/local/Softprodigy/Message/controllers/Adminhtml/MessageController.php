<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Softprodigy
 * @package    Softprodigy_Message
 * @copyright  Copyright (c) 2014 SoftProdigy <magento@softprodigy.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Softprodigy_Message_Adminhtml_MessageController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('messagelinks/managemessage')
			->_addBreadcrumb(Mage::helper('adminhtml')->__("Send Messages"), Mage::helper('adminhtml')->__("Send Messages"));
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()->renderLayout();
	}
	
	public function editAction() {

			$this->loadLayout();
			$this->getLayout()->getBlock('head')->setTitle($this->__("Send Messages to all"));
			
			$this->_setActiveMenu('messagelinks/managemessage');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__("Send Messages"), Mage::helper('adminhtml')->__("Send Messages"));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('message/adminhtml_message_edit'))
				->_addLeft($this->getLayout()->createBlock('message/adminhtml_message_edit_tabs'));

			$this->renderLayout();
	}
	
	public function sendmsgAction()
	{
		try
		{
			//echo Mage::getStoreConfig('message/general/status');die;
			if(Mage::getStoreConfig('message/general/status') == 1)
			{
			$param = $this->getRequest()->getParams();
			$number = $param['number'];
			$msg = $param['send_message'];
			$name = $param['name'];
			require "Services/Twilio.php";
			$AccountSid = Mage::getStoreConfig('message/general/accountSid');
			$AuthToken = Mage::getStoreConfig('message/general/authToken');
			$client = new Services_Twilio($AccountSid, $AuthToken);
			
			$people = array(
			//"+919592932627" => "Curious George",
			$number =>  $name,
			);
			foreach ($people as $number => $name) {
				
			$sms = $client->account->messages->sendMessage(
			// Step 6: Change the 'From' number below to be a valid Twilio number
			// that you've purchased, or the (deprecated) Sandbox number
			"+1 (925) 392-5088",
			// the number we are sending to - Any phone number
			$number,
			// the sms body
			"Hey $name,$msg"
			);
			//echo "ship";die;
			// Display a confirmation message on the screen
			echo "Sent message to $name";
			}
			}
			if(Mage::getStoreConfig('message/general/status_gateway') == 1)
			{
			$user='pooji.rajput';
			$sender_id='CHIDIA';           // sender id
			$mob_no = '8528738234';       //123, 456 being recepients number
			$pwd='151193';               //your account password
			$msg='Dear, you password is %password% . Thanks and regards';       //your message
			$str = trim(str_replace(' ', '%20', $msg));
			// to replace the space in message with  ‘%20’
			$url="http://login.smsgatewayhub.com/smsapi/pushsms.aspx?user=$user&pwd=$pwd&to=$mob_no&sid=$sender_id&msg=$str&fl=0&gwid=2";
			// create a new cURL resource
			// $url;die;
			$ch = curl_init();
			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL,$url);
			// grab URL and pass it to the browser
			curl_exec($ch);
			// close cURL resource, and free up system resources
			curl_close($ch);
			}
			
			//Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Message sent.'));
			//$this->_redirect('*/*/edit');	
			/*$msg = $param['send_message'];
			$customer = Mage::getModel('customer/customer')->getCollection()
					->addFieldToFilter('tokentype',array('in'=> array('android','iphone')))
					->addFieldToFilter('token',array('neq'=>'')); 
			echo "<pre>";
			//print_r($customer->getdata());exit;
			if($customer->count())
			{
				$android = array();
				$iphone = array();
				foreach($customer as $index)
				{
					if(($index->gettokentype()) == 'android')
					{
						if(!in_array(($index->gettoken()),$android))
							$android[] = $index->gettoken(); 
					}
					else if(($index->gettokentype()) == 'iphone')
					{
						if(!in_array(($index->gettoken()),$iphone))
							$iphone[$index->getId()] = $index->gettoken(); 
					}	
				}
				
				/*echo '<pre>';
				print_r($iphone);
				
				echo '<pre>';
				print_r($android);
				die;*/
				//if(!empty($android))
					//Mage::helper('message')->AndroidPushnotification($android,$msg);
					
				//if(!empty($iphone))	
					//Mage::helper('message')->Pushnotification($iphone,$msg);
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('SMS send to the user'));
				$this->_redirect('*/*/edit');	
			//}
			//else
			//{
			//	Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('No App user found.'));
			//	$this->_redirect('*/*/edit');
			//}
			
		}
		catch(Exception $e)
		{
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__($e->getMessage()));
			$this->_redirect('*/*/edit');	
		}
	}
	
}
