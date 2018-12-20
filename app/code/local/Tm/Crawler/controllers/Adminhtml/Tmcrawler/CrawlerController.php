<?php

class TM_Crawler_Adminhtml_Tmcrawler_CrawlerController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/tmcache/crawler/crawler')
            ->_addBreadcrumb($this->__('Crawler'), $this->__('Crawler'));
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Crawler'));

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

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title($this->__('Crawler'));

        $id = $this->getRequest()->getParam('crawler_id');
        $model = Mage::getModel('tmcrawler/crawler');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__('This crawler no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getIdentifier() : $this->__('New Crawler'));

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('crawler', $model);

        if (TM_Crawler_Model_Crawler::STATE_RUNNING === $model->getState()) {
            $refreshUrl = $this->getUrl('*/*/*', array('_current' => true, 'active_tab' => '{{tab_id}}'));
            Mage::getSingleton('adminhtml/session')->addNotice(
                $this->__(
                    "Crawler is running and cannot be changed. See the progress on the 'Log' tab. %sRefresh%s",
                    '<a href="#" onclick="setLocation(getUrl(\''.$refreshUrl.'\')); return false;">',
                    '</a>'
                )
            );
        }

        $this->_initAction()
            ->_addBreadcrumb(
                $model->getId() ? $model->getIdentifier() : $this->__('New Crawler'),
                $model->getId() ? $model->getIdentifier() : $this->__('New Crawler')
            );

        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$data = $this->getRequest()->getPost('crawler')) {
            $this->_redirect('*/*/');
            return;
        }

        $model = Mage::getModel('tmcrawler/crawler');
        if ($id = $this->getRequest()->getParam('crawler_id')) {
            $model->load($id);
        }

        try {
            if (TM_Crawler_Model_Crawler::STATE_RUNNING === $model->getState()) {
                throw new Exception($this->__('Crawler is running and cannot be saved.'));
            }
            $model->addData($data);
            $model->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('tmcrawler')->__('Crawler has been saved.')
            );
            Mage::getSingleton('adminhtml/session')->setFormData(false);
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('crawler_id' => $model->getId(), '_current' => true));
                return;
            }
            $this->_redirect('*/*/');
            return;
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_getSession()->setFormData($data);
        $this->_redirect('*/*/edit', array('_current'=>true));
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('crawler_id')) {
            try {
                $model = Mage::getModel('tmcrawler/crawler');
                $model->load($id);
                if (TM_Crawler_Model_Crawler::STATE_RUNNING === $model->getState()) {
                    throw new Exception($this->__('Crawler is running and cannot be deleted.'));
                }
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('tmcrawler')->__('Crawler has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('crawler_id' => $id));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tmcrawler')->__('Unable to find a crawler to delete.'));
        $this->_redirect('*/*/');
    }

    protected function _initCrawler()
    {
        $id = $this->getRequest()->getParam('crawler_id');
        $model = Mage::getModel('tmcrawler/crawler')->load($id);
        if (!$model->getId()) {
            return false;
        }
        Mage::register('crawler', $model);
        return $model;
    }

    public function logAction()
    {
        if (!$this->_initCrawler()) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('This crawler no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function reportAction()
    {
        if (!$this->_initCrawler()) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('This crawler no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function runAction()
    {
        if (!$crawler = $this->_initCrawler()) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('This crawler no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }
        if (TM_Crawler_Model_Crawler::STATE_RUNNING === $crawler->getState()) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('Crawler is already running.'));
            $this->_redirect('*/*/');
            return;
        }

        session_write_close();
        $crawler->run();
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
            case 'new':
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/crawler/crawler/save');
                break;
            case 'run':
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/crawler/crawler/run');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/crawler/crawler/delete');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/crawler/crawler');
                break;
        }
    }
}
