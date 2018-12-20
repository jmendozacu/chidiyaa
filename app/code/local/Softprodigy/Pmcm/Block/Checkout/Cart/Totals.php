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
class Softprodigy_Pmcm_Block_Checkout_Cart_Totals extends Mage_Checkout_Block_Cart_Totals
{
    public function needDisplayBaseGrandtotal()
    {
        $quote = $this->getQuote();
        //if ($quote->getPayment()->getMethodInstance()->getCode() == 'paypal_standard') {
        if ($quote->getPayment()->getMethodInstance()->getCode() == 'paypal_express') {
            if (Mage::helper('pmcm')->shouldConvert() && ($quote->getQuoteCurrencyCode() != Mage::helper('pmcm')->getToCurrency())) {
                return true;
            } else {
                return false;
            }
        }
        if ($quote->getBaseCurrencyCode() != $quote->getQuoteCurrencyCode()) {
            return true;
        }
        return false;
    }

    public function displayBaseGrandtotal()
    {
        $firstTotal = reset($this->_totals);
        if (Mage::helper('pmcm')->shouldConvert()) {
            $total = $firstTotal->getAddress()->getBaseGrandTotal();
            $total = Mage::helper('pmcm')->getExchangeRate($total);
            $currency = Mage::getModel('directory/currency')->load(Mage::helper('pmcm')->getToCurrency());
            return $currency->format($total, array(), true);
        }
        if ($firstTotal) {
            $total = $firstTotal->getAddress()->getBaseGrandTotal();
            return Mage::app()->getStore()->getBaseCurrency()->format($total, array(), true);
        }
        return '-';
    }
}
