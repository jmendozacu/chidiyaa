<?php
class Extendware_EWBotBlocker_BotController extends Extendware_EWCore_Controller_Frontend_Action
{
	public function indexAction() 
	{
		return '';
	}
	public function verifyAction() 
	{
		$this->mHelper()->deleteVerificationCookie();
		$collection = Mage::getModel('ewbotblocker/bot')->getCollection();
		$collection->addFieldToFilter('status', 'enabled');
		$collection->addFieldToFilter('ip_address', $this->mHelper()->getIpAddress());
		$collection->addDateToFilter('expires_at', '>=', now());
		$bot = $collection->getFirstItem();

		if ($bot->getId() > 0) {
			$bot->setLastSeenAt(now());
			$bot->setNumVisits($bot->getNumVisits() + 1);
			$bot->save();
			
			$this->_getSession()->addNotice($this->__('There is some suspicious traffic coming from your IP address. Please verify that you are human and you can return to browsing our store!'));
			$this->loadLayout();
			$this->renderLayout();
			return;
		}
		
		return $this->_redirectUrl(Mage::getBaseUrl());
	}
	
	public function verifyPostAction() 
	{
		if (Mage::helper('ewbotblocker/config')->isEnabled() === false) return;
		
		$recaptcha = Mage::getSingleton('ewbotblocker/recaptcha');
		if ($recaptcha->passed() === true) {
			Mage::getModel('ewbotblocker/bot')->loadByIpAddress($this->mHelper()->getIpAddress())->delete();
			return $this->_redirectReferer();
		}
		$this->_getSession()->addError($this->__('The entered CAPTCHA was not correct. Please try again'));
		return $this->_redirect('*/*/verify');
	}
	
	public function rejectAction() 
	{
		if (Mage::helper('ewbotblocker/config')->isEnabled() === false) return;
		try {
			if (Mage::helper('ewbotblocker/config')->isOnlyPostEnabled() === true) {
				if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] != 'POST') {
					return $this->_redirectUrl(Mage::getBaseUrl());
				}
			}
			
			$ipAddress = $this->mHelper()->getIpAddress();
			$useragent = $this->mHelper()->getUserAgent();
			
			$bot = Mage::getModel('ewbotblocker/bot');
			if (!$bot->loadByIpAddress($ipAddress)->getId()) {
				$bot->setStatus(Mage::helper('ewbotblocker/config')->getDefaultStatus());
				$bot->setIpAddress($ipAddress);
				$bot->setUserAgent($useragent);
				$bot->save();
			}
		} catch (Exception $e) {
			Mage::logException ($e);
		}
		
		return $this->_redirect('*/*/verify');
	}
}