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
class Softprodigy_Pmcm_Model_Source_Currency extends Mage_Adminhtml_Model_System_Config_Source_Currency
{
    protected $_options;

    public function toOptionArray($isMultiselect)
    {
        $_supportedCurrencyCodes = Mage::helper('pmcm')->getSupportedCurrency();
        if (!$this->_options) {
            $this->_options = Mage::app()->getLocale()->getOptionCurrencies();
        }
        $options = array();
        foreach ($this->_options as $option) {
            if (in_array($option['value'], $_supportedCurrencyCodes)) {
                $options[] = $option;
            }
        }
        return $options;
    }
}
