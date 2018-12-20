<?php

class TM_Crawler_Model_Resource_Crawler extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('tmcrawler/crawler', 'crawler_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $keys = array('store_ids', 'type', 'currencies');
        foreach ($keys as $key) {
            $value = $object->getData($key);
            if (is_array($value)) {
                $value = implode(TM_Crawler_Model_Crawler::DELIMITER, $value);
                $object->setData($key, $value);
            }
        }

        if (!$object->getState()) {
            $object->setState(TM_Crawler_Model_Crawler::STATE_NEW);
        }

        if (!$object->getLimit()) {
            $object->setLimit(20);
        }

        return parent::_beforeSave($object);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if ($items = $object->getItemsToLog()) {
            $this->_getWriteAdapter()->insertMultiple(
                $this->getTable('tmcrawler/report'),
                $items
            );
        }
        return parent::_afterSave($object);
    }
}
