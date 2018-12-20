<?php
class EH_ProductScheduler_IndexController extends Mage_Core_Controller_Front_Action{	
	public function indexAction(){
	
		$today = date('Y-m-d 00:00:00');
		$yesterday = date('Y-m-d 00:00:00', strtotime('-1 day'));
		
		$collection = Mage::getModel('catalog/product')->getCollection();
		$collection->addAttributeToSelect(array('eh_schedule_start_date', 'eh_schedule_end_date', 'eh_schedule_status'));
		$collection->addFieldToFilter(array(
				array('attribute'=>'eh_schedule_start_date','eq'=>$today),
				array('attribute'=>'eh_schedule_end_date','eq'=>$yesterday)
		));
		
		foreach ($collection as $product) {	
			if($product->getEhScheduleStatus()){
				$productId = $product->getId();
				$productCurrentStatus = $product->getStatus();
				
				$_product = Mage::getModel('catalog/product')->load($productId);
				
				$EH_scheduleStatusText = $_product->getAttributeText('eh_schedule_status');
				$EH_schedulestartStatus = 1;
				$EH_scheduleendStatus = 2;
				if($EH_scheduleStatusText=='Disable'){ 
					$EH_schedulestartStatus = 2; 
					$EH_scheduleendStatus = 1; 
				}
				if($product->getEhScheduleStartDate() == $today) {
						$this->_updateProStatus($EH_schedulestartStatus,$productId);
				} elseif($product->getEhScheduleEndDate() == $yesterday) {
						$this->_updateProStatus($EH_scheduleendStatus,$productId);
				}
			}
		}
	}
	
	private function _updateProStatus($EH_scheduleStatus,$productId){
		if($EH_scheduleStatus == 1) {
		echo 'if';
			Mage::getModel('catalog/product_status')->updateProductStatus($productId, 0, Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
		} else {
		echo 'else';
			Mage::getModel('catalog/product_status')->updateProductStatus($productId, 0, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
		}
	}
}
