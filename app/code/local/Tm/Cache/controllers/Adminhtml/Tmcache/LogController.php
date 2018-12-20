<?php

class TM_Cache_Adminhtml_Tmcache_LogController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/tmcache_log/index')
            ->_addBreadcrumb(
                Mage::helper('tmcache')->__('Full Page Cache'),
                Mage::helper('tmcache')->__('Full Page Cache')
            );
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Cache Activity Log'));
        $this->_initAction();
        $this->renderLayout();
    }

    public function clearAction()
    {
        try {
            $mode = $this->getRequest()->getParam('mode', 'old');
            if ($mode === 'all') {
                $result = Mage::getResourceModel('tmcache/log')->clear();
            } else {
                $result = Mage::getModel('tmcache/observer')->clearLog();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__('Total of %d record(s) have been deleted.', $result)
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());
        switch ($action) {
            case 'clear':
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/tmcache_log/clear');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/tmcache_log');
                break;
        }
    }
}
