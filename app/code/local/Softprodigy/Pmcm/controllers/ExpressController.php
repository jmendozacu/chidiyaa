<?php 
require_once(Mage::getModuleDir('controllers','Mage_Paypal').DS.'ExpressController.php');

class Softprodigy_Pmcm_ExpressController extends Mage_Paypal_ExpressController
{
	public function startAction()
    {
        try {
            $this->_initCheckout();

            if ($this->_getQuote()->getIsMultiShipping()) {
                $this->_getQuote()->setIsMultiShipping(false);
                $this->_getQuote()->removeAllAddresses();
            }

            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $quoteCheckoutMethod = $this->_getQuote()->getCheckoutMethod();
            if ($customer && $customer->getId()) {
                $this->_checkout->setCustomerWithAddressChange(
                    $customer, $this->_getQuote()->getBillingAddress(), $this->_getQuote()->getShippingAddress()
                );
            } elseif ((!$quoteCheckoutMethod
                || $quoteCheckoutMethod != Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER)
                && !Mage::helper('checkout')->isAllowedGuestCheckout(
                $this->_getQuote(),
                $this->_getQuote()->getStoreId()
            )) {
                Mage::getSingleton('core/session')->addNotice(
                    Mage::helper('paypal')->__('To proceed to Checkout, please log in using your email address.')
                );
                $this->redirectLogin();
                Mage::getSingleton('customer/session')
                    ->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current' => true)));
                return;
            }

            // billing agreement
            $isBARequested = (bool)$this->getRequest()
                ->getParam(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
            if ($customer && $customer->getId()) {
                $this->_checkout->setIsBillingAgreementRequested($isBARequested);
            }
			
            // Bill Me Later
            $this->_checkout->setIsBml((bool)$this->getRequest()->getParam('bml'));

            // giropay
            $this->_checkout->prepareGiropayUrls(
                Mage::getUrl('checkout/onepage/success'),
                Mage::getUrl('paypal/express/cancel'),
                Mage::getUrl('checkout/onepage/success')
            );
			 
			//$this->setPaymentInfo($this->_getQuote());
			 
            $button = (bool)$this->getRequest()->getParam(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_BUTTON);
            $token = $this->_checkout->start(Mage::getUrl('*/*/return'), Mage::getUrl('*/*/cancel'), $button);
           
            if ($token && $url = $this->_checkout->getRedirectUrl()) {
			    $this->_initToken($token);
                $this->getResponse()->setRedirect($url);
                return;
            }
           
        } catch (Mage_Core_Exception $e) {
			 $this->_getCheckoutSession()->addError($e->getMessage());
            
        } catch (Exception $e) {
            $this->_getCheckoutSession()->addError($this->__('Unable to start Express Checkout.'));
            Mage::logException($e);
        }

        $this->_redirect('checkout/cart');
    }
     private function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }
    protected function _initCheckout()
    {
        $quote = $this->_getQuote();
        //$this->setPaymentInfo($quote);
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
            Mage::throwException(Mage::helper('paypal')->__('Unable to initialize Express Checkout.'));
        }
        
        $this->_checkout = Mage::getSingleton($this->_checkoutType, array(
            'config' => $this->_config,
            'quote'  => $quote,
        ));
		return $this->_checkout;
    }
    public function setPaymentInfo($quote)
    {
        $order = $quote; //$observer->getOrder();
        $payment = $order->getPayment();
        $code = $payment->getMethod();
        if (in_array($code, array('paypal_express'))) {
            $payment->setAdditionalInformation('payment_currency', Mage::helper('pmcm')->getToCurrency());
            $payment->setAdditionalInformation('due_amount', Mage::helper('pmcm')->convertAmount($order->getBaseGrandTotal()));
            $payment->setAdditionalInformation('exchange_rate', Mage::helper('pmcm')->getCurrentExchangeRate());
        }
        $payment->save();
    }

}
