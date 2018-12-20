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
 * @package     Softprodigy_Sociallogin
 * @copyright   Copyright (c) 2015 Softprodigy System Solutions Pvt. Ltd (http://www.softprodigy.com)
 * @license    http://www.opensource.org/licenses/gpl-license.php  GNU General Public License
 */
 
class SoftProdigy_SocialLogin_Model_System_Config_Source_Position {

    public function toOptionArray() {
        return array(
            array('value' => 'top', 'label' => Mage::helper('adminhtml')->__('Top')),
            array('value' => 'inloginbox', 'label' => Mage::helper('adminhtml')->__('In Login Box')),
            array('value' => 'belowloginbox', 'label' => Mage::helper('adminhtml')->__('Bottom')),
            array('value' => 'dontshow', 'label' => Mage::helper('adminhtml')->__("Don't Show")),
        );
    }

}
