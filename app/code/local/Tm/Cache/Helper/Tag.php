<?php

class TM_Cache_Helper_Tag extends Mage_Core_Helper_Abstract
{
    /**
     * Detects tags for received objects
     *
     * @return array
     */
    public function getObjectTags($object)
    {
        $mapping = array(
            'Mage_Catalog_Model_Category' => 'getCategoryTags',
            'Mage_Catalog_Model_Product'  => 'getProductTags',
            'Cnx_Product_Catalog_Model_Product' => 'getProductTags',
            'Iksanika_Productupdater_Model_Product' => 'getProductTags',
            'Mage_Cms_Model_Page'         => 'getCmsPageTags',
            'Mage_Review_Model_Review'    => 'getProductReviewTags',
            'Mage_Sales_Model_Order'      => 'getOrderTags',
            'Magestore_Inventoryplus_Model_Warehouse_Product' => 'getInventoryplusWarehouseProductTags',
            'Magestore_Simisalestrackingapi_Model_Bestsellers_Order' => 'getOrderTags',
            'Mage_Tag_Model_Tag'          => 'getProductTagTags',
            'Mage_Tag_Model_Tag_Relation' => 'getProductTagTags',
            'TM_AskIt_Model_Item'         => 'getAskitTags',
            'TM_Attributepages_Model_Entity' => 'getAttributepageTags',
            'AW_Productquestions_Model_Productquestions' => 'getProductquestionsTags'
        );
        $class = get_class($object);
        if (!isset($mapping[$class])) {
            return false;
        }
        return $this->{$mapping[$class]}($object);
    }

    public function getCategoryTags($category)
    {
        return array(
            TM_Cache_Model_Cache::TAG, // need to clear all tm_cache, because of top navigation
            'tm_cache_catalog_category_view_' . $category->getId()
        );
    }

    public function getProductTags($product, $includeCategoryTags = true)
    {
        if (is_numeric($product)) {
            $productId = $product;
            $includeCategoryTags = false;
        } else {
            $productId = $product->getId();
        }

        $tags = array(
            'tm_cache_catalog_product_view_' . $productId
        );

        $complexProducts = array(
            'bundle/product_type',
            'catalog/product_type_configurable',
            'catalog/product_type_grouped'
        );
        foreach ($complexProducts as $modelAlias) {
            $productModel = Mage::getModel($modelAlias);
            $childIds = $productModel->getChildrenIds($productId);
            foreach ($childIds as $group) {
                foreach ($group as $childId) {
                    $tags[] = 'tm_cache_catalog_product_view_' . $childId;
                }
            }
            $parentIds = $productModel->getParentIdsByChild($productId);
            foreach ($parentIds as $parentId) {
                $tags[] = 'tm_cache_catalog_product_view_' . $parentId;
            }
        }

        if (Mage::getStoreConfig('quick_shopping/general')) {
            $_tags = array(
                'amlanding',
                'catalog',
                'catalogsearch',
                'catalogsearchadvanced',
                'cms',
                'highlight',
                'splash'
            );
            foreach ($_tags as $_tag) {
                $tags[] = 'tm_cache_quickshopping_product_' . $_tag . '_' . $productId;
            }
        }

        if ($includeCategoryTags && $product->getCategoryIds()) {
            $categories = Mage::getResourceModel('catalog/category_collection')
                ->addFieldToFilter(
                    'entity_id',
                    array(
                        'in' => $product->getCategoryIds()
                    )
                );

            foreach ($categories as $category) {
                $tags[] = 'tm_cache_catalog_category_view_' . $category->getId();
                foreach ($category->getAnchorsAbove() as $id) {
                    $tags[] = 'tm_cache_catalog_category_view_' . $id;
                }
            }
            $tags = array_unique($tags);
        }
        return $tags;
    }

    public function getProductCollectionTags($collection, $includeCategoryTags = true)
    {
        $tags = array();
        foreach ($collection as $product) {
            $tags = array_merge($tags, $this->getProductTags($product, $includeCategoryTags));
        }
        return array_unique($tags);
    }

    public function getCmsPageTags($page)
    {
        $tags = array(
            'tm_cache_cms_page_view_' . $page->getId()
        );
        if (!$storeIds = $page->getStores()) {
            $storeIds = $page->getStoreId();
            if (!$storeIds) {
                $storeIds = array(0);
            }
        }
        if (in_array(0, $storeIds)) {
            $storeIds = array_keys(Mage::app()->getStores(false, false));
        }
        foreach ($storeIds as $storeId) {
            $storeHome = Mage::getStoreConfig('web/default/cms_home_page', $storeId);
            if ($page->getIdentifier() === $storeHome) {
                $tags[] = 'tm_cache_cms_page_view_home_' . $storeId;
            }
        }
        return $tags;
    }

    public function getOrderTags($order)
    {
        $allowedStates = array(
            Mage_Sales_Model_Order::STATE_NEW,
            Mage_Sales_Model_Order::STATE_CANCELED
        );
        if (!in_array($order->getState(), $allowedStates)) {
            return false;
        }

        $items      = $order->getItemsCollection();
        $productIds = $items->getColumnValues('product_id');
        if (!$productIds) {
            return false;
        }

        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addFieldToFilter('entity_id', array('in' => $productIds));

        return $this->getProductCollectionTags($collection);
    }

    public function getProductReviewTags($review)
    {
        if ($this->_isFrontend()
            && $review->getStatusId() != Mage_Review_Model_Review::STATUS_APPROVED) {

            return false;
        }

        $tags = array(
            'tm_cache_review_product_view_' . $review->getId(),
            'tm_cache_review_product_list_' . $review->getEntityPkValue()
        );

        $product = Mage::getModel('catalog/product')->load($review->getEntityPkValue());
        if ($product) {
            $tags = array_merge($tags, $this->getProductTags($product));
        }
        return $tags;
    }

    public function getProductTagTags($tag)
    {
        $tagRelation = false;
        if ($this->_isFrontend()) {
            if ($tag instanceof Mage_Tag_Model_Tag_Relation) {
                $tagRelation = $tag;
                $tag = Mage::getModel('tag/tag')->load($tagRelation->getTagId());

                if ($tag->getId()
                     && $tag->getStatus() != Mage_Tag_Model_Tag::STATUS_APPROVED) {

                    return false;
                }
            } else {
                return false;
            }

        }

        $tags = array(
            'tm_cache_tag_product_list_' . $tag->getId()
        );
        if ($tagRelation) {
            $productIds = array($tagRelation->getProductId());
        } else {
            $collection = Mage::getResourceModel('tag/product_collection')
                ->addTagFilter($tag->getId());
            $productIds = $collection->getColumnValues('entity_id');
        }
        foreach ($productIds as $id) {
            $tags = array_merge($tags, $this->getProductTags($id, false));
        }
        return $tags;
    }

    /**
     * Askit could be saved for product, category or cms page
     *
     * @param TM_AskIt_Model_Item
     * @return array
     */
    public function getAskitTags($askit)
    {
        if ($this->_isFrontend()
            && $askit->getStatus() != TM_AskIt_Model_Status::STATUS_APROVED) {

            return false;
        }

        $tags   = array('tm_cache_askit_index_index');
        $itemId = $askit->getItemId();
        if (!$askit->getItemTypeId()) { // old askit version
            $itemId  = $askit->getProductId();
            $tags[]  = 'tm_cache_askit_index_product_' . $itemId;
            $product = Mage::getModel('catalog/product')->load($itemId);
            if ($product) {
                $tags = array_merge($tags, $this->getProductTags($product, false));
            }
        } else {
            switch ($askit->getItemTypeId()) {
                case TM_AskIt_Model_Item_Type::PRODUCT_ID:
                    $tags[]  = 'tm_cache_askit_index_product_' . $itemId;
                    $product = Mage::getModel('catalog/product')->load($itemId);
                    if ($product) {
                        $tags = array_merge($tags, $this->getProductTags($product, false));
                    }
                    break;

                case TM_AskIt_Model_Item_Type::PRODUCT_CATEGORY_ID:
                    $tags[] = 'tm_cache_askit_index_category_' . $itemId;
                    $tags[] = 'tm_cache_catalog_category_view_' . $itemId;
                    break;

                case TM_AskIt_Model_Item_Type::CMS_PAGE_ID:
                    $tags[] = 'tm_cache_askit_index_page_' . $itemId;
                    $page   = Mage::getModel('cms/page')->load($itemId);
                    if ($page) {
                        $tags = array_merge($tags, $this->getCmsPageTags($page));
                    }
                    break;
            }
        }
        return $tags;
    }

    /**
     * AW_Productquestions
     *
     * @param AW_Productquestions_Model_Productquestions
     * @return array
     */
    public function getProductquestionsTags($question)
    {
        $tags = array(
            'tm_cache_productquestions_index_index',
            'tm_cache_productquestions_index_rss'
        );
        $product = Mage::getModel('catalog/product')->load($question->getProductId());
        if ($product) {
            $tags = array_merge($tags, $this->getProductTags($product, false));
        }
        return $tags;
    }

    /**
     * Magestore_Inventoryplus
     *
     * @param  Magestore_Inventoryplus_Model_Warehouse_Product $warehouseProduct
     * @return array
     */
    public function getInventoryplusWarehouseProductTags($warehouseProduct)
    {
        $product = Mage::getModel('catalog/product')->load($warehouseProduct->getProductId());
        if ($product->getId()) {
            return $this->getProductTags($product);
        }
        return false;
    }

    /**
     * Retrieve tags for specific attributepage
     *
     * @param  TM_Attributepages_Model_Entity $attributepage
     * @return array
     */
    public function getAttributepageTags($attributepage)
    {
        $tags = array(
            'tm_cache_attributepages_page_view_' . $attributepage->getId()
        );

        if ($attributepage->isAttributeBasedPage()) {
            $children = Mage::getResourceModel('attributepages/entity_collection')
                ->addOptionOnlyFilter()
                ->addFieldToFilter('attribute_id', $attributepage->getAttributeId());

            foreach ($children as $child) {
                $tags[] = 'tm_cache_attributepages_page_view_' . $child->getId();
            }
        }
        return $tags;
    }

    /**
     * Generate cache tags for received request
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return array
     */
    public function generateTagsByRequest($request)
    {
        $mapping = array(
            'cms_index_index' => array(
                'handle' => 'cms_page_view',
                'suffix' => 'home_' . Mage::app()->getStore()->getId()
            ),
            'cms_page_view' => $request->getParam('page_id'),
            'default'       => $request->getParam('id', ''),
            'tag_product_list'     => $request->getParam('tagId', ''),
            'askit_index_product'  => $request->getParam('product_id', ''),
            'askit_index_category' => $request->getParam('category_id', ''),
            'askit_index_page'     => $request->getParam('page_id', '')
        );
        $handle = $request->getModuleName()
            . '_' . $request->getControllerName()
            . '_' . $request->getActionName();
        $params = array(
            'tags'   => array(),
            'handle' => $handle,
            'suffix' => ''
        );
        if (!isset($mapping[$handle])) {
            $handle = 'default';
        }
        $rule = $mapping[$handle];
        if (!is_array($rule)) {
            $params['suffix'] = $rule;
        } else {
            foreach ($rule as $key => $value) {
                $params[$key] = $value;
            }
        }

        return array_merge(array(
            'tm_cache_' . $params['handle'],
            'tm_cache_' . $params['handle'] . '_'. $params['suffix']
        ), $params['tags']);
    }

    protected function _isFrontend()
    {
        $design = Mage::getSingleton('core/design_package');
        return ($design->getArea() === Mage_Core_Model_App_Area::AREA_FRONTEND);
    }
}
