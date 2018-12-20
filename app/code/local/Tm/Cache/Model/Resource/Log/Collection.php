<?php

class TM_Cache_Model_Resource_Log_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_map = array('fields' => array(
        'hit_count'   => 'COUNT(entity_id) - 1',
        'last_access' => 'MAX(created_at)'
    ));

    protected $_havingFields = array(
        'hit_count',
        'last_access'
    );

    protected function _construct()
    {
        $this->_init('tmcache/log');
    }

    /**
     * Having fields filters
     *
     * @param string $field
     * @param array $condition
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (in_array($field, $this->_havingFields)) {
            $resultCondition = $this->_translateCondition($field, $condition);
            $this->_select->having($resultCondition);
            return $this;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Retrieve all cache_ids for collection
     *
     * @return array
     */
    public function getAllCacheIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);

        $idsSelect->columns('cache_id', 'main_table');
        return $this->getConnection()->fetchCol($idsSelect);
    }

    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            $this->_totalRecords = count($this->getConnection()->fetchCol($sql, $this->_bindParams));
        }
        return intval($this->_totalRecords);
    }

    /**
     * Overriden to get it work with left join and group stmt
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        $countSelect->columns('main_table.entity_id');

        return $countSelect;
    }
}
