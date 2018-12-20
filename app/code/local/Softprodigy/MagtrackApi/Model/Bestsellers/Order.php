<?php
/**
 * Softprodigy
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Softprodigy.com license that is
 * available through the world-wide-web at this URL:
 * http://www.Softprodigy.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagtrackApi
 */

/**
 * MagtrackApi Bestsellers Model
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagtrackApi
 * @author      Softprodigy Developer
 */
class Softprodigy_MagtrackApi_Model_Bestsellers_Order extends Mage_Sales_Model_Order
{
    /**
     * Order state protected setter.
     * By default allows to set any state. Can also update status to default or specified value
     * Сomplete and closed states are encapsulated intentionally, see the _checkState()
     *
     * @param string $state
     * @param string|bool $status
     * @param string $comment
     * @param bool $isCustomerNotified
     * @param $shouldProtectState
     * @return Mage_Sales_Model_Order
     */
    protected function _setState($state, $status = false, $comment = '', $isCustomerNotified = null, $shouldProtectState = false)
    {
        // dispatch an event before we attempt to do anything
        Mage::dispatchEvent('sales_order_status_before', array('order' => $this, 'state' => $state, 'status' => $status, 'comment' => $comment, 'isCustomerNotified' => $isCustomerNotified, 'shouldProtectState' => $shouldProtectState));

        // attempt to set the specified state
        if ($shouldProtectState) {
            if ($this->isStateProtected($state)) {
                Mage::throwException(Mage::helper('sales')->__('The Order State "%s" must not be set manually.', $state));
            }
        }
        $this->setData('state', $state);

        // add status history
        if ($status) {
            if ($status === true) {
                $status = $this->getConfig()->getStateDefaultStatus($state);
            }
            $this->setStatus($status);
            $history = $this->addStatusHistoryComment($comment, false); // no sense to set $status again
            $history->setIsCustomerNotified($isCustomerNotified); // for backwards compatibility
        }

        // dispatch an event after status has changed
        Mage::dispatchEvent('sales_order_status_after', array('order' => $this, 'state' => $state, 'status' => $status, 'comment' => $comment, 'isCustomerNotified' => $isCustomerNotified, 'shouldProtectState' => $shouldProtectState));

        return $this;
    }
}
