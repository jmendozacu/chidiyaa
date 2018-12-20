<?php

class TM_Cache_Model_Observer
{
    protected $_layoutUpdates = '';

    public function getLayoutUpdates()
    {
        return $this->_layoutUpdates;
    }

    public function addLayoutUpdate($update)
    {
        $this->_layoutUpdates .= $update;
    }

    public function addCatalogCategoryLayoutUpdate($observer)
    {
        $category = $observer->getCategory();
        $design   = Mage::getSingleton('catalog/design');
        $settings = $design->getDesignSettings($category);

        // apply custom layout update once layout is loaded
        if ($layoutUpdates = $settings->getLayoutUpdates()) {
            if (is_array($layoutUpdates)) {
                foreach($layoutUpdates as $layoutUpdate) {
                    $this->addLayoutUpdate($layoutUpdate);
                }
            }
        }
    }

    public function addCatalogProductLayoutUpdate($observer)
    {
        $product  = $observer->getProduct();
        $design   = Mage::getSingleton('catalog/design');
        $settings = $design->getDesignSettings($product);

        // apply custom layout update once layout is loaded
        if ($layoutUpdates = $settings->getLayoutUpdates()) {
            if (is_array($layoutUpdates)) {
                foreach($layoutUpdates as $layoutUpdate) {
                    $this->addLayoutUpdate($layoutUpdate);
                }
            }
        }
    }

    public function addCmsPageLayoutUpdate($observer)
    {
        $page = $observer->getPage();
        $inRange = Mage::app()->getLocale()->isStoreDateInInterval(null, $page->getCustomThemeFrom(), $page->getCustomThemeTo());
        $layoutUpdate = ($page->getCustomLayoutUpdateXml() && $inRange) ? $page->getCustomLayoutUpdateXml() : $page->getLayoutUpdateXml();
        $this->addLayoutUpdate($layoutUpdate);
    }

    public function logHit($observer)
    {
        $observer->setIsHit(1);
        $this->log($observer);
    }

    public function logMiss($observer)
    {
        $observer->setIsHit(0);
        $this->log($observer);
    }

    public function log($observer)
    {
        $params = $observer->getCacheKeyParams();
        unset($params['prefix']);

        $data = array(
            'is_hit'     => (int) $observer->getIsHit(),
            'created_at' => gmdate('Y-m-d H:i:s'),
            'cache_id'   => $observer->getCacheKey(),
            'crawler_id' => Mage::helper('tmcrawler')->getCrawlerId()
        );

        $keys = array('full_action_name', 'store_id', 'customer_group_id');
        foreach ($keys as $key) {
            $data[$key] = $params[$key];
            unset($params[$key]);
        }
        $data['params'] = Mage::helper('core')->jsonEncode($params);

        $record = Mage::getModel('tmcache/log');
        $record->addData($data)->save();
    }

    /**
     * Remove old log records
     *
     * @return Deleted records count
     */
    public function clearLog()
    {
        $days  = (int) Mage::getStoreConfig('tmcache/cron/old_log_records_days');
        $date  = gmdate('Y-m-d H:i:s', strtotime('now - ' . $days . ' days'));
        $where = array('created_at < ?' => $date);
        return Mage::getResourceModel('tmcache/log')->clear($where);
    }

    /**
     * Remove old cache entries with no hits during selected period (2 days by default)
     *
     * @param  bool $force  Use this flag to skip configuration check
     * @return              Deleted records count
     */
    public function removeOldRecordsWithNoHits($force = false)
    {
        if (!$force &&
            !Mage::getStoreConfigFlag('tmcache/cron/old_cache_records_auto_remove')) {

            return 0;
        }

        $days = (int) Mage::getStoreConfig('tmcache/cron/old_cache_records_days');
        $date = gmdate('Y-m-d H:i:s', strtotime('now - ' . $days . ' days'));
        $collection = Mage::getResourceModel('tmcache/log_collection');
        $collection->getSelect()->group('cache_id');
        $collection
            ->addFieldToFilter('last_access', array('to' => $date))
            ->addFieldToFilter('hit_count', array('to' => 0));

        if ($collection->getSize()) {
            $cache = Mage::getSingleton('tmcache/cache');
            $ids   = $collection->getColumnValues('cache_id');
            foreach ($ids as $id) {
                $cache->remove($id);
            }
            Mage::getResourceModel('tmcache/log')->updateMultipleRows(
                array('cache_id' => ''),
                array('cache_id IN (?)' => $ids)
            );
        }
        return $collection->getSize();
    }

    public function addLastCategoryIdToCacheKey($object)
    {
        if (Mage::getStoreConfigFlag('catalog/seo/product_use_categories')) {
            // category_id is available in request params
            return;
        }

        $request = $object->getRequest();
        $fullActionName = implode('_', array(
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName(),
        ));
        if ('catalog_product_view' !== $fullActionName) {
            // last category id affects product page only
            return;
        }

        $product = Mage::getModel('catalog/product')->load($request->getParam('id'));
        if (!$product->getId()) {
            return;
        }
        Mage::register('tmcache_current_product', $product);

        $categoryId = Mage::getSingleton('catalog/session')->getLastVisitedCategoryId();
        if ($categoryId && $product->canBeShowInCategory($categoryId)) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $product->setCategory($category);
            Mage::register('tmcache_current_category', $category);
        } else {
            // set categoryId to null, if product does not belongs to it
            $categoryId = null;
        }

        $params = $object->getParams();
        $params->setLastCategoryId($categoryId);
    }
}
