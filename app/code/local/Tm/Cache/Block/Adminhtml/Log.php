<?php

class TM_Cache_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'tmcache';
        $this->_controller = 'adminhtml_log';
        $this->_headerText = Mage::helper('tmcache')->__('Cache Activity Log');

        parent::__construct();

        $this->_removeButton('add');

        if ($this->_isAllowedAction('clear')) {
            $alert = $this->__('Are you sure you want to do this?');
            $url   = $this->getUrl('*/*/clear', array('mode' => 'all'));

            $this->_addButton('clear', array(
                'label'   => Mage::helper('tmcache')->__('Clear Log'),
                'onclick' => 'if (confirm(\'' . $alert . '\')) { setLocation(\'' . $url .'\'); }',
                'class'   => 'delete'
            ));

            $url = $this->getUrl('*/*/clear', array('mode' => 'old'));
            $this->_addButton('clear_old', array(
                'label'   => Mage::helper('tmcache')->__('Clear Old Log Entries'),
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
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/tmcache_log/' . $action);
    }
}
