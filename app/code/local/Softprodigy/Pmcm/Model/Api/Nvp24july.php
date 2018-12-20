<?php 

class Softprodigy_Pmcm_Model_Api_Nvp extends Mage_Paypal_Model_Api_Nvp{
	
	public function callSetExpressCheckout()
    {
		$this->_prepareExpressCheckoutCallRequest($this->_setExpressCheckoutRequest);
        $request = $this->_exportToRequest($this->_setExpressCheckoutRequest);
        $this->_exportLineItems($request);

        // import/suppress shipping address, if any
        $options = $this->getShippingOptions();
        if ($this->getAddress()) {
            $request = $this->_importAddresses($request);
            $request['ADDROVERRIDE'] = 1;
        } elseif ($options && (count($options) <= 10)) { // doesn't support more than 10 shipping options
            $request['CALLBACK'] = $this->getShippingOptionsCallbackUrl();
            $request['CALLBACKTIMEOUT'] = 6; // max value
            $request['MAXAMT'] = $request['AMT'] + 999.00; // it is impossible to calculate max amount
            $this->_exportShippingOptions($request);
        }

        // add recurring profiles information
        $i = 0;
        foreach ($this->_recurringPaymentProfiles as $profile) {
            $request["L_BILLINGTYPE{$i}"] = 'RecurringPayments';
            $request["L_BILLINGAGREEMENTDESCRIPTION{$i}"] = $profile->getScheduleDescription();
            $i++;
        }
       
       // var_dump($request); die;
       $this->prepareRequest($request);
        $response = $this->call(self::SET_EXPRESS_CHECKOUT, $request);
        $this->_importFromResponse($this->_setExpressCheckoutResponse, $response);
    }
      public function callDoExpressCheckoutPayment()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_doExpressCheckoutPaymentRequest);
        $request = $this->_exportToRequest($this->_doExpressCheckoutPaymentRequest);
        $this->_exportLineItems($request);
		/* rounded price and converted to usd doller,Also changed currency */
		$request['AMT'] = round(Mage::helper('pmcm')->getExchangeRate($request['AMT']),2);
                
		$request['CURRENCYCODE'] = Mage::helper('pmcm')->getToCurrency();
		$this->prepareRequest($request);
		$response = $this->call(self::DO_EXPRESS_CHECKOUT_PAYMENT, $request);
        $this->_importFromResponse($this->_paymentInformationResponse, $response);
        $this->_importFromResponse($this->_doExpressCheckoutPaymentResponse, $response);
        $this->_importFromResponse($this->_createBillingAgreementResponse, $response);
    }

    public function prepareRequest(&$request){
       $sum = 0;
       
       foreach($request as $ky=>$val){
       	 if(strpos( $ky, 'L_AMT')!==false){
       	 	$v = (float)$val;
       	 	$inx = (int)str_replace('L_AMT','', $ky);
       	 	$qty = (float)$request['L_QTY'.$inx ];
       	 	$sum += $v*$qty;
       	 }
       }
       $request['ITEMAMT'] = round($sum,2);
       return $this;
    }
    
   
	
}
