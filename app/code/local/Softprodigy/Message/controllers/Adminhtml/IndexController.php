<?php
class Softprodigy_Message_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction(){
		 $this->loadLayout();
		 $this->renderLayout();
    } 
	public function indexAction(){
		$this->_initAction();
	}
	public function editAction()
    {
			$customerID   =   $this->getRequest()->getParam('id');
       if ($customerID!='') {
			$this->loadLayout();
			$this->getLayout()->getBlock('head')->setTitle($this->__("Send Messages to all"));
			
			$this->_setActiveMenu('messagelinks/managemessage');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__("Send Messages"), Mage::helper('adminhtml')->__("Send Messages"));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('message/adminhtml_message_edit1'))
				->_addLeft($this->getLayout()->createBlock('message/adminhtml_message_edit1_tabs1'));

			$this->renderLayout();
    }  
}
public function sendmsgAction()
	{
		try
		{
			$param = $this->getRequest()->getParams();
			//print_r($param);
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
			"+1 (925) 392-5088",
			$number,
			"Hey $name,$msg"
			);
			echo "Sent message to $name";
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('SMS send to the user'));
				$this->_redirect('*/*/index');
		}
		catch(Exception $e)
		{
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__($e->getMessage()));
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('SMS send to the user'));
				$this->_redirect('*/*/index');
		}
	}
		public function massDeleteAction()
		{
			$Ids = $this->getRequest()->getParam('id');
			if(!is_array($Ids)) {
			die('die');
			}
			else
			{
			$this->loadLayout();
			$this->getLayout()->getBlock('head')->setTitle($this->__("Send Messages to all"));
			
			$this->_setActiveMenu('messagelinks/managemessage');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__("Send Messages"), Mage::helper('adminhtml')->__("Send Messages"));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('message/adminhtml_message_edit2'))
				->_addLeft($this->getLayout()->createBlock('message/adminhtml_message_edit2_tabs2'));

			$this->renderLayout();
			}
		}
		public function sendmsgtoallAction()
		{
			$customerIds = Mage::getSingleton('core/session')->getCustomerId(); 
			$customerIds = unserialize($customerIds);
			require "Services/Twilio.php";
			$AccountSid = Mage::getStoreConfig('message/general/accountSid');
			$AuthToken = Mage::getStoreConfig('message/general/authToken');
			foreach ($customerIds as $value) {
				//echo "$value <br>";
				$customerID   =   $value;
				$customer = Mage::getModel('customer/customer')->load($customerID);
				$phone = $customer->getPrimaryBillingAddress()->getTelephone();
		try
			{
			$param = $this->getRequest()->getParams();
			//print_r($param);
			$number = $phone;
  			$msg = $param['send_message'];
			//$name = $param['name'];
			$client = new Services_Twilio($AccountSid, $AuthToken);
			
			$people = array(
			//"+919592932627" => "Curious George",
			$number =>  $name,
			);
			foreach ($people as $number => $name) {
				
			$sms = $client->account->messages->sendMessage(
			"+1 (925) 392-5088",
			$number,
			"Hey $name,$msg"
			);
			echo "Sent message to $name";
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('SMS send to all users'));
				$this->_redirect('*/*/index');	
		}
		catch(Exception $e)
		{
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__($e->getMessage()));
			$this->_redirect('*/*/index');	
		}
		}
		}
}
?>
