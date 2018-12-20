<?php
class EH_ProductScheduler_Model_Observer
{

			public function Observer(Varien_Event_Observer $observer)
			{
				//if($this->_chkSchedule($observer->getProduct())){
				//	$EH_scheduleStatusText = $observer->getProduct()->getAttributeText('eh_schedule_status');
				//	$EH_scheduleStatus = 1;
				//	if($EH_scheduleStatusText=='Disable'){ $EH_scheduleStatus = 2; }
					//$observer->getProduct()->setStatus($EH_scheduleStatus);
				//}
			}
			
			private function _chkSchedule($product){
				$currentDate = strtotime('+1 day');
				if($product->getEhScheduleStartDate())
				{
					$EH_scheduleStartDate = strtotime($product->getEhScheduleStartDate());
				}
				if($product->getEhScheduleEndDate())
				{
					$EH_scheduleEndDate = strtotime($product->getEhScheduleEndDate());
				}
				if(isset($EH_scheduleStartDate) && isset($EH_scheduleEndDate))
				{
					if($EH_scheduleStartDate <= $currentDate && $EH_scheduleEndDate >= $currentDate){
						return true;
					}
				}
				elseif(isset($EH_scheduleStartDate))
				{
					if($EH_scheduleStartDate <= $currentDate){
						return true;
					}
				}
				elseif(isset($EH_scheduleEndDate))
				{
					if($EH_scheduleEndDate >= $currentDate){
						return true;
					}
				}
				return false;
			}
}
