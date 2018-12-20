<?php

class TM_Crawler_Model_UrlFactory_Cms extends TM_Crawler_Model_UrlFactory_Abstract
{
    protected function _construct()
    {
        $this->_init('cms/page', 'page_id');
    }

    protected function _loadUrls()
    {
        $urls = parent::_loadUrls();
        $urls[] = ''; // emty string - is the homepage
        return $urls;
    }

    /**
     * Retrieve cms page urls array
     *
     * @param int $storeId
     * @return array
     */
    protected function _prepareSelect()
    {
        $storeId = $this->_storeId;
        $this->_select = $this->_getWriteAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()), array($this->getIdFieldName(), 'identifier AS url'))
            ->join(
                array('store_table' => $this->getTable('cms/page_store')),
                'main_table.page_id=store_table.page_id',
                array()
            )
            ->where('main_table.is_active=1')
            ->where('store_table.store_id IN(?)', array(0, $storeId));

        $this->_select
            ->limit($this->_limit, $this->_offset)
            ->order('main_table.page_id');

        return $this->_select;
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
        if ($row['url'] == Mage_Cms_Model_Page::NOROUTE_PAGE_ID) {
            return false;
        }
        return $row['url'];
    }
}
