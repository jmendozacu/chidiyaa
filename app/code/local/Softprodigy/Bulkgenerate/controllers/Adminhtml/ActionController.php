<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
// require_once BP . '/app/code/core/Mage/Adminhtml/controllers/Sales/OrderController.php';

/**
 * Description of Order
 *
 * @author root
 */
class Softprodigy_Bulkgenerate_Adminhtml_ActionController extends 
Mage_Adminhtml_Controller_Action{
	
	protected function _isAllowed()
	{
		return true;
	}

	public function exportPaymentOptionsAction() {
	 	 	$fileName = 'bulkgenerate_payment_modes.csv';        
	 	 /** @var $gridBlock Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid */$gridBlock = $this->getLayout()->createBlock('bulkgenerate/adminhtml_paymentoptions_grid');        
	 	     $website = Mage::app()->getWebsite($this->getRequest()->getParam('website'));
	 	     if ($this->getRequest()->getParam('conditionName')) {           
	 	      $conditionName = $this->getRequest()->getParam('conditionName');
	 	       }             
	 	            $gridBlock->setWebsiteId($website->getId())->setConditionName($conditionName);    
	 	                $content = $gridBlock->getCsvFile();   
	 	                     $this->_prepareDownloadResponse($fileName, $content);
    }
     
}
