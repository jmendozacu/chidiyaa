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
 
class SoftProdigy_SocialLogin_Block_Button_Type_Twitter extends SoftProdigy_SocialLogin_Block_Button_Type{

    protected $_class = 'ico-tw';
    protected $_title = 'Twitter';
    protected $_name = 'twitter';
    protected $_width = 700;
    protected $_height = 500;
    protected $_disconnect = 'softprodigy_sociallogin/twitter/disconnect';

    public function __construct($name = null, $class = null,$title=null){
        parent::__construct();

        $this->client = Mage::getSingleton('softprodigy_sociallogin/twitter_client');
        if(!($this->client->isEnabled())) {
            return;
        }

        $this->userInfo = Mage::registry('softprodigy_sociallogin_twitter_userinfo');

        if(!($redirect = Mage::getSingleton('customer/session')->getBeforeAuthUrl())) {
            $redirect = Mage::helper('core/url')->getCurrentUrl();
        }

        // Redirect uri
        Mage::getSingleton('core/session')->setTwitterRedirect($redirect);
    }

}
