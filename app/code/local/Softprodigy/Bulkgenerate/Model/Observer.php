<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Observer
 *
 * @author root
 */
class Softprodigy_Bulkgenerate_Model_Observer  {
	public function addbutton($observer) {
			// echo "here";exit;
		if ($observer->getEvent()->getBlock() instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction ||
                $observer->getEvent()->getBlock() instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction) {
			if ($observer->getEvent()->getBlock()->getRequest()->getControllerName() == 'sales_order') {
				// echo "here";exit;
				$observer->getEvent()->getBlock()->addItem('generate_bulk_invoice', array('label' => Mage::helper('bulkgenerate')->__('Generate Invoice'),'url' => Mage::helper('adminhtml')->getUrl('bulkgenerate/adminhtml_invoice/generate', Mage::app()->getStore()->isCurrentlySecure() ? array('_secure' => 1) : array()),));
            }
		}
	}
}