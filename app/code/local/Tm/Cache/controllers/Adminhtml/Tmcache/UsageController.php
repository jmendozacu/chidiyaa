<?php

class TM_Cache_Adminhtml_Tmcache_UsageController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/tmcache_usage/index')
            ->_addBreadcrumb(
                Mage::helper('tmcache')->__('Full Page Cache'),
                Mage::helper('tmcache')->__('Full Page Cache')
            );
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Cache Usage Statistics'));
        $this->_initAction();
        $this->renderLayout();
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('cache_id');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select item(s).'));
        } else {
            if (!empty($ids)) {
                try {
                    $cache = Mage::getSingleton('tmcache/cache');
                    foreach ($ids as $id) {
                        $cache->remove($id);
                    }
                    Mage::getResourceModel('tmcache/log')->updateMultipleRows(
                        array('cache_id' => ''),
                        array('cache_id IN (?)' => $ids)
                    );
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($ids))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    public function deleteOldAction()
    {
        try {
            $count = Mage::getModel('tmcache/observer')
                ->removeOldRecordsWithNoHits(true);
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Total of %d record(s) have been deleted.',
                    $count
                )
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
            case 'massdelete':
            case 'deleteold':
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/tmcache_usage/delete');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/tmcache_usage');
                break;
        }
    }

    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
}
