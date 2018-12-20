<?php
class Softprodigy_Message_Adminhtml_EmailController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction(){
		 $this->loadLayout();
		 $this->renderLayout();
    } 
	public function indexAction(){
		$this->_initAction();
	}  
	public function editAction() {

			$this->loadLayout();
			$this->getLayout()->getBlock('head')->setTitle($this->__("Send Messages to all"));
			
			$this->_setActiveMenu('messagelinks/managemessage');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__("Send Messages"), Mage::helper('adminhtml')->__("Send Messages"));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('message/adminhtml_email_edit'))
				->_addLeft($this->getLayout()->createBlock('message/adminhtml_email_edit_tabs'));
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
			$this->renderLayout();
	}
	public function sendAction()
    {
			$customerID   =   $this->getRequest()->getParam('id');
       if ($customerID!='') {
			$this->loadLayout();
			$this->getLayout()->getBlock('head')->setTitle($this->__("Send Messages to all"));
			
			$this->_setActiveMenu('messagelinks/managemessage');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__("Send Messages"), Mage::helper('adminhtml')->__("Send Messages"));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('message/adminhtml_email_edit1'))
				->_addLeft($this->getLayout()->createBlock('message/adminhtml_email_edit1_tabs1'));
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
			$this->renderLayout();
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
			$this->_addContent($this->getLayout()->createBlock('message/adminhtml_email_edit2'))
				->_addLeft($this->getLayout()->createBlock('message/adminhtml_email_edit2_tabs2'));
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
			$this->renderLayout();
			}
		}
		public function sendmsgAction()
	{
			$param = $this->getRequest()->getParams();
			//print_r($param);
			echo $frommail = Mage::getStoreConfig('trans_email/ident_general/email');
			echo $storename = Mage::app()->getStore()->getName();
			echo $name = $param['name'];
			echo $email = $param['email'];
			echo $subject = $param['subject'];
			echo $msg = $param['description'];
		$emailTemplate  = Mage::getModel('core/email_template')
						->loadDefault('custom_email_template1');									
 
							//Create an array of variables to assign to template
							$emailTemplateVariables = array();
							$emailTemplateVariables['myvar1'] = $msg;
							 
							/**
							 * The best part :)
							 * Opens the activecodeline_custom_email1.html, throws in the variable array 
							 * and returns the 'parsed' content that you can use as body of email
							 */
							$processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
							 
							/*
							 * Or you can send the email directly, 
							 * note getProcessedTemplate is called inside send()
							 */
							$mail = Mage::getModel('core/email')
								 ->setToName($name)
								 ->setToEmail($email)
								 ->setBody($processedTemplate)
								 ->setSubject($subject)
								 ->setFromEmail($frommail)
								 ->setFromName($storename)
								 ->setType('html');
								 try{
								 //Confimation E-Mail Send
								 $mail->send();
									Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Email send to the user'));
								 $this->_redirect('*/*/edit');
								 }
								 catch(Exception $error)
								 {
								 Mage::getSingleton('core/session')->addError($error->getMessage());
								 return false;
								 }
	}
	public function sendmsgtoallAction()
		{
			$customerIds = Mage::getSingleton('core/session')->getCustomerId(); 
			$customerIds = unserialize($customerIds);
			$param = $this->getRequest()->getParams();
			$frommail = Mage::getStoreConfig('trans_email/ident_general/email');
			$storename = Mage::app()->getStore()->getName();
			$msg = $param['description'];
			$subject = $param['subject'];
			foreach ($customerIds as $value) {
				//echo "$value <br>";
				$customerID   =   $value;
				$customer = Mage::getModel('customer/customer')->load($customerID);
				$email = $customer->getEmail();
				$name = $customer->getFirstname();
				$emailTemplate  = Mage::getModel('core/email_template')
				->loadDefault('custom_email_template1');									
 
							//Create an array of variables to assign to template
							$emailTemplateVariables = array();
							$emailTemplateVariables['myvar1'] = $msg;
							 
							/**
							 * The best part :)
							 * Opens the activecodeline_custom_email1.html, throws in the variable array 
							 * and returns the 'parsed' content that you can use as body of email
							 */
							$processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
							 
							/*
							 * Or you can send the email directly, 
							 * note getProcessedTemplate is called inside send()
							 */
							$mail = Mage::getModel('core/email')
								 ->setToName($name)
								 ->setToEmail($email)
								 ->setBody($processedTemplate)
								 ->setSubject($subject)
								 ->setFromEmail($frommail)
								 ->setFromName($storename)
								 ->setType('html');
								 try{
								 //Confimation E-Mail Send
								 $mail->send();
								 $this->_redirect('*/*/edit');
								 }
								 catch(Exception $error)
								 {
								 Mage::getSingleton('core/session')->addError($error->getMessage());
								 return false;
								 }
		
		}
		}
}
