<?php

class TM_Cache_Block_Adminhtml_Dashboard extends Mage_Adminhtml_Block_Template
{
    protected $_canUseTags   = null;
    protected $_tmCacheCount = null;

    protected function _getCacheInstance()
    {
        return Mage::getSingleton('tmcache/cache')->getCacheInstance();
    }

    public function getCacheCountLabel($separator = ' / ')
    {
        return implode($separator, array(
            $this->__('Magento cache records'),
            $this->__('TM_CACHE tagged records')
        ));
    }

    public function getCacheCountString($separator = ' / ')
    {
        return implode($separator, array(
            $this->getAllIdsCount(),
            $this->getTmCacheIdsCount()
        ));
    }

    public function getAllIdsCount()
    {
        return count(Mage::app()->getCacheInstance()->getFrontend()->getIds());
    }

    public function getTmCacheIdsCount()
    {
        try {
            $this->_tmCacheCount = count(
                $this->_getCacheInstance()
                    ->getFrontend()
                    ->getIdsMatchingTags(array(TM_Cache_Model_Cache::TAG))
            );
            $this->_canUseTags = true;
        } catch (Exception $e) {
            $this->_canUseTags   = false;
            $this->_tmCacheCount = 0;
        }
        return $this->_tmCacheCount;
    }

    public function canUseTags()
    {
        if (null === $this->_canUseTags) {
            $this->getTmCacheIdsCount();
        }
        return $this->_canUseTags;
    }
}
