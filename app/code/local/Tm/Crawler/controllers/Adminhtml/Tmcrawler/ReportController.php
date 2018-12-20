<?php

class TM_Crawler_Adminhtml_Tmcrawler_ReportController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/tmcache/crawler/report')
            ->_addBreadcrumb(
                $this->__("Reports"),
                $this->__("Reports")
            );
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__("Reports"));
        $this->_initAction();
        $this->renderLayout();
    }

    public function clearAction()
    {
        try {
            $mode = $this->getRequest()->getParam('mode', 'old');
            $where = '';
            if ($mode === 'old') {
                $days = 5;
                $date = gmdate('Y-m-d H:i:s', strtotime('now - ' . $days . ' days'));
                $where = array();
                $where['created_at < ?'] = $date;
            }
            $result = Mage::getResourceModel('tmcrawler/report')->clear($where);
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
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/crawler/report/clear');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/crawler/report');
                break;
        }
    }
}
