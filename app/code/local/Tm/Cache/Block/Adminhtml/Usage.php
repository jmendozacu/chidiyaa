<?php

class TM_Cache_Block_Adminhtml_Usage extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'tmcache';
        $this->_controller = 'adminhtml_usage';
        $this->_headerText = Mage::helper('tmcache')->__('Cache Usage Statistics');

        parent::__construct();

        $this->_removeButton('add');

        if ($this->_isAllowedAction('delete')) {
            $alert = $this->__('Are you sure you want to do this?');
            $url = $this->getUrl('*/*/deleteOld');
            $this->_addButton('delete_old', array(
                'label'   => Mage::helper('tmcache')->__('Delete old cache records with no hits'),
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
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/tmcache_usage/' . $action);
    }
}
