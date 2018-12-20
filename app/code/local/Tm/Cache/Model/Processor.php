<?php

class TM_Cache_Model_Processor
{
    /**
     * @return TM_Cache_Model_Filter
     */
    public function getFilter()
    {
        return Mage::getSingleton('tmcache/filter');
    }

    /**
     * @return TM_Cache_Model_Cache
     */
    public function getCache()
    {
        return Mage::getSingleton('tmcache/cache');
    }

    /**
     * Load page output from cache if possible
     *
     * @param $observer
     */
    public function processRequest($observer)
    {
        $controller = $observer->getControllerAction();
        $helper     = Mage::helper('tmcache');
        if (!$helper->canUseCache($controller->getRequest())) {
            return;
        }

        $cacheKeyParams = $helper->getCacheKeyParams($controller->getRequest());
        $cacheKey = $helper->getCacheKey($cacheKeyParams);
        if (Mage::helper('tmcrawler')->getShouldCleanCache()) {
            $this->getCache()->remove($cacheKey);
            return;
        }
        $output = $this->getCache()->load($cacheKey);
        if (!$output) {
            return;
        }

        Mage::helper('tmcache/registry')->registerObjects($controller->getRequest());
        Mage::register('tmcache_hit', 1);
        Mage::getSingleton('tmcache/eventDispatcher')
            ->dispatchEvent(
                'controller_action_predispatch',
                array('controller_action' => $controller)
            );
        // compilation mode compatibility
        if (version_compare($helper->getMagentoVersion(), '1.7.0.0') < 0) {
            Varien_Autoload::registerScope($controller->getRequest()->getRouteName());
        }

        $controller->getResponse()->setBody(
            $this->getFilter()->setController($controller)->filter($output)
        );
        $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        $controller->getRequest()->setDispatched(true);

        Mage::dispatchEvent('controller_action_postdispatch', array('controller_action'=>$controller));
    }

    /**
     * Saves the output to cache if needed
     *
     * @param $observer
     */
    public function processResponse($observer)
    {
        $helper     = Mage::helper('tmcache');
        $controller = $observer->getControllerAction();
        $cacheKeyParams = $helper->getCacheKeyParams($controller->getRequest());
        $cacheKey       = $helper->getCacheKey($cacheKeyParams);

        if (Mage::registry('tmcache_hit')) {
            Mage::dispatchEvent('tmcache_cache_hit', array(
                'request'          => $controller->getRequest(),
                'cache_key_params' => $cacheKeyParams,
                'cache_key'        => $cacheKey
            ));
            return;
        }

        if (!$helper->canUseCache($controller->getRequest())) {
            return;
        }

        /**
         * @var TM_Cache_Model_Filter
         */
        $filter = $this->getFilter();
        $output = $controller->getResponse()->getBody();
        if (empty($output)) {
            return;
        }
        $controller->getResponse()->setBody($filter->filter($output));

        if (!$helper->canSaveCache($controller->getResponse(), $controller->getRequest())) {
            return;
        }

        // miss is moved here to prevent false misses registering. see the returns above
        Mage::dispatchEvent('tmcache_cache_miss', array(
            'request'          => $controller->getRequest(),
            'cache_key_params' => $cacheKeyParams,
            'cache_key'        => $cacheKey
        ));

        $layout = $controller->getLayout();
        $this->getCache()->save(
            implode('', array(
                $filter->replaceDynamicPartsWithPlaceholder($output),
                $filter->getLayoutHandlesPlaceholder($layout->getUpdate()->getHandles()),
                $filter->getLayoutUpdatesPlaceholder(Mage::getSingleton('tmcache/observer')->getLayoutUpdates()),
                $filter->getMessageStoragePlaceholder($layout->getMessagesBlock()),
                $filter->getStaticMessages($controller->getRequest())
            )),
            $cacheKey,
            Mage::helper('tmcache/tag')->generateTagsByRequest($controller->getRequest()),
            $helper->getCacheLifetimeByRequest($controller->getRequest())
        );
    }

    /**
     * Set placeholder instead of the dynamic block content
     */
    public function processBlockHtml($observer)
    {
        /**
         * @var Mage_Core_Block_Abstract
         */
        $block = $observer->getBlock();
        if (false === $block->getUsePlaceholder()) {
            return $this;
        }

        $helper = Mage::helper('tmcache');
        if (!$helper->canUseCache($block->getRequest())) {
            return $this;
        }

        // process only dynamic blocks. All parent blocks should be static
        if ($helper->isDynamicBlock($block)
            && $helper->isStaticBlock($block->getParentBlock())) {

            $filter = $this->getFilter();
            $placeholder = $filter->getBlockPlaceholder($block);
            $filter->addPlaceholderOutput(
                $placeholder,
                $observer->getTransport()->getHtml()
            );

            // replace html output with placeholder to store in cache
            $observer->getTransport()->setHtml($placeholder);
        }
    }
}
