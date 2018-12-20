<?php
/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Godam
 * @copyright  Copyright (c) 2014 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 */
class Delhivery_Godam_Helper_Data extends Mage_Core_Helper_Abstract {
    
    public function getGodamUrl(){
        return $this->_getUrl('Godam');
    }
	/*
	* Function to execute curl
	* @return API response
	*/
	public function Executecurl($url, $type, $params){
		//echo $url;die;
		mage::log("Delhivery_Godam_Helper_Data::Executecurl called");
		//echo '<pre>';print_r($params);echo '</pre>';die;
		$token = Mage::getStoreConfig('godam/godam/api_token');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "$url");
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		if($type == 'post'):
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($params));
		endif;
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded',"Authorization: Token $token",'accept: application/json'));
		$retValue = curl_exec($ch);
		
		curl_close($ch);
		mage::log("Return Value from Delhivery_Godam_Helper_Data::Executecurl = $retValue");
		return $retValue;
	}
}
