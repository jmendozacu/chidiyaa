<?php

class TM_Crawler_Model_Resource_Crawler_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('tmcrawler/crawler');
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            if ($store instanceof Mage_Core_Model_Store) {
                $store = array($store->getId());
            }

            if (!is_array($store)) {
                $store = array($store);
            }

            if ($withAdmin) {
                $store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
            }

            $where = array();
            $db = $this->getConnection();
            foreach ($store as $_store) {
                $where[] = $db->quoteInto('store_ids = ?', $_store);
                $where[] = $db->quoteInto('store_ids like ?',        $_store . ',%');
                $where[] = $db->quoteInto('store_ids like ?', '%,' . $_store);
                $where[] = $db->quoteInto('store_ids like ?', '%,' . $_store . ',%');
            }
            $where = implode(' OR ', $where);
            $this->getSelect()->where($where);
        }
        return $this;
    }

    public function addTypeFilter($type)
    {
        if (!$this->getFlag('type_filter_added')) {
            if (!is_array($type)) {
                $type = array($type);
            }

            $where = array();
            $db = $this->getConnection();
            foreach ($type as $_type) {
                $where[] = $db->quoteInto('type = ?', $_type);
                $where[] = $db->quoteInto('type like ?',        $_type . ',%');
                $where[] = $db->quoteInto('type like ?', '%,' . $_type);
                $where[] = $db->quoteInto('type like ?', '%,' . $_type . ',%');
            }
            $where = implode(' OR ', $where);
            $this->getSelect()->where($where);
        }
        return $this;
    }

    protected function _toOptionHash($valueField = 'crawler_id', $labelField = 'identifier')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }
}
