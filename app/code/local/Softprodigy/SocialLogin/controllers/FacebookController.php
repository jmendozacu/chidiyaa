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
 
class SoftProdigy_SocialLogin_FacebookController extends Mage_Core_Controller_Front_Action
{
    protected $referer = null;
    protected $flag = null;

    public function connectAction()
    {
		$helper	= Mage::app()->getHelper('softprodigy_sociallogin');
		/*if($helper->check())
		{*/
		
			try {
				$this->_connectCallback();
			} catch (Exception $e) {
				Mage::getSingleton('core/session')->addError($e->getMessage());
			}

			if (!empty($this->referer)) {
				if(empty($this->flag)){
					echo '
					<script type="text/javascript">
						window.opener.location.reload(true);
						window.close();
					</script>
					';
				}else{
					echo '
					<script type="text/javascript">
						window.close();
					</script>
					';
				}

			} else {
				Mage::helper('softprodigy_sociallogin')->redirect404($this);
			}
        
       /* } 
		else 
		{
			die("<script type='text/javascript'>window.location = '".Mage::getUrl()."';</script>");
		}	*/
    }

    public function disconnectAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        try {
            $this->_disconnectCallback($customer);
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        if (!empty($this->referer)) {
            $this->_redirectUrl($this->referer);
        } else {
            Mage::helper('softprodigy_sociallogin')->redirect404($this);
        }
    }

    protected function _disconnectCallback(Mage_Customer_Model_Customer $customer)
    {
        $this->referer = Mage::getUrl('softprodigy_sociallogin/account/facebook');

        Mage::helper('softprodigy_sociallogin/facebook')->disconnect($customer);

        Mage::getSingleton('core/session')
            ->addSuccess(
                $this->__('You have successfully disconnected your %s account from our store account.', $this->__('Facebook'))
            );
    }

    protected function _connectCallback()
    {
        $errorCode = $this->getRequest()->getParam('error');
        $code = $this->getRequest()->getParam('code');
        $state = $this->getRequest()->getParam('state');
        if (!($errorCode || $code) && !$state) {
            // Direct route access - deny
            return;
        }

        $this->referer = Mage::getSingleton('core/session')
            ->getFacebookRedirect();

        if (!$state || $state != Mage::getSingleton('core/session')->getFacebookCsrf()) {
            //return;
        }

        if ($errorCode) {
            // Facebook API read light - abort
            if ($errorCode === 'access_denied') {
                $this->flag = "noaccess";
                echo '<script type="text/javascript">window.close();</script>';
            }
            return;
        }

        if ($code) {
            $attributeModel = Mage::getModel('eav/entity_attribute');
            $attributegId = $attributeModel->getIdByCode('customer', 'softprodigy_sociallogin_fid');
            $attributegtoken = $attributeModel->getIdByCode('customer', 'softprodigy_sociallogin_ftoken');
            if($attributegId == false || $attributegtoken == false){
                echo "Attribute `softprodigy_sociallogin_fid` or `softprodigy_sociallogin_ftoken` not exist !";
                exit();
            }
            // Facebook API green light - proceed
            $client = Mage::getSingleton('softprodigy_sociallogin/facebook_client');

            $userInfo = $client->api('/me');
            $fields = array('id', 'name', 'first_name', 'last_name', 'link', 'website','gender', 'locale', 'about', 'email', 'hometown', 'location');
            $userInfo = $client->api('/me?fields=' . implode(',', $fields));
            //var_dump($userInfo);
            $token = $client->getAccessToken();

            $customersByFacebookId = Mage::helper('softprodigy_sociallogin/facebook')
                ->getCustomersByFacebookId($userInfo->id);

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                // Logged in user
                if ($customersByFacebookId->count()) {
                    // Facebook account already connected to other account - deny
                    Mage::getSingleton('core/session')
                        ->addNotice(
                            $this->__('Your %s account is already connected to one of our store accounts.', $this->__('Facebook'))
                        );

                    return;
                }

                // Connect from account dashboard - attach
                $customer = Mage::getSingleton('customer/session')->getCustomer();

                Mage::helper('softprodigy_sociallogin/facebook')->connectByFacebookId(
                    $customer,
                    $userInfo->id,
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('Your %1$s account is now connected to your store account. You can now login using our %1$s Connect button or using store account credentials you will receive to your email address.', $this->__('Facebook'))
                );

                return;
            }

            if ($customersByFacebookId->count()) {
                // Existing connected user - login
                $customer = $customersByFacebookId->getFirstItem();

                Mage::helper('softprodigy_sociallogin/facebook')->loginByCustomer($customer);

                Mage::getSingleton('core/session')
                    ->addSuccess(
                        $this->__('You have successfully logged in using your %s account.', $this->__('Facebook'))
                    );

                return;
            }

            $customersByEmail = Mage::helper('softprodigy_sociallogin/facebook')
                ->getCustomersByEmail($userInfo->email);

            if ($customersByEmail->count()) {
                // Email account already exists - attach, login
                $customer = $customersByEmail->getFirstItem();

                Mage::helper('softprodigy_sociallogin/facebook')->connectByFacebookId(
                    $customer,
                    $userInfo->id,
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('We have discovered you already have an account at our store. Your %s account is now connected to your store account.', $this->__('Facebook'))
                );

                return;
            }

            // New connection - create, attach, login
            if (empty($userInfo->first_name)) {
                throw new Exception(
                    $this->__('Sorry, could not retrieve your %s first name. Please try again.', $this->__('Facebook'))
                );
            }

            if (empty($userInfo->last_name)) {
                throw new Exception(
                    $this->__('Sorry, could not retrieve your %s last name. Please try again.', $this->__('Facebook'))
                );
            }

            Mage::helper('softprodigy_sociallogin/facebook')->connectByCreatingAccount(
                $userInfo->email,
                $userInfo->first_name,
                $userInfo->last_name,
                $userInfo->id,
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Your %1$s account is now connected to your new user accout at our store. Now you can login using our %1$s Connect button or using store account credentials you will receive to your email address.', $this->__('Facebook'))
            );
        }
    }

}
