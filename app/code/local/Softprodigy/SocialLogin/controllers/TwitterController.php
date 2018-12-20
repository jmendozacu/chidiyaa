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
 
class SoftProdigy_SocialLogin_TwitterController extends Mage_Core_Controller_Front_Action
{
    protected $referer = null;
    protected $flag = null;

    public function requestAction()
    {
        $client = Mage::getSingleton('softprodigy_sociallogin/twitter_client');
        if(!($client->isEnabled())) {
            Mage::helper('softprodigy_sociallogin')->redirect404($this);
        }

        $client->fetchRequestToken();
    }   

    public function connectAction()
    {
		$helper	= Mage::app()->getHelper('softprodigy_sociallogin');
	/*	if($helper->check())
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
		/*} 
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
        $this->referer = Mage::getUrl('softprodigy_sociallogin/account/twitter');
        
        Mage::helper('softprodigy_sociallogin/twitter')->disconnect($customer);

        Mage::getSingleton('core/session')
            ->addSuccess(
                $this->__('You have successfully disconnected your %s account from our store account.', $this->__('Twitter'))
            );
    }     

    protected function _connectCallback() {
        $attributeModel = Mage::getModel('eav/entity_attribute');
        $attributegId = $attributeModel->getIdByCode('customer', 'softprodigy_sociallogin_tid');
        $attributegtoken = $attributeModel->getIdByCode('customer', 'softprodigy_sociallogin_ttoken');
        if($attributegId == false || $attributegtoken == false){
            echo "Attribute `softprodigy_sociallogin_tid` or `softprodigy_sociallogin_ttoken` not exist";
            exit();
        }

        if (!($params = $this->getRequest()->getParams())
            ||
            !($requestToken = unserialize(Mage::getSingleton('core/session')
                ->getTwitterRequestToken()))
            ) {
            // Direct route access - deny
            return;
        }

        $this->referer = Mage::getSingleton('core/session')->getTwitterRedirect();
        
        if(isset($params['denied'])) {
            unset($this->referer);
            $this->flag = "noaccess";
            echo '<script type="text/javascript">window.close();</script>';
            return;
        }       

        $client = Mage::getSingleton('softprodigy_sociallogin/twitter_client');

        $token = $client->getAccessToken();
        
        $userInfo = (object) array_merge(
                (array) ($userInfo = $client->api('/account/verify_credentials.json', 'GET', array('skip_status' => true))),
                array('email' => sprintf('%s@twitter-user.com', strtolower($userInfo->screen_name)))
        );

        $customersByTwitterId = Mage::helper('softprodigy_sociallogin/twitter')
            ->getCustomersByTwitterId($userInfo->id);

        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            // Logged in user
            if($customersByTwitterId->count()) {
                // Twitter account already connected to other account - deny
                Mage::getSingleton('core/session')
                    ->addNotice(
                        $this->__('Your %s account is already connected to one of our store accounts.', $this->__('Twitter'))
                    );

                return;
            }

            // Connect from account dashboard - attach
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            Mage::helper('softprodigy_sociallogin/twitter')->connectByTwitterId(
                $customer,
                $userInfo->id,
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Your %1$s account is now connected to your store account. You can now login using our %1$s Connect button or using store account credentials you will receive to your email address.', $this->__('Twitter'))
            );

            return;
        }

        if($customersByTwitterId->count()) {
            // Existing connected user - login
            $customer = $customersByTwitterId->getFirstItem();

            Mage::helper('softprodigy_sociallogin/twitter')->loginByCustomer($customer);

            Mage::getSingleton('core/session')
                ->addSuccess(
                    $this->__('You have successfully logged in using your %s account.', $this->__('Twitter'))
                );

            return;
        }

        $customersByEmail = Mage::helper('softprodigy_sociallogin/twitter')
            ->getCustomersByEmail($userInfo->email);

        if($customersByEmail->count()) {
            // Email account already exists - attach, login
            $customer = $customersByEmail->getFirstItem();

            Mage::helper('softprodigy_sociallogin/twitter')->connectByTwitterId(
                $customer,
                $userInfo->id,
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('We have discovered you already have an account at our store. Your %s account is now connected to your store account.', $this->__('Twitter'))
            );

            return;
        }

        // New connection - create, attach, login
        if(empty($userInfo->name)) {
            throw new Exception(
                $this->__('Sorry, could not retrieve your %s last name. Please try again.', $this->__('Twitter'))
            );
        }

        Mage::helper('softprodigy_sociallogin/twitter')->connectByCreatingAccount(
            $userInfo->email,
            $userInfo->name,
            $userInfo->id,
            $token
        );

        Mage::getSingleton('core/session')->addSuccess(
            $this->__('Your Twitter account is now connected to your new user accout at our store. Now you can login using our Twitter Connect button.')
        );
        Mage::getSingleton('core/session')->addNotice(
            sprintf($this->__('Since Twitter doesn\'t support third-party access to your email address, we were unable to send you your store accout credentials. To be able to login using store account credentials you will need to update your email address and password using our <a href="%s">Edit Account Information</a>.'), Mage::getUrl('customer/account/edit'))
        );
    }

}
