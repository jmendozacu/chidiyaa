<?php

class TM_Crawler_Adminhtml_Tmcrawler_LogController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/tmcache/crawler/log')
            ->_addBreadcrumb(
                $this->__("Crawler's Log"),
                $this->__("Crawler's Log")
            );
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__("Crawler's Activity Log"));

        if (!Mage::helper('tmcache')->isEnabled()) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                $this->__(
                    "Crawlers are suspended while <a href='%s' title='%s'>%s</a>",
                    $this->getUrl('*/cache/index'),
                    $this->__('Cache Management'),
                    $this->__('Full Page Cache is disabled')
                )
            );
        }

        $this->_initAction();
        $this->renderLayout();
    }

    public function clearAction()
    {
        try {
            $mode = $this->getRequest()->getParam('mode', 'old');
            $where = array('crawler_id > ?' => 0);
            if ($mode === 'old') {
                $days = (int) Mage::getStoreConfig('tmcache/cron/old_log_records_days');
                $date = gmdate('Y-m-d H:i:s', strtotime('now - ' . $days . ' days'));
                $where['created_at < ?'] = $date;
            }
            $result = Mage::getResourceModel('tmcache/log')->clear($where);
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
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/crawler/log/clear');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/crawler/log');
                break;
        }
    }
}
