<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Softprodigy
 * @package    Softprodigy_MagtrackApi
 * @copyright  Copyright (c) 2014 SoftProdigy <magento@softprodigy.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Softprodigy_MagtrackApi_ApiController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        error_reporting(1);
        @ini_set('display_errors', 1);
        $data = file_get_contents("php://input");
        $data = preg_replace('/[\n\r]/', '', $data);	    
		$data = json_decode($data,true);
        $result = array('success' => 1);
        $data['call'] = $this->getRequest()->getParam('call');
        if (isset($data['call'])) {
            try {
                $result['data'] = Mage::getModel('softprodigy_magtrackapi/api')->run($data);
                $result['currency_symbol'] = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['error'] = $e->getCode();
                $result['data'] = $e->getMessage();
            }
        }
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        return;
    }
}
