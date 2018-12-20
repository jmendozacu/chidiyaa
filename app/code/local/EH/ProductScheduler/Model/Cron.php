<?php
class EH_ProductScheduler_Model_Cron{	
	public function Cron(){
			$collection = Mage::getModel('catalog/product')->getCollection();
			$today = date('Y-m-d 00:00:00');
			$yesterday = date('Y-m-d 00:00:00', strtotime('-1 day'));
			$collection->addAttributeToSelect(array('eh_schedule_start_date', 'eh_schedule_end_date', 'eh_schedule_status'));
			$collection->addFieldToFilter(array(
					array('attribute'=>'eh_schedule_start_date','eq'=>$today),
					array('attribute'=>'eh_schedule_end_date','eq'=>$yesterday)
			));
			foreach ($collection as $product) {	
				if($product->getEhScheduleStatus()){
					$productId = $product->getId();
					$EH_scheduleStatusText = $product->getAttributeText('eh_schedule_status');
					$EH_schedulestartStatus = 1;
					$EH_scheduleendStatus = 2;
					if($EH_scheduleStatusText=='Disable'){ $EH_schedulestartStatus = 2; $EH_scheduleendStatus = 1; }
					if($product->getEhScheduleStartDate() == $today) {
						if($EH_schedulestartStatus == 1) {
							Mage::getModel('catalog/product_status')->updateProductStatus($productId, 0, Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
						} else {
							Mage::getModel('catalog/product_status')->updateProductStatus($productId, 0, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
						}
					} elseif($product->getEhScheduleEndDate() == $yesterday) {
						if($EH_scheduleendStatus == 1) {
							Mage::getModel('catalog/product_status')->updateProductStatus($productId, 0, Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
						} else {
							Mage::getModel('catalog/product_status')->updateProductStatus($productId, 0, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
						}
					}
				}
			}
	}
}
