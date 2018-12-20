<?php

class TM_Crawler_Block_Adminhtml_Report extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'tmcrawler';
        $this->_controller = 'adminhtml_report';
        $this->_headerText = Mage::helper('adminhtml')->__("Reports");

        parent::__construct();

        $this->_removeButton('add');

        if ($this->_isAllowedAction('clear')) {
            $alert = $this->__('Are you sure you want to do this?');
            $url   = $this->getUrl('*/*/clear', array('mode' => 'all'));

            $this->_addButton('clear', array(
                'label'   => $this->__('Clear Report'),
                'onclick' => 'if (confirm(\'' . $alert . '\')) { setLocation(\'' . $url .'\'); }',
                'class'   => 'delete'
            ));

            $url = $this->getUrl('*/*/clear', array('mode' => 'old'));
            $this->_addButton('clear_old', array(
                'label'   => $this->__('Clear Old Report Entries'),
                'onclick' => 'if (confirm(\'' . $alert . '\')) { setLocation(\'' . $url .'\'); }',
                'class'   => 'delete'
            ));
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
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/crawler/report/' . $action);
    }
}
