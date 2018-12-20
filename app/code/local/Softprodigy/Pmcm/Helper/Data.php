<?php
/**
 * Softprodigy System Solutions Pvt. Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.idealiagroup.com/magento-ext-license.html
 *
 * @category    Softprodigy
 * @package     Softprodigy_Pmcm
 * @copyright   Copyright (c) 2015 Softprodigy System Solutions Pvt. Ltd (http://www.softprodigy.com)
 * @license    http://www.opensource.org/licenses/gpl-license.php  GNU General Public License
 */
class Softprodigy_Pmcm_Helper_Data extends Mage_Core_Helper_Abstract
{
    public static function getBaseCurrency()
    {
        return Mage::app()->getStore()->getBaseCurrencyCode();
    }

    public function getCurrencyArray()
    {
        return explode(',', self::getConfig('extra_currencies'));
    }

    public static function getSupportedCurrency()
    {
        return array('AUD', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN',
            'NOK', 'NZD', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'USD', 'TWD', 'THB');
    }

    public static function shouldConvert()
    {
        //return self::isActive() && !in_array(Mage::app()->getStore()->getCurrentCurrencyCode(), self::getSupportedCurrency()) && !in_array(self::getBaseCurrency(),self::getSupportedCurrency());
        return self::isActive() && !in_array(self::getBaseCurrency(), self::getSupportedCurrency());
    }

    public static function getConfig($name = '')
    {
        if ($name) {
            return Mage::getStoreConfig('payment/pmcm/' . $name);
        }
        return;
    }

    public static function getToCurrency()
    {
        $to = self::getConfig('to_currency');
        if (!$to) {
            $to = 'USD';
        }
        return $to;
    }

    public function getCurrentExchangeRate()
    {
        $auto = self::getConfig('auto_rate');
        if ($auto) {
            $current = Mage::app()->getStore()->getCurrentCurrencyCode();
            $to = self::getToCurrency();
            $rate = Mage::getModel('directory/currency')->getCurrencyRates($current, $to);
            //var_dump($rate);
            if (!empty($rate[$to])) {
                $rate = $rate[$to];
            } else {
                $rate = 1;
            }
        } else {
            $rate = self::getConfig('rate');
        }
        return $rate;
    }

    public static function isActive()
    {
        $state = self::getConfig('active');
        if (!$state) {
            return;
        }
        return $state;

    }

    public function convertAmount($amount = false)
    {
        return self::getExchangeRate($amount);
    }

    public static function getExchangeRate($amount = false)
    {
        if (!self::shouldConvert()) {
            return $amount;
        }
        if (!$amount) {
            return;
        }
        $auto = self::getConfig('auto_rate');
        if ($auto) {
            $current = Mage::app()->getStore()->getCurrentCurrencyCode();
            $base = Mage::app()->getStore()->getBaseCurrencyCode();
            $to = self::getToCurrency();
            //$rate = Mage::getModel('directory/currency')->getCurrencyRates($current, $to);
            $rate = Mage::getModel('directory/currency')->getCurrencyRates($base, $to);
            //var_dump($rate);
            if (!empty($rate[$to])) {
                $rate = $rate[$to];
            } else {
                $rate = 1;
            }
        } else {
            $rate = self::getConfig('rate');
        }
        if ($rate) {
            return $amount * $rate;
        }
        return;
    }
    
    public function check( )
	{
		$check = Mage::getSingleton('core/session')->getIsTrue('true');
		if($check == 'true') {
			return true;
		}
		if($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
			return true;
		}
		$response = $this->getData('70', $_SERVER['HTTP_HOST']);
		$data = json_decode($response);
		if($data->response == 'true') {
			Mage::getSingleton('core/session')->setIsTrue('true');
			return true;
		} else {
			return false;
		}
	}
	
	private function getData($pid,$url) {
		// Get cURL resource
		$curl = curl_init();
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'http://www.softprodigy.com/store/index.php/encyptext/?pid='.$pid.'&url='.$url,
			CURLOPT_USERAGENT => 'Codular Sample cURL Request'
		));
		// Send the request & save response to $resp
		$resp = curl_exec($curl);
		// Close request to clear up some resources
		curl_close($curl);
		
		return $resp;
	}

}
