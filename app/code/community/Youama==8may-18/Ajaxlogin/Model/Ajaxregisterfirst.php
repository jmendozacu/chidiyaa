<?php

/**
 * YOUAMA SOFTWARE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the OSL 3.0 license.
 *
 *******************************************************************************
 * MAGENTO EDITION USAGE NOTICE
 *
 * This package designed for Magento Community Edition. Developer(s) of
 * YOUAMA.COM does not guarantee correct work of this extension on any other
 * Magento Edition except clear installed Magento Community Edition. YouAMA.com
 * does not provide extension support in case of incorrect usage.
 *******************************************************************************
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 *******************************************************************************
 * @category   Youama
 * @package    Youama_Ajaxlogin
 * @copyright  Copyright (c) 2012-2016 David Belicza (http://www.youama.com)
 * @license    https://opensource.org/licenses/osl-3.0.php
 */

/**
 * Register user.
 * Class Youama_Ajaxlogin_Model_Ajaxregister
 * @author David Belicza
 */
class Youama_Ajaxlogin_Model_Ajaxregisterfirst
    extends Youama_Ajaxlogin_Model_Validator
{
    /**
     * Init.
     */
    public function _construct() 
    {
        parent::_construct();

        // Result for Javascript
        $this->_result = '';
        $this->_userId = -1;

        // Terms and conditions has been accepted
        if ($_POST['email'] != '') {
            $this->setEmail($_POST['email']);

            // If this email is already exist
            if ($this->isEmailExist()) {
                $this->_result .=  'emailisexist,';
            // If this email is not exist yet.
            } else {
                $this->_result .=  'emailisnew';
            }
        // Terms and conditions has not been accepted
        } else {
            $this->_result = 'noemail,';
        }        
    }

    
}
