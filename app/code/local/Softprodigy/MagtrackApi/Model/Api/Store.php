<?php
/**
 * Softprodigy
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Softprodigy.com license that is
 * available through the world-wide-web at this URL:
 * http://www.Softprodigy.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagTrack
 */

/**
 * SimiSalestracking Api Orders Server Model
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagtrackApi
 * @author      Softprodigy Developer
 */
class Softprodigy_MagtrackApi_Model_Api_Store extends Softprodigy_MagtrackApi_Model_Api_Abstract
{
     
	public function apiIndex($params)
	{
		$allStores = Mage::app()->getStores();
		$data = array();
		$i=0;
		foreach ($allStores as $_eachStoreId => $val)
		{
			$data[$i]['store_code'] = Mage::app()->getStore($_eachStoreId)->getCode();
			$data[$i]['store_name'] = Mage::app()->getStore($_eachStoreId)->getName();
			$data[$i]['store_id'] = Mage::app()->getStore($_eachStoreId)->getId();
			$i++;
		}
		 return $data; 
	 }
}
