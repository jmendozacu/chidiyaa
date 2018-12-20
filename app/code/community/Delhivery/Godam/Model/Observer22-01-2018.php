<?php

/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Godam
 * @copyright  Copyright (c) 2014 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 */
class Delhivery_Godam_Model_Observer {

    /**
     * Function to call Godam API to post order details
     */
    public function sales_order_place_after($observer) {
        mage::log("Observer::sales_order_place_after Called");
        $order = $observer->getEvent()->getOrder();

        $autosubmit = Mage::getStoreConfig('godam/godamorder/autosubmit');
        $status_to_autosubmit = Mage::getStoreConfig('godam/godamorder/orderstatus');
        $invoiced = Mage::getStoreConfig('godam/godamorder/invoiced');

        mage::log("Invoiced  = " . $invoiced);
        mage::log("status_to_autosubmit  = " . $status_to_autosubmit);
        mage::log("autosubmit  = " . $autosubmit);


        if ($invoiced == 1 && !$order->hasInvoices()) {
            //Mage::getConfig()->saveConfig('godam/godamorder/autosubmit', false);
            $model = Mage::getModel('godam/godam');
           // $ordered_items = $order->getAllItems();
            $ordered_items = $order->getAllVisibleItems(); //changed becuase of issue with configurable items
            foreach ($ordered_items as $item) {
                $data = array();
                $data['orderid'] = $order->getId();
                $data['orderincid'] = $order->getIncrementId();
                $data['suborderid'] = $item->getItemId() . '-' . $item->getSku();
                $data['godamstate'] = 1;
                $model->setData($data)->save();
            }
        } else {
            $model = Mage::getModel('godam/godam');
            //$ordered_items = $order->getAllItems(); 
            $ordered_items = $order->getAllVisibleItems(); //changed becuase of issue with configurable items 
            foreach ($ordered_items as $item) {
                $data = array();
				mage::log($item->getItemId() . '-' . $item->getSku());
				mage::log('$order->getId() = '.$order->getId());
				mage::log('$order->getIncrementId() = '.$order->getIncrementId());
                $data['orderid'] = $order->getId();
                $data['orderincid'] = $order->getIncrementId();
                $data['suborderid'] = $item->getItemId() . '-' . $item->getSku();
                $data['godamstate'] = 1;
                $model->setData($data)->save();
                if ($autosubmit && $order->getStatus() == $status_to_autosubmit) {
                    mage::log("Sending auto submit to Godam for $order->getId() | $model->getSuborderid()");
					$model->submitGodam($order->getId(), $model->getSuborderid(), $model->getGodamId());
                }
            }
        }
        return;
    }

    /**
     * Function to call Godam API to post order details
     */
    public function sales_order_item_cancel($observer) {
        mage::log("Observer::sales_order_item_cancel called");
		$model = Mage::getModel('godam/godam');
		$item = $observer->getEvent()->getItem();
		$order = Mage::getModel("sales/order")->load($item->getOrderId()); 
		mage::log('$order->status   = '. $order->status);
		mage::log('$order->getIncrementId()   = '. $order->getIncrementId());
		//if ($item->getStatusId() !== 2) {
		$orderdata = $model->loadByOrderId($order->getIncrementId());
		mage::log('$orderdata->status   = '. $orderdata->status);
        if ($orderdata->getStatus() == "shipped") {
            Mage::getSingleton("adminhtml/session")->addError("Order: " . $item->getOrder()->getIncrementId() . " : You can't cancel the order which is already Shipped.!!");
            $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : Mage::getUrl();
            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
            Mage::app()->getResponse()->sendResponse();
            exit;
        }
        return;
    }

    /**
     * This method observes the salesOrderInvoiceSaveAfter event
     * 
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderInvoiceSaveAfter($observer) {
        mage::log("Observer::salesOrderInvoiceSaveAfter called");
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();

		$autosubmit = Mage::getStoreConfig('godam/godamorder/autosubmit');
		$invoiced = Mage::getStoreConfig('godam/godamorder/invoiced');

		if($autosubmit && $invoiced == 1){
			$status_to_autosubmit = Mage::getStoreConfig('godam/godamorder/orderstatus');
			$ordered_items = $order->getAllItems();
                        

			foreach ($ordered_items as $item) {
				$data = array();
				$data['orderid'] = $order->getId();
				$data['orderincid'] = $order->getIncrementId();
				$data['suborderid'] = $item->getItemId() . '-' . $item->getSku();
				$data['godamstate'] = 1;

				$orderId = $order->getIncrementId();
				$suborder = $item->getItemId() . '-' . $item->getSku();
				$custommodel = Mage::getModel('godam/godam')->loadByOrderSuborderId($orderId, $suborder);

				$godamId = $custommodel->getGodamId();
				mage::log('$godamId  = ' . $godamId);

				$model = Mage::getModel('godam/godam')->load($godamId)->addData($data);
				$model->submitGodam($order->getId(), $model->getSuborderid(), $godamId);
			}
		}

		return;
	}

}
