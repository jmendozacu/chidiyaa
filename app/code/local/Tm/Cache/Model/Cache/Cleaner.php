<?php

class TM_Cache_Model_Cache_Cleaner
{
    public function clean($tags)
    {
        if (Mage::getStoreConfigFlag('tmcache/general/automatic_clean')) {
            Mage::getSingleton('tmcache/cache')->clean($tags);
        } else {
            Mage::getSingleton('tmcache/cache')->invalidateType('tmcache');
        }
    }

    public function onSaveAfter($observer)
    {
        $tags = Mage::helper('tmcache/tag')->getObjectTags($observer->getObject());
        if (false === $tags) {
            return;
        }
        $this->clean($tags);
    }

    public function onDeleteBefore($observer)
    {
        $tags = Mage::helper('tmcache/tag')->getObjectTags($observer->getObject());
        if (!is_array($tags)) {
            return;
        }
        Mage::unregister('tmcache_tags_to_clean'); // mass action fix
        Mage::register('tmcache_tags_to_clean', $tags);
    }

    public function onDeleteAfter($observer)
    {
        $tags = Mage::registry('tmcache_tags_to_clean');
        if (!$tags) {
            return;
        }
        $this->clean($tags);
    }

    public function onCatalogRuleApply($observer)
    {
        $tags = array();

        $productCondition = $observer->getEvent()->getProductCondition();
        if ($productCondition) {
            $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
            $ids = $adapter->query($productCondition->getIdsSelect($adapter))->fetchAll();
            if ($ids) {
                $collection = Mage::getResourceModel('catalog/product_collection')
                    ->addIdFilter($ids)
                    ->load(); // collection should be loaded for addCategoryIds method
                $collection->addCategoryIds();
                $tags = Mage::helper('tmcache/tag')->getProductCollectionTags($collection);
            }
        }

        if (!$tags && ($product = $observer->getEvent()->getProduct())) {
            if ($product instanceof Mage_Catalog_Model_Product) {
                $tags = Mage::helper('tmcache/tag')->getProductTags($product);
            } else {
                $product = Mage::getModel('catalog/product')->load($product);
                if ($product) {
                    $tags = Mage::helper('tmcache/tag')->getProductTags($product);
                }
            }
        }

        if (!$tags) {
            return;
            // $tags = array(TM_Cache_Model_Cache::TAG);
        }
        $this->clean($tags);
    }

    public function onCategoryMove($observer)
    {
        $this->clean(Mage::helper('tmcache/tag')->getCategoryTags($observer->getCategory()));
    }

    /**
     * controller_action_postdispatch_adminhtml_catalog_product_action_attribute_save
     * controller_action_postdispatch_adminhtml_catalog_product_massStatus
     */
    public function cleanProductsCache($observer)
    {
        $ids = Mage::helper('adminhtml/catalog_product_edit_action_attribute')->getProductIds();
        if (!$ids) {
            $ids = $observer->getControllerAction()->getRequest()->getParam('product');
        }
        if (!is_array($ids)) {
            return;
        }

        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addIdFilter($ids)
            ->load(); // collection should be loaded for addCategoryIds method
        $collection->addCategoryIds();
        $this->clean(Mage::helper('tmcache/tag')->getProductCollectionTags($collection));
    }

    public function cleanOrdersCache($observer)
    {
        //
    }

    public function onConfigSave($observer)
    {
        $this->clean(array(TM_Cache_Model_Cache::TAG));
    }

//    public function cleanReviewCache($observer)
//    {
//        $review   = $observer->getDataObject();
//        $origData = $review->getOrigData();
//        if ($origData && !empty($origData['status_id'])) {
//            if ($origData['status_id'] > 1 && $review->getStatusId() > 1) {
//                return; // not approved before and after save
//            }
//        } elseif (!$review->isApproved()) {
//            return; // new pending review
//        }

//        $this->clean(Mage::helper('tmcache/tag')->getProductReviewTags($review));
//    }
}
