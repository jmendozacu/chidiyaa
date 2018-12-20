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
 
class SoftProdigy_SocialLogin_Block_Button_Type extends Mage_Core_Block_Template{

    protected $_class;
    protected $_title;
    protected $_name;
    protected $_width;
    protected $_height;
    protected $client = null;
    protected $userInfo = null;
    protected $_disconnect;

    public function __construct($name = null, $class = null, $title= null){
        parent::__construct();
        if($name){
            $this->_name = $name;
        }
        if($class){
            $this->_class = $class;
        }
        if($title){
            $this->_title = $title;
        }
        $this->setTemplate('softprodigy_sociallogin/button/type.phtml');
    }

    protected function getClass(){
        return $this->_class;
    }

    protected function getTitle(){
        return $this->_title;
    }

    protected function getName(){
        return $this->_name;
    }

    protected function getWidth(){
        return $this->_width;
    }

    protected function getHeight(){
        return $this->_height;
    }

    protected function getDisconnect(){
        return $this->_disconnect;
    }

    protected function getUrlDisconnect(){
        $url = $this->getUrl($this->getDisconnect());
        return $url;
    }

    protected function setClass($class){
        $this->_class = $class;
    }

    protected function setTitle($title){
        $this->_title = $title;
    }

    protected function setName($name){
        $this->_name = $name;
    }

    protected function setWidth($width){
        $this->_width = $width;
    }

    protected function setHeight($height){
        $this->_height = $height;
    }

    protected function setDisconnect($disconnect){
        $this->_disconnect = $disconnect;
    }

    protected function render(){
        return $this->toHtml();
    }

    protected function _getXmlPath(){
        $data = 'softprodigy_sociallogin/'.$this->getName().'/enabled';
        return $data;
    }

    protected function checkEnable(){
        $result = false;
        $enabled = Mage::getStoreConfig($this->_getXmlPath());
        if($enabled == 1){
            $result = true;
        }
        return $result;
    }

    protected function _getButtonUrl()
    {
        if(empty($this->userInfo)) {
            return $this->client->createAuthUrl();
        } else {
            return $this->getUrlDisconnect();
        }
    }
}
