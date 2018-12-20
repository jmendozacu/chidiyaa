<?php
class Softprodigy_MagtrackApi_Model_User extends Mage_Core_Model_Abstract
{
    /**
     * @var SoftProdigy_MagtrackApi_Helper_Data
     */
    protected $_helper;
    
    public function __construct() {
        $this->_helper = Mage::helper('softprodigy_magtrackapi');
    }
    
    
    /**
     * login API
     */
    public function login($username, $password)
    {
        $session = Mage::getSingleton('admin/session');
        if( $this->authenticate($username, $password)){
            if (empty($username) || empty($password)) {
                return;
            }
            /** @var $user Mage_Admin_Model_User */
            $user = Mage::getModel('admin/user');
            $user->login($username, $password);
            if ($user->getId()) {
                if (method_exists($session, 'renewSession')) {
                    $session->renewSession();
                }
                $session->setIsFirstPageAfterLogin(true);
                $session->setUser($user);
                $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
                
                return true;
            }
        }
        return false;
    }
    
    
    /**
     * logout API
     */
    public function logout(){
        //set time before logout
        if(Mage::getSingleton('admin/session', array('name' => 'adminhtml'))->isLoggedIn()){
            Mage::getModel('softprodigy_magtrackapi/settings')->saveLogout();
        }
        //clear all datas
        Mage::getSingleton('admin/session', array('name' => 'adminhtml'))
            ->getCookie()->delete(
                Mage::getSingleton('admin/session', array('name' => 'adminhtml'))
                    ->getSessionName());
        Mage::getSingleton('admin/session', array('name' => 'adminhtml'))->unsetAll();
        Mage::getSingleton('adminhtml/session')->unsetAll();
    }
    
    /**
     * API authenticate
     * @param type $username
     * @param type $password
     * @return boolean
     * @throws Mage_Core_Exception
     */
    protected function authenticate($username,$password){
        $config = Mage::getStoreConfigFlag('admin/security/use_case_sensitive_login');
        $result = false;
        $user = Mage::getModel('admin/user')->loadByUsername($username);
        try {
            $sensitive = ($config) ? $username==$user->getUsername() : true;
            if ($sensitive && $user->getId() && Mage::helper('core')->validateHash($password, $user->getPassword())) {

                if ($user->getIsActive() != '1') {
                    Mage::throwException(Mage::helper('softprodigy_magtrackapi')->__('This account is inactive.'));
                }
                if (!$user->hasAssigned2Role($user->getId())) {
                    $result = false;
                }else{
                    $result = true;
                }
            }
        }catch (Mage_Core_Exception $e) {
            $user->unsetData();
            throw $e;
        }
        return $result;
    }
    
    
}
