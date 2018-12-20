<?php

class TM_Crawler_Model_UrlFactory_Category extends TM_Crawler_Model_UrlFactory_Catalog
{
    protected function _construct()
    {
        $this->_init('catalog/category', 'entity_id');
    }

    protected function _prepareSelect()
    {
        $storeId = $this->_storeId;
        /* @var $store Mage_Core_Model_Store */
        $store = Mage::app()->getStore($storeId);
        if (!$store) {
            return false;
        }
        $this->_select = $this->_getWriteAdapter()->select()
            ->from($this->getMainTable())
            ->where($this->getIdFieldName() . '=?', $store->getRootCategoryId());
        $categoryRow = $this->_getWriteAdapter()->fetchRow($this->_select);
        if (!$categoryRow) {
            return false;
        }
        $this->_select = $this->_getWriteAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()), array($this->getIdFieldName()))
            ->where('main_table.path LIKE ?', $categoryRow['path'] . '/%');

        // joins from Mage_Catalog_Helper_Category_Url_Rewrite::joinTableToSelect
        // magento 1.7 compatibility
        $this->_select->joinLeft(
            array('url_rewrite' => $this->getTable('core/url_rewrite')),
            'url_rewrite.category_id=main_table.entity_id AND url_rewrite.is_system=1 AND ' .
                $this->getReadConnection()->quoteInto('url_rewrite.store_id = ? AND ', (int)$storeId) .
                $this->getReadConnection()->prepareSqlCondition('url_rewrite.id_path', array('like' => 'category/%')),
            array('request_path' => 'url_rewrite.request_path')
        );

        $this->_addFilter($storeId, 'is_active', 1);

        $this->_select
            ->limit($this->_limit, $this->_offset)
            ->order('main_table.entity_id');

        return $this->_select;
    }

    /**
     * Loads category attribute by given attribute code.
     *
     * @param string $attributeCode
     * @return Mage_Sitemap_Model_Resource_Catalog_Abstract
     */
    protected function _loadAttribute($attributeCode)
    {
        $attribute = Mage::getSingleton('catalog/category')->getResource()->getAttribute($attributeCode);
        $this->_attributesCache[$attributeCode] = array(
            'entity_type_id' => $attribute->getEntityTypeId(),
            'attribute_id'   => $attribute->getId(),
            'table'          => $attribute->getBackend()->getTable(),
            'is_global'      => $attribute->getIsGlobal(),
            'backend_type'   => $attribute->getBackendType()
        );
        return $this;
    }

    /**
     * Retrieve entity url
     *
     * @param array $row
     * @param Varien_Object $entity
     * @return string
     */
    protected function _getEntityUrl($row, $entity)
    {
        return !empty($row['request_path']) ? $row['request_path'] : 'catalog/category/view/id/' . $entity->getId();
    }
}
