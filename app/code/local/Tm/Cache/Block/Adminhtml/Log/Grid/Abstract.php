<?php

class TM_Cache_Block_Adminhtml_Log_Grid_Abstract extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Add column to display full action name
     *
     * @return $this
     */
    public function addFullActionNameColumn()
    {
        $this->addColumn('full_action_name', array(
            'header' => Mage::helper('tmcache')->__('Full Action Name'),
            'index'  => 'full_action_name',
            'width'   => 160
        ));
    }

    /**
     * Add column to request path
     *
     * @return $this
     */
    public function addRequestColumn()
    {
        $this->addColumn('request', array(
            'header'   => Mage::helper('adminhtml')->__('Request Path'),
            'index'    => 'params',
            'renderer' => 'tmcache/adminhtml_log_grid_renderer_request',
            'sortable' => false,
            'width'    => 200
        ));
    }

    /**
     * Add column to display query parameters
     *
     * @return $this
     */
    public function addAdditionalParametersColumn()
    {
        $renderer = $this->getLayout()
            ->createBlock('tmcache/adminhtml_log_grid_renderer_params')
            ->setColumnsToUnset(array(
                'request_params',
                'request_uri'
            ));
        $this->addColumn('params', array(
            'header'   => Mage::helper('tmcache')->__('Additional Parameters'),
            'index'    => 'params',
            'sortable' => false,
            'renderer' => $renderer
        ));
    }

    /**
     * Add column to display store name
     *
     * @return $this
     */
    public function addStoreColumn()
    {
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('cms')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => false,
                'store_view'    => true,
                'sortable'      => false
            ));
        }
    }

    /**
     * Add column to display customer group name
     *
     * @return $this
     */
    public function addCustomerGroupColumn()
    {
        $groups = Mage::getResourceModel('customer/group_collection')
            ->load()
            ->toOptionHash();
        $this->addColumn('customer_group_id', array(
            'header'  => Mage::helper('customer')->__('Group'),
            'width'   => 120,
            'index'   => 'customer_group_id',
            'type'    => 'options',
            'options' => $groups
        ));
    }
}
