<?php

class TM_Cache_Helper_Registry extends Mage_Core_Helper_Abstract
{
    /**
     * Load product and category objects into Magento registry.
     * This feature provides compatibility with third-party modules that are
     * depends on registry variables and should not be cached
     *
     * This method is called, when cache is hit.
     */
    public function registerObjects($request)
    {
        $this->registerProduct($request);
        $this->registerCategory($request);
    }

    public function registerProduct($request, $force = false)
    {
        if (!$this->_canRegisterProduct($request) && !$force) {
            return;
        }

        if (Mage::registry('product')) {
            return;
        }

        if (!$product = Mage::registry('tmcache_current_product')) {
            $product = Mage::getModel('catalog/product')->load($request->getParam('id'));
            if (!$product->getId()) {
                $product = null;
            }
        }

        if ($product) {
            Mage::register('product', $product);
            Mage::register('current_product', $product);
        }
    }

    public function registerCategory($request, $force = false)
    {
        if (!$this->_canRegisterCategory($request) && !$force) {
            return;
        }

        if (Mage::registry('current_category')) {
            return;
        }

        if (!$category = Mage::registry('tmcache_current_category')) {
            $fullActionName = implode('_', array(
                $request->getModuleName(),
                $request->getControllerName(),
                $request->getActionName(),
            ));

            if ($fullActionName === 'catalog_category_view') {
                $categoryId = $request->getParam('id');
            } elseif ($fullActionName === 'catalog_product_view') {
                $categoryId = $request->getParam('category');
                $product = Mage::registry('product');
                if (!$categoryId && $product) { // "catalog/seo/product_use_categories" is disabled
                    $categoryId = Mage::getSingleton('catalog/session')->getLastVisitedCategoryId();
                    if ($categoryId && !$product->canBeShowInCategory($categoryId)) {
                        $categoryId = null;
                    }
                }
            }

            if ($categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                if (!$category->getId()) {
                    $category = null;
                }
            }
        }

        if ($category) {
            Mage::register('current_category', $category);
        }
    }

    protected function _canRegisterProduct($request)
    {
        $fullActionName = implode('_', array(
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName(),
        ));

        if ('catalog_product_view' !== $fullActionName) {
            return false;
        }

        if (Mage::getStoreConfigFlag('tmcache/registry/product')) {
            return true;
        }

        if (!Mage::getStoreConfigFlag('catalog/seo/product_use_categories')) {
            // is category does not included in url, but category registration
            // is required - register product too.
            return Mage::getStoreConfigFlag('tmcache/registry/category');
        }

        return false;
    }

    protected function _canRegisterCategory($request)
    {
        $fullActionName = implode('_', array(
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName(),
        ));
        $pages = array('catalog_product_view', 'catalog_category_view');
        if (!in_array($fullActionName, $pages)) {
            return false;
        }
        return Mage::getStoreConfigFlag('tmcache/registry/category');
    }
}
