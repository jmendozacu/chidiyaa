<?php

class Softprodigy_Pmcm_Model_Express extends Mage_Paypal_Model_Express
{
	 public function canUseForCurrency($currencyCode)
     {
        $result = $this->_pro->getConfig()->isCurrencyCodeSupported($currencyCode);
        if ($result == false) {
            $result = strpos(Mage::helper('pmcm')->getConfig('extra_currencies'), Mage::app()->getStore()->getCurrentCurrencyCode());
		}
        return $result;
         
    }

}
?>
