<?php
class IWD_Opc_Model_Observer{
	
	public function checkRequiredModules($observer){
		$cache = Mage::app()->getCache();
		
		if (Mage::getSingleton('admin/session')->isLoggedIn()) {
			if (!Mage::getConfig()->getModuleConfig('IWD_All')->is('active', 'true')){
				if ($cache->load("iwd_opc")===false){
					$message = 'Important: Please setup IWD_ALL in order to finish <strong>IWD One Page Checkout</strong> installation.<br />
						Please download <a href="http://iwdextensions.com/media/modules/iwd_all.tgz" target="_blank">IWD_ALL</a> and setup it via Magento Connect.';
				
					Mage::getSingleton('adminhtml/session')->addNotice($message);
					$cache->save('true', 'iwd_opc', array("iwd_opc"), $lifeTime=5);
				}
			}
		}
	}
	
	
	
	public function newsletter($observer){
		$_session = Mage::getSingleton('core/session');

		$newsletterFlag = $_session->getIsSubscribed();
		if ($newsletterFlag==true){
			
			$email = $observer->getEvent()->getOrder()->getCustomerEmail();
			
			$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
	        if($subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED && $subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED) {
	            $subscriber->setImportMode(true)->subscribe($email);
	            
	            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
	            $subscriber->sendConfirmationSuccessEmail();
	        }
			
		}
		
	}
	
	public function applyComment($observer){
		$order = $observer->getData('order');
		
		$comment = Mage::getSingleton('core/session')->getOpcOrderComment();
		if (!Mage::helper('opc')->isShowComment() || empty($comment)){
			return;
		}
		try{
			$order->setCustomerComment($comment);
			$order->setCustomerNoteNotify(true);
			$order->setCustomerNote($comment);
			$order->addStatusHistoryComment($comment)->setIsVisibleOnFront(true)->setIsCustomerNotified(true);
			$order->save();
			$order->sendOrderUpdateEmail(true, $comment);
		}catch(Exception $e){
			Mage::logException($e);
		}
	}
	public function changeSubtotal($observer)
	   {

		$quote=$observer->getEvent()->getQuote();
		$quoteid=$quote->getId();
			$address = $quote->getShippingAddress();
    if ($address) {
        $total = $address->getTaxAmount();
    }
		$discountAmount=$total;
		if($quoteid) {
        if($discountAmount>0) {
	   $total=$quote->getBaseSubtotal();
	   $quote->setSubtotal(0);
	   $quote->setBaseSubtotal(0);

	   $quote->setSubtotalWithDiscount(0);
	   $quote->setBaseSubtotalWithDiscount(0);

	   $quote->setGrandTotal(0);
	   $quote->setBaseGrandTotal(0);
  
    
   $canAddItems = $quote->isVirtual()? ('billing') : ('shipping'); 
   foreach ($quote->getAllAddresses() as $address) {
    
   $address->setSubtotal(0);
            $address->setBaseSubtotal(0);

            $address->setGrandTotal(0);
            $address->setBaseGrandTotal(0);

            $address->collectTotals();

            $quote->setSubtotal((float) $quote->getSubtotal() + $address->getSubtotal());
            $quote->setBaseSubtotal((float) $quote->getBaseSubtotal() + $address->getBaseSubtotal());

            $quote->setSubtotalWithDiscount(
                (float) $quote->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount()
            );
            $quote->setBaseSubtotalWithDiscount(
                (float) $quote->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount()
            );

            $quote->setGrandTotal((float) $quote->getGrandTotal() + $address->getGrandTotal());
            $quote->setBaseGrandTotal((float) $quote->getBaseGrandTotal() + $address->getBaseGrandTotal());
 
   $quote ->save(); 
 
      $quote->setGrandTotal($quote->getBaseSubtotal())
      ->setBaseGrandTotal($quote->getBaseSubtotal())
      ->setSubtotalWithDiscount($quote->getBaseSubtotal())
      ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal())
      ->save(); 
      
    
    if($address->getAddressType()==$canAddItems) {
    //echo $address->setDiscountAmount; exit;
     $address->setSubtotalWithDiscount((float) $address->getSubtotalWithDiscount()-$discountAmount);
     $address->setGrandTotal((float) $address->getGrandTotal()-$discountAmount);
     $address->setBaseSubtotalWithDiscount((float) $address->getBaseSubtotalWithDiscount()-$discountAmount);
     $address->setBaseGrandTotal((float) $address->getBaseGrandTotal()-$discountAmount);
     if($address->getDiscountDescription()){
     //$address->setDiscountAmount(-($address->getDiscountAmount()-$discountAmount));
     //$address->setDiscountDescription($address->getDiscountDescription().', Custom Discount');
     //$address->setBaseDiscountAmount(-($address->getBaseDiscountAmount()-$discountAmount));
     }else {
    // $address->setDiscountAmount(-($discountAmount));
     //$address->setDiscountDescription('Custom Discount');
//address->setBaseDiscountAmount(-($discountAmount));
     }
     $address->save();
    }//end: if
   } //end: foreach
   //echo $quote->getGrandTotal();
  
  foreach($quote->getAllItems() as $item){
                 //We apply discount amount based on the ratio between the GrandTotal and the RowTotal
                 $rat=$item->getPriceInclTax()/$total;
                 $ratdisc=$discountAmount*$rat;
               //  $item->setDiscountAmount(($item->getDiscountAmount()+$ratdisc) * $item->getQty());
               //  $item->setBaseDiscountAmount(($item->getBaseDiscountAmount()+$ratdisc) * $item->getQty())->save();
                
               }
            
                
            }
            

	   }
    
}

}
