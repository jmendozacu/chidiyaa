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
class Softprodigy_Pmcm_Model_Cart extends Mage_Paypal_Model_Cart
{
	 /**
     * Add a line item
     *
     * @param string  $name
     * @param numeric $qty
     * @param float   $amount
     * @param string  $identifier
     * @return Varien_Object
     */
     
     
    public function addItem($name, $qty, $amount, $identifier = null)
    {
        $this->_shouldRender = true;
        $amount = Mage::helper('pmcm')->getExchangeRate($amount);
        $item = new Varien_Object(array(
            'name'   => $name,
            'qty'    => $qty,
            'amount' => (float)sprintf('%.4F',$amount),
        ));
		if ($identifier) {
            $item->setData('id', $identifier);
        }
        $this->_items[] = $item;
        return $item;
    }

    /**
     * Check the line items and totals according to PayPal business logic limitations
     */
    protected function _validate()
    {
        $this->_areItemsValid = false;
        $this->_areTotalsValid = false;
        //$referenceAmount = $this->_salesEntity->getBaseGrandTotal();
        $referenceAmount = Mage::helper('pmcm')->getExchangeRate($this->_salesEntity->getBaseGrandTotal());

        $itemsSubtotal = 0;
        foreach ($this->_items as $i) {
            $itemsSubtotal = $itemsSubtotal + $i['qty'] * $i['amount'];
        }
        $sum = $itemsSubtotal + $this->_totals[self::TOTAL_TAX];
         /* rounded sum price and converted to exchange rate! */
        if (!$this->_isShippingAsItem) {
            $sum += round(Mage::helper('pmcm')->getExchangeRate($this->_totals[self::TOTAL_SHIPPING]), 2);
        }
        if (!$this->_isDiscountAsItem) {
            $sum -= Mage::helper('pmcm')->getExchangeRate($this->_totals[self::TOTAL_DISCOUNT]);
        }
         
        /**
         * numbers are intentionally converted to strings because of possible comparison error
         * see http://php.net/float
         */
        // match sum of all the items and totals to the reference amount
        if (sprintf('%.4F', $sum) == sprintf('%.4F', $referenceAmount)) {
            $this->_areItemsValid = true;
        }
		// PayPal requires to have discount less than items subtotal
        if (!$this->_isDiscountAsItem) {
            $this->_areTotalsValid = round($this->_totals[self::TOTAL_DISCOUNT], 4) < round($itemsSubtotal, 4);
        } else {
            $this->_areTotalsValid = $itemsSubtotal > 0.00001;
        }
        $this->_areItemsValid = $this->_areItemsValid && $this->_areTotalsValid;
     }

    protected function _render()
    {
        parent::_render();
         /* rounded sum price and converted to exchange rate! */
        foreach ($this->_totals as $key => $value) {
			 $this->_totals[$key] = round(Mage::helper('pmcm')->getExchangeRate($this->_totals[$key]),2);
        }
    }

}
