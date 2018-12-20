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
class Softprodigy_Bulkgenerate_Adminhtml_InvoiceController extends 
Mage_Adminhtml_Controller_Action{
	public function generateAction() {
	 		$this->loadLayout();
	 		// var_dump($this->getFullActionName());
	 		$this->renderLayout();

    }
    public function getOrderDetailsAction(){
    	// echo "<pre>";
    	// print_r($this->getRequest()->getParams());exit;
    	if($this->getRequest()->getParams()['isAjax']){
    		$response_data = $this->getOrderInfo($this->getRequest()->getParams()['order_id']);
    		echo json_encode($response_data);
    	}
    }
    public function getOrderInfo($order_id = null){
    	$order_info = Mage::getModel('sales/order')->load($order_id);
    	$order_details['order_id'] = $order_info->getIncrementId();
    	$payment_method_code  = $order_info->getPayment()->getMethodInstance()->getCode();
    	$payment_info = Mage::getModel('bulkgenerate/paymentoption')->getCollection();
    	$payment_info
    				->addFieldToSelect(array('capture_mode'))
                    ->addFieldToFilter('payment_code',$payment_method_code);
    	$order_details['created_at'] = $order_info['created_at'];
		try {
			if(!$order_info->canInvoice()){
				Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
			}
			 
			$invoice = Mage::getModel('sales/service_order', $order_info)->prepareInvoice();
			 
			if (!$invoice->getTotalQty()) {
				Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
			}
			 $capture_mode = ($payment_info->getFirstItem()->getCaptureMode()) ? Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE : Mage_Sales_Model_Order_Invoice::NOT_CAPTURE;
			$invoice->setRequestedCaptureCase($capture_mode);
			$invoice->register();
			$invoice->getOrder()->setIsInProcess(true);
			$transactionSave = Mage::getModel('core/resource_transaction')
			->addObject($invoice)
			->addObject($invoice->getOrder());
			 
			$transactionSave->save();
			$order_info->save();
			//$invoice->sendEmail(true);  // commented to stop send bulk email while generating invoice
			$order_new_info = Mage::getModel('sales/order')->load($order_info->getId());
			$order_details['status'] = $order_new_info->getStatus();
			$order_details['invoice_id'] = $invoice->getIncrementId();
		}catch (Mage_Core_Exception $e) {
		 	 $order_details['error'] = $e->getMessage();
		}
		return $order_details;
    }	
}
