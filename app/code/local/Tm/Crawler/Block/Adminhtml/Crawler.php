<?php

class TM_Crawler_Block_Adminhtml_Crawler extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'tmcrawler';
        $this->_controller = 'adminhtml_crawler';
        $this->_headerText = Mage::helper('tmcrawler')->__('Crawler');
        parent::__construct();

        if (!$this->_isAllowedAction('save')) {
            $this->_removeButton('add');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/crawler/crawler/' . $action);
    }
}
