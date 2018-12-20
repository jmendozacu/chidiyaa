<?php

class TM_Cache_Adminhtml_Tmcache_DashboardController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/tmcache_dashboard/index')
            ->_addBreadcrumb(
                Mage::helper('tmcache')->__('Full Page Cache'),
                Mage::helper('tmcache')->__('Full Page Cache')
            );
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Cache Dashboard'));
        $this->_initAction();
        $this->renderLayout();
    }

    public function chartAction()
    {
        $block = $this->getLayout()->createBlock('tmcache/adminhtml_dashboard_chart')
            ->setRange($this->getRequest()->getParam('range'));
        $block->toHtml();

        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
            'labels' => $block->getLabels(),
            'hits'   => $block->getHits(),
            'misses' => $block->getMisses()
        )));
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/tmcache_dashboard');
    }
}
