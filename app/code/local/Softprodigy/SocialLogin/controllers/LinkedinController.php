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
 
class SoftProdigy_SocialLogin_LinkedinController extends Mage_Core_Controller_Front_Action
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

			if(!empty($this->referer)) {
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
      /*  } 
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

        if(!empty($this->referer)) {
            $this->_redirectUrl($this->referer);
        } else {
            Mage::helper('softprodigy_sociallogin')->redirect404($this);
        }
    }

    protected function _disconnectCallback(Mage_Customer_Model_Customer $customer) {
        $this->referer = Mage::getUrl('softprodigy_sociallogin/account/linkedin');

        Mage::helper('softprodigy_sociallogin/linkedin')->disconnect($customer);

        Mage::getSingleton('core/session')
            ->addSuccess(
                $this->__('You have successfully disconnected your %s account from our store account.', $this->__('LinkedIn'))
            );
    }

    protected function _connectCallback() {
        $errorCode = $this->getRequest()->getParam('error');
        $code = $this->getRequest()->getParam('code');
        $state = $this->getRequest()->getParam('state');
        if(!($errorCode || $code) && !$state) {
            // Direct route access - deny
            return;
        }
        $this->referer = Mage::getSingleton('core/session')->getLinkedinRedirect();

        if(!$state || $state != Mage::getSingleton('core/session')->getLinkedinCsrf()) {
            //return;
        }

        if($errorCode) {
            // Linkedin API read light - abort
            if($errorCode === 'access_denied') {
                unset($this->referer);
                $this->flag = "noaccess";
                echo '<script type="text/javascript">window.close();</script>';
            }
            return;
        }

        if ($code) {
            $attributeModel = Mage::getModel('eav/entity_attribute');
            $attributegId = $attributeModel->getIdByCode('customer', 'softprodigy_sociallogin_lid');
            $attributegtoken = $attributeModel->getIdByCode('customer', 'softprodigy_sociallogin_ltoken');
            if($attributegId == false || $attributegtoken == false){
                echo "Attribute `softprodigy_sociallogin_lid` or `softprodigy_sociallogin_ltoken` not exist";
                exit();
            }
            // Linkedin API green light - proceed
            $client = Mage::getSingleton('softprodigy_sociallogin/linkedin_client');

            $userInfo = $client->api('/v1/people/~');
            $token = $client->getAccessToken();
            $customersByLinkedinId = Mage::helper('softprodigy_sociallogin/linkedin')
                ->getCustomersByLinkedinId($userInfo->id);

            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                // Logged in user
                if($customersByLinkedinId->count()) {
                    // Linkedin account already connected to other account - deny
                    Mage::getSingleton('core/session')
                        ->addNotice(
                            $this->__('Your %s account is already connected to one of our store accounts.', $this->__('LinkedIn'))
                        );

                    return;
                }

                // Connect from account dashboard - attach
                $customer = Mage::getSingleton('customer/session')->getCustomer();

                Mage::helper('softprodigy_sociallogin/linkedin')->connectByLinkedinId(
                    $customer,
                    $userInfo->id,
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('Your %1$s account is now connected to your store account. You can now login using our %1$s Connect button or using store account credentials you will receive to your email address.', $this->__('Linkedin'))
                );

                return;
            }

            if($customersByLinkedinId->count()) {
                // Existing connected user - login
                $customer = $customersByLinkedinId->getFirstItem();

                Mage::helper('softprodigy_sociallogin/linkedin')->loginByCustomer($customer);

                Mage::getSingleton('core/session')
                    ->addSuccess(
                        $this->__('You have successfully logged in using your %s account.', $this->__('LinkedIn'))
                    );

                return;
            }

            $customersByEmail = Mage::helper('softprodigy_sociallogin/linkedin')
                ->getCustomersByEmail($userInfo->emailAddress);

            if($customersByEmail->count())  {
                // Email account already exists - attach, login
                $customer = $customersByEmail->getFirstItem();

                Mage::helper('softprodigy_sociallogin/linkedin')->connectByLinkedinId(
                    $customer,
                    $userInfo->id,
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('We have discovered you already have an account at our store. Your %s account is now connected to your store account.', $this->__('LinkedIn'))
                );

                return;
            }

            // New connection - create, attach, login
            if(empty($userInfo->firstName)) {
                throw new Exception(
                    $this->__('Sorry, could not retrieve your %s first name. Please try again.', $this->__('LinkedIn'))
                );
            }

            if(empty($userInfo->lastName)) {
                throw new Exception(
                    $this->__('Sorry, could not retrieve your %s last name. Please try again.', $this->__('LinkedIn'))
                );
            }

            Mage::helper('softprodigy_sociallogin/linkedin')->connectByCreatingAccount(
                $userInfo->emailAddress,
                $userInfo->firstName,
                $userInfo->lastName,
                $userInfo->id,
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Your %1$s account is now connected to your new user accout at our store. Now you can login using our %1$s Connect button or using store account credentials you will receive to your email address.', $this->__('LinkedIn'))
            );
        }
    }

}
