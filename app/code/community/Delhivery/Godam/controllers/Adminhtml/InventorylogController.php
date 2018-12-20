<?php
/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Godam
 * @copyright  Copyright (c) 2014 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 * @purpose    Waybills admin controller actions   
 */
class Delhivery_Godam_Adminhtml_InventorylogController extends Mage_Adminhtml_Controller_Action {

     /**
     * Init Action to specify active menu and breadcrumb of the module
     */
    protected function _initAction() {
	     $this->loadLayout()
                ->_setActiveMenu('godam/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }
     /**
     * Function to render waybill layout block
     */
    public function indexAction() {
        $this->loadLayout()
                ->renderLayout();
    }
     /**
     * Loads default grid view of admin module
     */ 	
	public function gridAction()
	{
	$this->loadLayout();
	$this->getResponse()->setBody(
	$this->getLayout()->createBlock('godam/adminhtml_inventorylog_grid')->toHtml());
	}

     /**
     * Function to Update current waybill status from Delhivery Server
     */		
	public function updateFromGodamAction() {
		mage::log("GodamController::updateFromGodamAction called");
		$model = Mage::getModel('godam/godam');
		$orders = $this->getRequest()->getParam('godam');
		$token = Mage::getStoreConfig('godam/godam/api_token');
		$clientstore = urlencode(Mage::getStoreConfig('godam/godam/client_store'));
		//$clientfc =  urlencode(Mage::getStoreConfig('godam/godam/client_fc'));
		$url =  Mage::getStoreConfig('godam/godam/api_url');
		$apiversion =  urlencode(Mage::getStoreConfig('godam/godam/api_version'));
		//print_r($orders);die;
		if($clientstore && $token && $url)
		{
		$succsscount = 0;
		$failcount = 0;
		$msg = '';
		foreach ($orders as $gorder) {
				
				$model = Mage::getModel('godam/godam')->load($gorder);
				
                //if($model->state == 2): // Submit only if state is submitted
				$url =  Mage::getStoreConfig('godam/godam/api_url');
				$url .= "oms/api/detail/$model->orderincid/$model->suborderid/?client_store=".$clientstore."&version=".$apiversion;
				mage::log($url);
				//echo $url;
				$result = Mage::helper('godam')->Executecurl($url,'','');
				//echo $result;die;
				if($result)
				{
				$result = json_decode($result);
				//echo($result->OrderLine->status);die;
				if($result->OrderLine->status):
					// Add shipment to magento order if Order in godam is shipped
					if($result->OrderLine->status == 'shipped')
					{
						$model->completeShipment($model->orderincid,$result->OrderLine->waybill, $result->OrderLine->courier);
					}
					$model->setData('godamstatus',$result->OrderLine->status);
					$model->setData('awb',$result->OrderLine->waybill); 	 
					$model->setData('courier',$result->OrderLine->courier);
					$model->setData('courierstatus',$result->OrderLine->courier_status);
					$model->setData('courier_last_scan_location',$result->OrderLine->courier_last_scan_location);
					$model->setData('courier_lsd',$result->OrderLine->courier_lsd);
					
					$model->save();
					$msg = "$model->orderincid|$model->suborderid Updated Successfully<br />";
					$succsscount++;
					
				else:
					$msg = "No update found for $model->orderincid|$model->suborderid";
					$failcount++;
				endif;
				
				}
				else
				{
					$msg = "Request time out while updating $model->orderincid|$model->suborderid<br />";
					$failcount++;
				}
        }
		//echo $msg;
        $msg .= "<br />$succsscount orders updated successfully. $failcount orders Failed";
        Mage::getSingleton('adminhtml/session')->addSuccess($msg);
		mage::log($msg);
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('godam')->__('Please add valid Token, Client Store Name and Gateway URL in module configuration'));
		}		
		$this->_redirect('*/*/index');
    }	
     /**
     * Function to submit manifest about new waybills to Delhivery Server
     */		
	public function submitGodamAction() {
		mage::log("GodamController::submitGodamAction called");
		$model = Mage::getModel('godam/godam');
		$orders = $this->getRequest()->getParam('godam');
		$token = Mage::getStoreConfig('godam/godam/api_token');
		$clientstore = urlencode(Mage::getStoreConfig('godam/godam/client_store'));
		$clientfc =  urlencode(Mage::getStoreConfig('godam/godam/client_fc'));
		$supplierid =  urlencode(Mage::getStoreConfig('godam/godam/supplier_id'));
		$url =  Mage::getStoreConfig('godam/godam/api_url');
		$apiversion =  urlencode(Mage::getStoreConfig('godam/godam/api_version'));
		$invoiced = Mage::getStoreConfig('godam/godamorder/invoiced');
		//echo $clientstore;die;
		if($clientstore && $token && $url)
		{
		//$token = "$token"; // replace this with your token key
		$url .= "oms/api/create/?client_store=".$clientstore."&fc=".$clientfc."&version=".$apiversion;
		$succsscount = 0;
		$failcount = 0;
		$msg = '';
		foreach ($orders as $gorder) {
				
				$model = Mage::getModel('godam/godam')->load($gorder);
				$order = Mage::getModel('sales/order')->load($model->orderid);
				  if ($invoiced == 1 && !$order->hasInvoices()) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('godam')->__("$model->orderincid | $model->suborderid : Configurations Invoice Flag is Yes so please raise the invoice before submitting this order to Godam."));
                    $this->_redirectReferer();
                    return $this;
                }
                //if($model->status != "shipped"): 
				if (!$model->godamstatus || in_array(strtolower($model->godamstatus),array('pending','fulfillable','failed'))): // Submit only if status of Godam is pending or fulfillable
				$address = Mage::getModel('sales/order_address')->load($order->shipping_address_id);				 
				//$products = $order->getAllItems();
				$params = array(); // this will contain request meta and the package feed
				$package_data = Mage::getModel('godam/godam')->getPackageData($model->orderid,$model->suborderid);
				$params['data'] = '['.json_encode($package_data).']';
				
				$result = Mage::helper('godam')->Executecurl($url,'post',$params);
				if($result)
				{
				mage::log($result);
				$result = (array) json_decode($result);
				if($result['Request Id']):
					$model->setData('request_id',$result['Request Id']);
					$model->setData('godamstate','2')->save();
					$msg .= "$model->orderincid|$model->suborderid  Submitted Successfully<br />";
					$succsscount++;	
				else:
					$msg .= "$model->orderincid|$model->suborderid Failed to submit. Remark: ".$result->Message."<br />";
					$failcount++;
				endif;
				}
				else
				{
					$msg .= "Request timeout for $model->orderincid|$model->suborderid<br />";
					$failcount++;
				}
				else:
					$msg .= "$model->orderincid|$model->suborderid already submitted<br />";
				endif;
        }
        $msg .= "<br />$succsscount orders submited successfully. $failcount orders Failed";
		mage::log($msg);
        Mage::getSingleton('adminhtml/session')->addSuccess($msg);
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('godam')->__('Please add valid Token, Client Store Name and Gateway URL in module configuration'));
		}		
		$this->_redirect('*/*/index');
	}
}
