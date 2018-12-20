<?php
class Extendware_EWBotBlocker_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract {
	public function match(Zend_Controller_Request_Http $request) {
		$path = trim($request->getPathInfo(), '/');
		if (Mage::helper('ewbotblocker')->isHoneypotPath($path) === true) {
			$request->setModuleName('ewbotblocker');
			$request->setControllerName('bot');
			$request->setActionName('reject');
			$request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $path);
			return true;
		}

		return false;
	}
}
