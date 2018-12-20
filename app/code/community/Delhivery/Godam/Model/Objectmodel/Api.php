<?php
class Delhivery_Godam_Model_Objectmodel_Api extends Mage_Api_Model_Resource_Abstract
{
     /**
     * method Name
     *
     * @param string $orderIncrementId
     * @return string
     */
    public function receiveUpdate($type)
    { 
        Mage::log("Delhivery_Godam_Model_Objectmodel_Api: receiveUpdate called");
		mage::log($type);
		$params = explode("|",$type);
		
		mage::log($params);
		//mage::log($status);	
		//$result= "API Called Successfully";	
		$model = Mage::getModel('godam/godam');
		$result = $type;
		if($params[0] == 'ordercreate')
		$result = $model->changeOrderStatus($params[1], $params[2]);
		else
		$result = $model->updateFromGodam($params[1]);			
        return $result;
    }
	public function inventoryUpdate($params)
	{
		mage::log($params);
		$response=array();
		$finalresponse=array();
		$param=json_decode($params);
		$date=Mage::getModel('core/date')->date('Y-m-d H:i:s');
		foreach($param as $info)
		{
			if($info->sku){
				$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$info->sku);
				if($product)
				{
					$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
					//$qty=$stockItem->getData('qty');
					$totalqty=$info->qty;
					$stockItem->setData('is_in_stock',$totalqty ? 1 : 0);
					$stockItem->setData('qty',$totalqty);
					if($stockItem->save())
					{
						$log=Mage::getModel('godam/inventorylog');
						$log->setSku($info->sku);
						$log->setQty($info->qty);
						$log->setCreatedTime($date);
						$log->setUpdateTime($date);
						$log->save();
						$response['status']='success';
						$response['message']='SKU "'.$info->sku.'" has updated';
					}
					else
					{
						$response['status']='fail';
						$response['message']='SKU "'.$info->sku.'" has been not updated';
					}
				}
				else
				{
					$response['status']='fail';
					$response['message']='product not found with SKU "'.$info->sku.'"';
				}
			}
			else
				{
					$response['status']='fail';
					$response['message']='SKU "'.$info->sku.'" has been not updated';
				}
				$finalresponse[]=$response;
		}
		mage::log(json_encode($finalresponse));
		return json_encode($finalresponse);
	}
}
?>