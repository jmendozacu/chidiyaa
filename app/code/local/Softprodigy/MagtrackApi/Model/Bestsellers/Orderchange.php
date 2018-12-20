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
class Softprodigy_MagtrackApi_Model_Bestsellers_Orderchange extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('softprodigy_magtrackapi/bestsellers_orderchange');
    }
    
    public function getOrderIds(){
        return $this->_getResource()->getOrderIds();
    }
}
