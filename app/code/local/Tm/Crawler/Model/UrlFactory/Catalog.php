<?php

abstract class TM_Crawler_Model_UrlFactory_Catalog extends TM_Crawler_Model_UrlFactory_Abstract
{
    /**
     * Attribute cache
     *
     * @var array
     */
    protected $_attributesCache = array();

    /**
     * Loads attribute by given attribute_code
     *
     * @param string $attributeCode
     * @return Mage_Sitemap_Model_Resource_Catalog_Abstract
     */
    abstract protected function _loadAttribute($attributeCode);

    /**
     * Add attribute to filter
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param mixed $value
     * @param string $type
     * @return Zend_Db_Select
     */
    protected function _addFilter($storeId, $attributeCode, $value, $type = '=')
    {
        if (!isset($this->_attributesCache[$attributeCode])) {
            $this->_loadAttribute($attributeCode);
        }
        $attribute = $this->_attributesCache[$attributeCode];
        if (!$this->_select instanceof Zend_Db_Select) {
            return false;
        }
        switch ($type) {
            case '=':
                $conditionRule = '=?';
                break;
            case 'in':
                $conditionRule = ' IN(?)';
                break;
            default:
                return false;
                break;
        }
        if ($attribute['backend_type'] == 'static') {
            $this->_select->where('main_table.' . $attributeCode . $conditionRule, $value);
        } else {
            $this->_select->join(
                array('t1_' . $attributeCode => $attribute['table']),
                'main_table.entity_id=t1_' . $attributeCode . '.entity_id AND t1_' . $attributeCode . '.store_id=0',
                array()
            )
                ->where('t1_' . $attributeCode . '.attribute_id=?', $attribute['attribute_id']);
            if ($attribute['is_global']) {
                $this->_select->where('t1_' . $attributeCode . '.value' . $conditionRule, $value);
            } else {
                $ifCase = $this->_select->getAdapter()->getCheckSql('t2_' . $attributeCode . '.value_id > 0',
                    't2_' . $attributeCode . '.value', 't1_' . $attributeCode . '.value'
                );
                $this->_select->joinLeft(
                    array('t2_' . $attributeCode => $attribute['table']),
                    $this->_getWriteAdapter()->quoteInto(
                        't1_' . $attributeCode . '.entity_id = t2_' . $attributeCode . '.entity_id AND t1_'
                            . $attributeCode . '.attribute_id = t2_' . $attributeCode . '.attribute_id AND t2_'
                            . $attributeCode . '.store_id = ?', $storeId
                    ),
                    array()
                )
                ->where('(' . $ifCase . ')' . $conditionRule, $value);
            }
        }
        return $this->_select;
    }
}
