<?php
class Extendware_EWBotBlocker_Block_Bot_Verify extends Extendware_EWCore_Block_Mage_Core_Template
{
	public function getRedirectParamValue() {
		return $this->getRequest()->getParam($this->getRedirectParamName());
	}

	public function getRedirectParamName() {
		return Mage_Core_Controller_Varien_Action::PARAM_NAME_URL_ENCODED;
	}
	
	public function getRecaptcha() {
		return Mage::getSingleton('ewbotblocker/recaptcha');
	}
}
