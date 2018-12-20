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
 
class SoftProdigy_SocialLogin_Block_Button_Type_Linkedin extends SoftProdigy_SocialLogin_Block_Button_Type{

    protected $_class = 'ico-li';
    protected $_title = 'Linkedin';
    protected $_name = 'linkedin';
    protected $_width = 600;
    protected $_height = 500;
    protected $_disconnect = 'softprodigy_sociallogin/linkedin/disconnect';

    public function __construct($name = null, $class = null,$title=null){
        parent::__construct();

        $this->client = Mage::getSingleton('softprodigy_sociallogin/linkedin_client');
        if(!($this->client->isEnabled())) {
            return;
        }

        $this->userInfo = Mage::registry('softprodigy_sociallogin_linkedin_userinfo');

        // CSRF protection
        Mage::getSingleton('core/session')->setLinkedinCsrf($csrf = md5(uniqid(rand(), TRUE)));
        $this->client->setState($csrf);

        if(!($redirect = Mage::getSingleton('customer/session')->getBeforeAuthUrl())) {
            $redirect = Mage::helper('core/url')->getCurrentUrl();
        }

        // Redirect uri
        Mage::getSingleton('core/session')->setLinkedinRedirect($redirect);
    }

}
