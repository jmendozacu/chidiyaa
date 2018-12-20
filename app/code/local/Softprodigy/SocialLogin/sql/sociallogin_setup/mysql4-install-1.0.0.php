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
 
$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$installer->addAttribute('customer', 'softprodigy_sociallogin_gid', array(
    'type' => 'text',
    'visible' => false,
    'required' => false,
    'user_defined' => false
));

$installer->addAttribute('customer', 'softprodigy_sociallogin_gtoken', array(
    'type' => 'text',
    'visible' => false,
    'required' => false,
    'user_defined' => false
));

$installer->addAttribute('customer', 'softprodigy_sociallogin_fid', array(
    'type' => 'text',
    'visible' => false,
    'required' => false,
    'user_defined' => false
));


$installer->addAttribute('customer', 'softprodigy_sociallogin_ftoken', array(
    'type' => 'text',
    'visible' => false,
    'required' => false,
    'user_defined' => false
));

$installer->addAttribute('customer', 'softprodigy_sociallogin_tid', array(
    'type' => 'text',
    'visible' => false,
    'required' => false,
    'user_defined' => false
));

$installer->addAttribute('customer', 'softprodigy_sociallogin_ttoken', array(
    'type' => 'text',
    'visible' => false,
    'required' => false,
    'user_defined' => false
));

$installer->addAttribute('customer', 'softprodigy_sociallogin_lid', array(
    'type' => 'text',
    'visible' => false,
    'required' => false,
    'user_defined' => false
));

$installer->addAttribute('customer', 'softprodigy_sociallogin_ltoken', array(
    'type' => 'text',
    'visible' => false,
    'required' => false,
    'user_defined' => false
));

$installer->addAttribute('customer', 'softprodigy_sociallogin_yid', array(
    'type' => 'text',
    'visible' => false,
    'required' => false,
    'user_defined' => false
));

$installer->addAttribute('customer', 'softprodigy_sociallogin_ytoken', array(
    'type' => 'text',
    'visible' => false,
    'required' => false,
    'user_defined' => false
));

$installer->endSetup();
