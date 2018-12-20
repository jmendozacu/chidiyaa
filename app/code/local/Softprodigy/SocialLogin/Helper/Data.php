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
 * @package     Softprodigy_Sociallogin
 * @copyright   Copyright (c) 2015 Softprodigy System Solutions Pvt. Ltd (http://www.softprodigy.com)
 * @license    http://www.opensource.org/licenses/gpl-license.php  GNU General Public License
 */
 
class SoftProdigy_SocialLogin_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function redirect404($frontController)
    {
        $frontController->getResponse()
            ->setHeader('HTTP/1.1', '404 Not Found');
        $frontController->getResponse()
            ->setHeader('Status', '404 File not found');

        $pageId = Mage::getStoreConfig('web/default/cms_no_route');
        if (!Mage::helper('cms/page')->renderPage($frontController, $pageId)) {
            $frontController->_forward('defaultNoRoute');
        }
    }

    public function checkShowSociallogin(){
        $result = false;
        $servers = array(
            'facebook',
            'google',
            'twitter',
            'linkedin',
            'yahoo'
        );
        $count = 0;
        foreach($servers as $server){
            $xml_path = $this->_getXmlPath($server);
            $server_enable = Mage::getStoreConfig($xml_path);
            if($server_enable == 1){
                $count++;
            }
        }

        if($count != 0){
            $result = true;
        }

        return $result;
    }

    protected function _getXmlPath($server_name){
        $data = "softprodigy_sociallogin/".$server_name.'/enabled';
        return $data;
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
		$response = $this->getData('76', $_SERVER['HTTP_HOST']);
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
