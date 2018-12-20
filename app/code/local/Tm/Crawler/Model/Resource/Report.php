<?php

class TM_Crawler_Model_Resource_Report extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('tmcrawler/report', 'entity_id');
    }

    /**
     * Remove records from table
     *
     * @param  array $where
     * @return Number of affected rows
     */
    public function clear($where = '')
    {
        return $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
    }
}
