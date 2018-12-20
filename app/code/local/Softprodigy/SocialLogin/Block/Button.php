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
 
class SoftProdigy_SocialLogin_Block_Button extends Mage_Core_Block_Template{

    protected $_buttons;

    protected function _construct(){
        parent::_construct();
        $this->_addButtons();
        $this->setTemplate('softprodigy_sociallogin/button.phtml');
    }

    protected function _addButtons(){
        $this->_addButton(new SoftProdigy_SocialLogin_Block_Button_Type_Facebook());
        $this->_addButton(new SoftProdigy_SocialLogin_Block_Button_Type_Google());
        $this->_addButton(new SoftProdigy_SocialLogin_Block_Button_Type_Linkedin());
        $this->_addButton(new SoftProdigy_SocialLogin_Block_Button_Type_Twitter());
        $this->_addButton(new SoftProdigy_SocialLogin_Block_Button_Type_Yahoo());
    }

    protected function _addButton(SoftProdigy_SocialLogin_Block_Button_Type $button){
        $this->_buttons[] = $button;
    }

    protected function getButtons(){
        return $this->_buttons;
    }

}
