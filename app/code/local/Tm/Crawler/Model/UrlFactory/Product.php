<?php

class TM_Crawler_Model_UrlFactory_Product extends TM_Crawler_Model_UrlFactory_Catalog
{
    protected function _construct()
    {
        $this->_init('catalog/product', 'entity_id');
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
            ->from(array('main_table' => $this->getMainTable()), array($this->getIdFieldName()))
            ->join(
                array('w' => $this->getTable('catalog/product_website')),
                'main_table.entity_id = w.product_id',
                array()
            )
            ->where('w.website_id=?', $store->getWebsiteId());

        // use direct and links with category paths and magento 1.7 compatibility
        // joins from Mage_Catalog_Helper_Product_Url_Rewrite::joinTableToSelect
        $this->_select->joinLeft(
            array('url_rewrite' => $this->getTable('core/url_rewrite')),
            'url_rewrite.product_id = main_table.entity_id AND url_rewrite.is_system = 1 AND ' .
                $this->getReadConnection()->quoteInto('url_rewrite.store_id = ? AND ', (int)$storeId) .
                $this->getReadConnection()->prepareSqlCondition('url_rewrite.id_path', array('like' => 'product/%')),
            array('request_path' => 'url_rewrite.request_path')
        );

        $this->_addFilter($storeId, 'visibility',
            Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds(), 'in'
        );
        $this->_addFilter($storeId, 'status',
            Mage::getSingleton('catalog/product_status')->getVisibleStatusIds(), 'in'
        );

        $this->_select
            ->limit($this->_limit, $this->_offset)
            ->order('main_table.entity_id')
            ->order('url_rewrite.request_path');

        return $this->_select;
    }

    /**
     * Loads product attribute by given attribute code
     *
     * @param string $attributeCode
     * @return Mage_Sitemap_Model_Resource_Catalog_Abstract
     */
    protected function _loadAttribute($attributeCode)
    {
        $attribute = Mage::getSingleton('catalog/product')->getResource()->getAttribute($attributeCode);
        $this->_attributesCache[$attributeCode] = array(
            'entity_type_id' => $attribute->getEntityTypeId(),
            'attribute_id'   => $attribute->getId(),
            'table'          => $attribute->getBackend()->getTable(),
            'is_global'      => $attribute->getIsGlobal() == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
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
        return !empty($row['request_path']) ? $row['request_path'] : 'catalog/product/view/id/' . $entity->getId();
    }
}
