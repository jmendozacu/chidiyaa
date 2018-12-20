<?php

class TM_Cache_Block_Core_Messages extends Mage_Core_Block_Messages
{
    protected $_storageTypesToInitialize = array();

    public function getGroupedHtml()
    {
        $helper = Mage::helper('tmcache');
        if (Mage::registry('tmcache_render')
            || false === $this->getUsePlaceholder()
            || !$helper->canUseCache($this->getRequest())) {

            return parent::getGroupedHtml();
        }

        return '{{tm_cache block type="core/messages" name="messages" method="getGroupedHtml"}}';
    }

    /**
     * Fix to prevent duplicate value in usedStorageTypes array
     *
     * @see TM_Cache_Model_Layout::initMessages
     */
    public function addStorageType($type)
    {
        if (method_exists('Mage_Core_Block_Messages', 'addStorageType')) {
            $this->_storageTypesToInitialize[] = $type;
            parent::addStorageType($type);
        }
    }

    public function getUsedStorageTypes()
    {
        if (!empty($this->_storageTypesToInitialize)) {
            // usually it's without the core/session
            return $this->_storageTypesToInitialize;
        }
        // magento 1.5.1 and older
        return array(
            'core/session',     'catalog/session',
            'checkout/session', 'customer/session',
            'tag/session',      'catalogsearch/session',
            'review/session'
        );
    }
}
