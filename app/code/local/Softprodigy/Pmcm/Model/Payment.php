<?php
/**
 * Softprodigy System Solutions Pvt. Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.idealiagroup.com/magento-ext-license.html
 *
 * @category    Softprodigy
 * @package     Softprodigy_Pmcm
 * @copyright   Copyright (c) 2015 Softprodigy System Solutions Pvt. Ltd (http://www.softprodigy.com)
 * @license    http://www.opensource.org/licenses/gpl-license.php  GNU General Public License
 */
class Softprodigy_Pmcm_Model_Payment extends Mage_Sales_Model_Order_Payment
{

    public function registerCaptureNotification($amount, $skipFraudDetection = false)
    {
        $this->_generateTransactionId(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
            $this->getAuthorizationTransaction()
        );

        $order = $this->getOrder();
        $amount = (float)$amount;
        //DD
        $currency = Mage::getModel('directory/currency')->load(Mage::helper('pmcm')->getToCurrency());
        Mage::log('Currency:' . $currency->getCurrencyCode());
        Mage::log($currency->getCurrencyCode());
        Mage::log('amount:' . strval($amount));
        Mage::log('grand total:' . strval((float)$order->getBaseGrandTotal()));
        if ($currency->getCurrencyCode() != Mage::app()->getStore()->getBaseCurrencyCode()) {
            $amount = (float)$order->getBaseGrandTotal();
        }

        //--DD
        $invoice = $this->_getInvoiceForTransactionId($this->getTransactionId());

        // register new capture
        if (!$invoice) {
            if ($this->_isCaptureFinal($amount)) {
                $invoice = $order->prepareInvoice()->register();
                $order->addRelatedObject($invoice);
                $this->setCreatedInvoice($invoice);
            } else {
                if (!$skipFraudDetection) {
                    $this->setIsFraudDetected(true);
                }
                $this->_updateTotals(array('base_amount_paid_online' => $amount));
            }
        }

        $status = true;
        if ($this->getIsTransactionPending()) {
            $message = Mage::helper('sales')->__('Capturing amount of %s is pending approval on gateway.', $this->_formatPrice($amount));
            $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
            if ($this->getIsFraudDetected()) {
                $message = Mage::helper('sales')->__('Order is suspended as its capture amount %s is suspected to be fraudulent.', $this->_formatPrice($amount));
                $status = Mage_Sales_Model_Order::STATUS_FRAUD;
            }
        } else {
            $message = Mage::helper('sales')->__('Registered notification about captured amount of %s.', $this->_formatPrice($amount));
            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            if ($this->getIsFraudDetected()) {
                $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                $message = Mage::helper('sales')->__('Order is suspended as its capture amount %s is suspected to be fraudulent.', $this->_formatPrice($amount));
                $status = Mage_Sales_Model_Order::STATUS_FRAUD;
            }
            // register capture for an existing invoice
            if ($invoice && Mage_Sales_Model_Order_Invoice::STATE_OPEN == $invoice->getState()) {
                $invoice->pay();
                $this->_updateTotals(array('base_amount_paid_online' => $amount));
                $order->addRelatedObject($invoice);
            }
        }

        $transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $invoice, true);
        $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState($state, $status, $message);
        return $this;
    }
}
