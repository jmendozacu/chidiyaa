<?php
class Softprodigy_Message_Adminhtml_ReturnproductsController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction(){
		 $this->loadLayout();
		 $this->renderLayout();
    } 
	public function indexAction(){
		$this->_initAction();
		 
	}
	public function editAction()
    {
       $customerID   =   $this->getRequest()->getParam('id');
       
       if ($customerID!='') {
			//$this->_setActiveMenu('rentalorder/items');
              $this->loadLayout();
             $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Order To Receive'), Mage::helper('adminhtml')->__('Order To Receive'));
             $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Receivable Info'), Mage::helper('adminhtml')->__('Receivable Info'));
             $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
             $this->_addContent($this->getLayout()->createBlock('message/adminhtml_return_products_edit'))
                ->_addLeft($this->getLayout()->createBlock('message/adminhtml_return_products_edit_tabs'));
             $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('message')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }
    public function barcodeGenerationAction(){
		
		//~ require_once(Mage::getBaseDir('lib') . '/barcode/BCGFontFile.php');
		//~ die;
		//~ require_once(Mage::getBaseDir('lib') . '/barcode/BCGColor.php');
		//~ require_once(Mage::getBaseDir('lib') . '/barcode/BCGDrawing.php');
		//~ require_once(Mage::getBaseDir('lib') . '/barcode/BCGcode128.barcode.php');
		//~ die;
	  //~ 
		//~ $color_black = new BCGColor(0, 0, 0);
		//~ $color_white = new BCGColor(255, 255, 255);
		 //~ 
		//~ $code = new BCGcode128();
        //~ $code->setScale(2);  
        //~ $code->setThickness(30);  
        //~ $code->setForegroundColor($color_black);  
        //~ $code->setBackgroundColor($color_white); 
        //~ $code->setFont($font);  
        //~ $code->parse('hi');  
        //~ 
	    //~ $drawing = new BCGDrawing('../../media/US_flag_2.png',$color_white);
		//~ $drawing->setBarcode($code);
		//~ $drawing->draw();
		//~ $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
	}
	public function updatedaysAction(){
		$order_id = $this->getRequest()->getParam('order_id');
		$value =  $this->getRequest()->getParam('value');
		$due_date  = time();
		$due_date =  date('Y-m-d H:i:s', strtotime('+'.$value.' day ', $due_date));
		$model=Mage::getModel('payperrentals/sendreturn')->getCollection();
		$model->addFieldToFilter('order_id', array('eq' =>$order_id)); 
		$id= $model->getFirstItem()->getId();
		if(isset($id) && $id!=''){
			foreach($model->getItems() as $item){
				Mage::getModel('payperrentals/sendreturn')
				->setReturnDate($due_date)
				 ->setId($item->getId())
				->save();
			}
			$result=array();
			$result['error']='false';
			$result['return_date']=$due_date;
		    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result)); 
		}
		
	}
	public function getReceiveDataAction(){
		$data = $this->getRequest()->getParam('data');
		if(isset($data) && $data!=''){
			$expl = explode('_',$data);
			$orderinc_id = $expl[0];
			$item_id = $expl[1];
			if(isset($orderinc_id) && isset($item_id)){
			   $order = Mage::getModel('sales/order')->loadByIncrementId($orderinc_id);
			  
		       $collection = Mage::getModel('sales/order_item')->getCollection();
			   $collection->addFieldToFilter('order_id',$order->getId())
				 ->addFieldToFilter('item_id',$item_id);
				$chek_item_id = $collection->getFirstItem()->getId();
				$chek_item_name = $collection->getFirstItem()->getName();
				if(isset($chek_item_id) && $chek_item_id!=''){
					$result['error'] ='false';
					$result['order_id'] =$order->getId();
					$result['order_increment_id'] =$orderinc_id;
					$result['order_item_id'] =$chek_item_id;
					$result['order_item_name'] =$chek_item_name;
				    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
					
				}
			}
			 
			 
		}
		
	}
	public function getReceiveData1Action(){
		$orderinc_id = $this->getRequest()->getParam('data');
		if(isset($orderinc_id) && $orderinc_id!=''){
			  $order = Mage::getModel('sales/order')->loadByIncrementId($orderinc_id);
			   $collection = Mage::getModel('sales/order_item')->getCollection();
			   $collection->addFieldToFilter('order_id',$order->getId());
			   $items= $collection->getData();
			   $i=0;
			  foreach($collection as $items){
				  $itemdata[$i]['order_item_id']=  $items->getItemId();
				  $itemdata[$i]['order_id']=  $items->getOrderId();
				  $itemdata[$i]['item_name']=  $items->getName();
				   $itemdata[$i]['order_increment_id']=  $orderinc_id;
				  $i++;
			  }
				
		 	 echo json_encode(array('error'=>false,'data'=>$itemdata));
		}else{
			 echo json_encode(array('error'=>true,'data'=>'record empty'));
		}
		
	}
	public function savereceiveItemsAction(){
		$data = array();
		$data = $this->getRequest()->getPost('receive');
		 //~ echo "<pre>";
		//~ print_r($data);
		//~ echo "</prepre>";
		
		 //$model = Mage::getModel('rentalorder/receiveproducts');
		$already = array();
		$truecoutner= array();
		$falseCounter = array();
		$defectedItems = array();
		foreach($data as $oid=>$val){
			foreach($val as $itemid => $vdata){
				if(isset($vdata['is_return']) && !empty($vdata['is_return'])){
					
					$collection =  Mage::getModel('rentalorder/receiveproducts')->getCollection()
								   ->addFieldToFilter('order_id',$oid)
								  ->addFieldToFilter('item_id',$itemid);
					echo $order_id = $collection->getFirstItem()->getOrderId();
				   if(!$order_id){
						$model = Mage::getModel('rentalorder/receiveproducts');
						$model->setOrderId($oid);
						$model->setItemId($itemid);
						$model->setCondition($vdata['condition']); 
						$model->setComment($vdata['comment']);
						$model->save();
						$model->clearInstance();
						if($vdata['condition']=='defected'){
							
						    $defectedItems[$vdata['increment_id']][$itemid]['comment'] = $vdata['comment'];
							$defectedItems[$vdata['increment_id']][$itemid]['condition'] = $vdata['condition'];
						} 
					}else{
						   $already[] = $vdata['increment_id']." --> Item Id #".$itemid;
						
					}
					$truecoutner[] = $itemid; 
				}else{
					$falseCounter[] = $itemid;
				}
				
			    
			
			} 
			//die();
		}
		
		if(count($defectedItems)>0){
			
							$emailTemplate = Mage::getModel('core/email_template')->loadDefault('receiveorder_defecteditem_email_template');
							$varObj = new Varien_Object();
							
							
							$varObj->setData($defectedItems);
							$emailTemplateVariables['data'] = $varObj;
							$senderName = Mage::getStoreConfig('trans_email/ident_general/name');
							$senderEmail = Mage::getStoreConfig('trans_email/ident_general/email');
							$processedTemplate=$emailTemplate->getProcessedTemplate($emailTemplateVariables);
							$mail= Mage::getModel('core/email')
									 ->setToName($senderName)
									 ->setToEmail('pardeep_grover@softprodigy.com') 
									 ->setBody($processedTemplate)
									 ->setSubject('Subject :Defected Details')
									 ->setFromEmail($senderEmail)
									 ->setFromName('Shiiped')
									 ->setType('html');	
									 if($mail){
									 }		
							
		}
		if(count($falseCounter)>0){
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('message')->__("Item(s)  #".implode(', #',$falseCounter) .' not selected'));
				$this->_redirect('*/*/edit', array('_current'=>true));
				return;
			}
			//echo $vdata['increment_id'];
		/*	$order = Mage::getModel('sales/order')->loadByIncrementId($vdata['increment_id']);
			$items = $order->getAllVisibleItems();
			foreach($items as $i):
			echo '/'.	$i->getProductId();
			endforeach;
			die();*/
			
		 
		  if(count($already)>0){
			  $this->_adderror($already);
		   }else{
			   Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mess')->__('Order Received')); 
			   Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rentalorder')->__("Item(s)  #".implode(', #',$truecoutner) .' Received')); 
			  
			   $this->_redirect('*/*/edit', array('_current'=>true));
		   }
		 
		 
	}
	private function _adderror($already){
		$string = implode(',',$already);
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rentalorder')->__("Order ID #".$string .'  already received'));
		$this->_redirect('*/*/edit', array('_current'=>true));
	}
	public function massStatusAction() {
		   if( $this->getRequest()->getParam('item_ids')) {
			  
			 try {
                $incr_ids = $this->getorderIdsByItemIds($this->getRequest()->getParam('item_ids'));
                if(isset($incr_ids) && count($incr_ids)>0){
				    foreach($incr_ids as $inc_id){
					    $colle = Mage::getModel('payperrentals/sendreturn')->getCollection();
						$colle->addFieldToFilter('order_id',$inc_id);
						foreach($colle->getItems() as $_items){
								$_items->setId($_items->getId());
								$_items->setChkStatus($this->getRequest()->getParam('status'));
								$_items->save();
						}
						
					    
					}
					 Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rentalorder')->__('Order status Updated')); 
			  
					$this->_redirect('*/*/');
				}
				 
            } catch (Exception $e) {
				Mage::logException($e);
            }
        }
	}
	private function getorderIdsByItemIds($itemIds){
		
		$conn= Mage::getSingleton("core/resource");
		$collection = Mage::getModel('sales/order')->getCollection();
		$collection->addAttributeToSelect('increment_id');
		
		$collection->getSelect()->join(array('orit'=>$conn->getTableName('sales/order_item')),'orit.order_id=main_table.entity_id');
		
		$collection->addFieldToFilter('orit.item_id',array('in'=>$itemIds));
		
		 $return = $collection->getColumnValues('increment_id');
		 return $return;
	}
    
}

?>
 
