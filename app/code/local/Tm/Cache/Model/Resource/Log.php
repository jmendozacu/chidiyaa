<?php

class TM_Cache_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('tmcache/log', 'entity_id');
    }

    /**
     * Returns grouped by is_hit and created_at records
     *
     * @param  string $range 1m, 1h, 24h, 7d
     * @return array
     * array(
     *     timeLabel => array(hits, misses, label)
     *     timeLabel => array(hits, misses, label)
     * )
     */
    public function getRecentHitsAndMisses($range = '7d')
    {
        $helper     = Mage::helper('tmcache/chart');
        $rangeRule  = $helper->getRangeRules($range);
        $stepRule   = $helper->getStepRules($rangeRule['step']);

        $left   = $stepRule['left'];
        $right  = $stepRule['right'];
        $concat = $stepRule['concat'];
        $timeTo   = strtotime('now');
        $timeFrom = strtotime($rangeRule['from']);

        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from(
                $this->getTable('tmcache/log'),
                array(
                    'count' => 'COUNT(entity_id)',
                    'label' => "CONCAT(RIGHT(LEFT(created_at, $left), $right), '$concat')",
                    'type'  => "IF (is_hit, 'hit', 'miss')",
                    'date'  => "CONCAT(LEFT(created_at, $left), '$concat')"
                )
            )
            ->group("is_hit, LEFT(created_at, $left)")
            ->order('created_at ASC')
            ->where('created_at >= ?', date("Y-m-d H:i:s", $timeFrom))
            ->where('crawler_id = 0');

        $tmp = array();
        foreach ($adapter->fetchAll($select) as $values) {
            $tmp[$values['date']][$values['type']] = (int) $values['count'];
        }

        // fill empty labels with zeros
        $labels = $helper->getLabels($timeFrom, $timeTo, $rangeRule['step']);
        $result = array();
        foreach ($labels as $label) {
            $result[$label['long']] = array(
                'hit'   => isset($tmp[$label['long']]['hit'])  ? $tmp[$label['long']]['hit']  : 0,
                'miss'  => isset($tmp[$label['long']]['miss']) ? $tmp[$label['long']]['miss'] : 0,
                'label' => $label['short']
            );
        }

        return $result;
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

    public function updateMultipleRows($data, $condition)
    {
        return $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            $data,
            $condition
        );
    }

    /**
     * Load an object using 'cache_id' field if there's no field specified and value is not numeric
     *
     * @param Mage_Core_Model_Abstract $object
     * @param mixed $value
     * @param string $field
     * @return TM_Cache_Model_Resource_Log
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'cache_id';
        }

        return parent::load($object, $value, $field);
    }
}
