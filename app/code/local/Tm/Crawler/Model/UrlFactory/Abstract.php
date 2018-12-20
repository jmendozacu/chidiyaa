<?php

abstract class TM_Crawler_Model_UrlFactory_Abstract extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Collection Zend Db select
     *
     * @var Zend_Db_Select
     */
    protected $_select;

    protected $_storeId;
    protected $_limit;
    protected $_offset;

    public function getUrls()
    {
        $select = $this->_prepareSelect();
        if (!$select) {
            return array();
        }
        $urls = $this->_loadUrls();
        return $urls;
    }

    /**
     * Prepare catalog object
     *
     * @param array $row
     * @return Varien_Object
     */
    protected function _prepareObject(array $row)
    {
        $entity = new Varien_Object();
        $entity->setId($row[$this->getIdFieldName()]);
        $entity->setUrl($this->_getEntityUrl($row, $entity));
        return $entity;
    }

    /**
     * Load and prepare entities
     *
     * @return array
     */
    protected function _loadUrls()
    {
        $urls = array();
        $query = $this->_getWriteAdapter()->query($this->_select);
        while ($row = $query->fetch()) {
            $entity = $this->_prepareObject($row);
            if ($url = $entity->getUrl()) {
                $urls[] = $url;
            }
        }
        return $urls;
    }

    public function setStoreId($id)
    {
        $this->_storeId = $id;
        return $this;
    }

    public function setLimit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    public function setOffset($offset)
    {
        $this->_offset = $offset;
        return $this;
    }

    /**
     * Retrieve entity url
     *
     * @param array $row
     * @param Varien_Object $entity
     * @return string
     */
    abstract protected function _getEntityUrl($row, $entity);

    abstract protected function _prepareSelect();
}