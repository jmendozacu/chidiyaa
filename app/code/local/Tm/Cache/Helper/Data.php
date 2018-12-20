<?php

class TM_Cache_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_canUseCache = null;

    protected $_cacheKeyParams = null;

    public function isEnabled()
    {
        return Mage::app()->useCache('tmcache');
    }

    /**
     * Checks is the cache could be used for current request
     *
     * @param Mage_Core_Controller_Request_Http $request
     */
    public function canUseCache($request)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (null !== $this->_canUseCache) {
            return $this->_canUseCache;
        }

        $this->_canUseCache = false;
        if ((!$request->isGet() && !$request->isHead())
            || $this->hasDynamicParams($request)) {

            return $this->_canUseCache;
        }

        // do not use cache if inline translate or debug is on and ip is among allowed ips
        $isDeveloperMode = $this->isDeveloperMode(
            Mage::helper('core/http')->getRemoteAddr(),
            Mage::helper('core/http')->getHttpHost()
        );
        if ($isDeveloperMode) {
            return $this->_canUseCache;
        }

        $paramKeys = array('module', 'controller', 'action');
        $url = array_combine($paramKeys, array(
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName()
        ));

        $rules = array(
            'disabled' => $this->getDisabledPages(),
            'enabled'  => $this->getEnabledPages()
        );
        $weight = array(
            'disabled' => 0,
            'enabled'  => 0
        );
        foreach ($rules as $type => $exceptions) {
            foreach ($exceptions as $exception) {
                $exception = explode('/', $exception);
                if (count($exception) !== 3) {
                    continue;
                }

                $exception = array_combine($paramKeys, $exception);
                $matches   = 0;
                foreach ($exception as $key => $value) {
                    if ($value === '*') {
                        $matches++;
                    } elseif ($url[$key] === $value) {
                        $matches += 2; // module/*/* has more weight than */*/* and so on
                    } else {
                        $matches = 0;
                        break;
                    }
                }

                if ($matches > $weight[$type]) {
                    $weight[$type] = $matches;
                }
            }
        }
        if ($weight['enabled'] > $weight['disabled']) {
            $this->_canUseCache = true;
        } else if ($weight['disabled']) {
            $this->_canUseCache = false;
        }

        return $this->_canUseCache;
    }

    /**
     * Compare client ip with developer configuration options
     *
     * @param string $ip
     * @param string $host
     * @return boolean
     */
    protected function isDeveloperMode($ip, $host)
    {
        $rules = array(
            'dev/restrict/allow_ips' => array(
                'dev/debug/profiler',
                'dev/debug/template_hints',
                // 'dev/debug/template_hints_blocks', commented because it works only if dev/debug/template_hints is enabled
                'dev/translate_inline/active'
            )
        );
        $isDeveloperMode = false;
        foreach ($rules as $ipFieldPath => $configPaths) {
            $ips = Mage::getStoreConfig($ipFieldPath);
            if (!empty($ips)) {
                $ips = preg_split('#\s*,\s*#', $ips, null, PREG_SPLIT_NO_EMPTY);
                if (array_search($ip, $ips) === false && array_search($host, $ips) === false) {
                    continue;
                }
            }
            foreach ($configPaths as $path) {
                if (Mage::getStoreConfigFlag($path)) {
                    return true;
                }
            }
        }
        return $isDeveloperMode;
    }

    /**
     * Checks the request for dynamic parameters
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return boolean
     */
    public function hasDynamicParams($request)
    {
        $uri = $request->getServer('REQUEST_URI');
        if (Mage::registry('m_original_request_uri')) { // mana_pro compatibility
            return true; // do not cache layered navigation to prevent a lot of cache records
            $uri = Mage::registry('m_original_request_uri');
        }

        $dynamicParams = $this->getDynamicParams();
        $result = false;
        foreach ($dynamicParams as $param) {
            if (false !== strpos($uri, $param)) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * Checks is the cache could be used for current response
     *
     * @param Mage_Core_Controller_Response_Http $response
     */
    public function canSaveCache($response, $request)
    {
        if ($response->getHttpResponseCode() !== 200) {
            return false;
        }

        $ignoredParams = $this->getIgnoredUrlParams();
        $requestParams = array_keys($request->getParams());
        if (array_intersect($requestParams, $ignoredParams)) {
            return false;
        }

        $headers = $response->getHeaders();
        $serializedHeaders = array();
        foreach ($headers as $header) {
            $serializedHeaders[] = $header['name'] . '|' . $header['value'];
        }
        $currentDynamicHeaders = array_intersect(
            $this->getDynamicHeaders(),
            $serializedHeaders
        );
        return empty($currentDynamicHeaders);
    }

    /**
     * Retrieve cache key for current request parameters
     *
     * @param  mixed $request Request or parameters from getCacheKeyParams method
     * @return string
     */
    public function getCacheKey($request)
    {
        if (is_array($request)) {
            $params = $request;
        } else {
            $params = $this->getCacheKeyParams($request);
        }
        return md5(implode('_', $params));
    }

    /**
     * Retrieve array of cache key parameters.
     *
     * @param  Mage_Core_Controller_Request_Http $request
     * @return array
     */
    public function getCacheKeyParams($request)
    {
        if (null === $this->_cacheKeyParams) {
            $store   = Mage::app()->getStore();
            $design  = Mage::getDesign();
            $session = Mage::getSingleton('customer/session');

            $params = new Varien_Object(array(
                'prefix'            => 'TM_CACHE',
                'full_action_name'  => $request->getModuleName()
                                        . '_' . $request->getControllerName()
                                        . '_' . $request->getActionName(),
                'is_ajax'           => (int) $request->isXmlHttpRequest(),
                'is_secure'         => (int) $store->isCurrentlySecure(),
                'store_id'          => $store->getId(),
                'currency'          => $store->getCurrentCurrency()->getCode(),
                'package'           => $design->getPackageName(),
                'theme'             => $design->getTheme('template'),
                'customer_group_id' => (int) $session->getCustomerGroupId(),
                'is_logged_in'      => (int) $session->isLoggedIn(), // fix for persitent cart
                'request_params'    => $this->_getSerializedRequestParams($request),
                'cookies'           => $this->_getSerializedCookieParams(),
                'request_uri'       => $this->_getRequestUriParams($request)
            ));
            Mage::dispatchEvent('tm_cache_prepare_cache_key', array(
                'params' => $params,
                'request' => $request
            ));
            $this->_cacheKeyParams = $params->getData();
        }
        return $this->_cacheKeyParams;
    }

    /**
     * Detects most suitable lifetime rule for received $request
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return integer|null => infinite cache lifetime
     */
    public function getCacheLifetimeByRequest($request)
    {
        $paramKeys  = array('module', 'controller', 'action');
        $currentUrl = array_combine($paramKeys, array(
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName()
        ));

        $rules = $this->getLifetimeRules();
        $perfectMatch = array(
            'url'    => '*/*/*',
            'weight' => 3
        );
        foreach ($rules as $url => $lifetime) {
            $urlParts = explode('/', $url);
            if (count($urlParts) !== 3) {
                continue;
            }

            $urlParts = array_combine($paramKeys, $urlParts);
            $weight = 0;
            foreach ($urlParts as $key => $value) {
                if ($value === '*') {
                    $weight++;
                } elseif ($currentUrl[$key] === $value) {
                    $weight += 2; // module/*/* has more weight than */*/* and so on
                } else {
                    $weight = 0;
                    break;
                }
            }

            /**
             * Match is better when:
             *  1. Url weight is higher
             *  2. Url weight is the same, but lifetime is shorter
             */
            if ($weight > $perfectMatch['weight']
                || ($weight == $perfectMatch['weight']
                    && $rules[$url] < $rules[$perfectMatch['url']])) {

                $perfectMatch = array(
                    'url'    => $url,
                    'weight' => $weight
                );
            }

            if ($weight === 6) {
                break; // perfect match by module/controller/action
            }
        }
        if (0 === $rules[$perfectMatch['url']]) {
            return null; // infinite cache lifetime
        }
        return $rules[$perfectMatch['url']];
    }

    /**
     * Retreive per-page lifetime rules
     *
     * @return array
     */
    public function getLifetimeRules()
    {
        $rawRules = Mage::getStoreConfig('tmcache/general/lifetime');
        $rawRules = unserialize($rawRules);
        $rules    = array();
        if (is_array($rawRules)) {
            foreach ($rawRules as $rawRule) {
                $rules[$rawRule['url']] = (int)$rawRule['lifetime'];
            }
        }
        if (!isset($rules['*/*/*'])) {
            $rules['*/*/*'] = (int)Mage::getStoreConfig('tmcache/cache/lifetime');
        }
        return $rules;
    }

    /**
     * Retreive cookie values, that affects page output
     *
     * @return string
     */
    protected function _getSerializedCookieParams()
    {
        $params = array('user_allowed_save_cookie' => 1);
        if (Mage::getStoreConfig('web/cookie/cookie_restriction') // since Magento 1.7
            && Mage::helper('core/cookie')->isUserNotAllowSaveCookie()) {

            $params['user_allowed_save_cookie'] = 0;
        }

        $cookieModel   = Mage::getSingleton('core/cookie');
        $allowedFormat = array('bool', 'boolean', 'raw');
        $cookieNames   = $this->getCookieNames();
        foreach ($cookieNames as $name) {
            $format = 'raw';
            if (false !== strpos($name, '|')) {
                list($name, $format) = explode('|', $name);
                if (!in_array($format, $allowedFormat)) {
                    $format = 'raw';
                }
            }

            $value = $cookieModel->get($name);
            switch ($format) {
                case 'bool':
                case 'boolean':
                    $value = (bool)$value;
                    break;
                default:
                    // no action
                    break;
            }
            $params[$name] = $value;
        }

        return http_build_query($params);
    }

    /**
     * Retrieve cookie names, that affects page output
     *
     * @return array
     */
    public function getCookieNames()
    {
        return $this->_getMergedWithConfig('tmcache/extra/cookies', array());
    }

    /**
     * Retrieve filtered request uri string. Ignored params will be dropped
     * from the string.
     *
     * @param  Mage_Core_Controller_Request_Http $request
     * @return string
     */
    protected function _getRequestUriParams($request)
    {
        $uri = trim($request->getServer('REQUEST_URI'), '/ ');
        if (false === strpos($uri, '?')) {
            return $uri;
        }
        list($uri, $params) = explode('?', $uri);
        $params         = explode('&', $params);
        $ignoredParams  = $this->getIgnoredUrlParams();
        $filteredParams = array();
        foreach ($params as $i => $param) {
            $parts    = explode('=', $param);
            $parts[0] = urldecode(str_replace('%20', '_', $parts[0]));
            if (0 === strlen($parts[0]) || in_array($parts[0], $ignoredParams)) {
                continue;
            }
            $filteredParams[$parts[0]] = isset($parts[1]) ? urldecode($parts[1]) : '';
        }
        if (!count($filteredParams)) {
            return trim($uri, '/ ');
        }
        return $uri . '?' . http_build_query($filteredParams);
    }

    /**
     * Removes ignored params and returns params as string
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return string
     */
    protected function _getSerializedRequestParams($request)
    {
        $params = $request->getParams();
        $params = array_diff_key($params, array_flip($this->getIgnoredUrlParams()));

        // read and add session params that affects page output
        $sessionParams = array(
            array(
                'handles' => array(
                    'catalog_category_view',
                    'catalogsearch_result_index',
                    'catalogsearch_advanced_result',
                    'tag_product_list'
                ),
                'sessions' => array(
                    'catalog/session' => array(
                        'order' => 'sort_order',
                        'dir'   => 'sort_direction',
                        'mode'  => 'display_mode',
                        'limit' => 'limit_page'
                    )
                )
            )
        );
        $currentHandle = $request->getModuleName()
            . '_' . $request->getControllerName()
            . '_' . $request->getActionName();
        foreach ($sessionParams as $rule) {
            if (isset($rule['handles'])) {
                if (!in_array($currentHandle, $rule['handles'])) {
                    continue;
                }
            }
            foreach ($rule['sessions'] as $session => $keys) {
                $session = Mage::getSingleton($session);
                foreach ($keys as $nameInRequest => $nameInSession) {
                    if (isset($params[$nameInRequest])) {
                        $session->setData($nameInSession, $params[$nameInRequest]);
                        continue;
                    }
                    if ($value = $session->getData($nameInSession)) {
                        $params[$nameInRequest] = $value;
                    }
                }
            }
        }

        return http_build_query($params);
    }

    /**
     * Block names that has dynamic methods.
     * The whole content is static, but the separate part is received with dynamic method.
     * Header and getWelcomeMessage for example.
     *
     * @param Varien_Object $block @see TM_Cache_Model_Layout::_generateBlock()
     * @return boolean
     */
    public function hasDynamicMethods($block)
    {
        return in_array($block->getNameInLayout(), array(
            'header',
            'account.links',
            'top.links'
        ));
    }

    /**
     * @param Varien_Object $block @see TM_Cache_Model_Layout::_generateBlock()
     * @return boolean
     */
    public function isDynamicBlock($block)
    {
        if (in_array($block->getType(), $this->getDynamicBlockTypes())
            || ($block->getNameInLayout()
                && in_array($block->getNameInLayout(), $this->getDynamicBlockNames()))) {

            return true;
        }
    }

    /**
     * The block is static if all of parents are static too
     *
     * @param mixed $block Could be null
     * @return boolean
     */
    public function isStaticBlock($block)
    {
        if (!$block) {
            return true;
        }

        $isStatic = !$this->isDynamicBlock($block);
        if ($isStatic) {
            return $this->isStaticBlock($block->getParentBlock());
        }
        return false;
    }

    /**
     * Some magento blocks are used as singletons only
     *
     * @param string $block name
     */
    public function isBlockSingleton($blockName)
    {
        return in_array($blockName, array('messages'));
    }

    /**
     * Blocks that should be replaces with dynamic placeholders
     *
     * @return array
     */
    public function getDynamicBlockTypes()
    {
        $nodes = Mage::app()->getConfig()->getNode('global/tmcache_config/dynamic_block_types');
        $types = array();
        if ($nodes) {
            foreach ($nodes->children() as $node) {
                if (!$node->children()) {
                    continue;
                }
                foreach ($node->children() as $type) {
                    $types[] = (string)$type;
                }
            }
        }

        return $this->_getMergedWithConfig(
            'tmcache/extra/dynamic_block_types',
            $types
        );
    }

    /**
     * @return array
     */
    public function getDynamicBlockNames()
    {
        return $this->_getMergedWithConfig('tmcache/extra/dynamic_block_names', array(
            'top.links'
        ));
    }

    /**
     * All pages are not cacheable by default.
     *
     * @return array
     */
    public function getDisabledPages()
    {
        return $this->_getMergedWithConfig('tmcache/extra/disabled_pages', array(
            'catalog/product/gallery',
            'catalog/product_compare/*',
            'cms/index/noRoute',
            // '*/customer/*',
            'review/customer/*',
            'tag/customer/*',
            'tag/index/save'
        ));
    }

    /**
     * @return array
     */
    public function getEnabledPages()
    {
        return $this->_getMergedWithConfig('tmcache/extra/enabled_pages', array(
            'askit/index/index',
            'askit/index/product',
            'askit/index/category',
            'askit/index/page',
            'attributepages/page/view',
            'catalog/*/*',
            'cms/*/*',
            'contacts/*/*',
            'highlight/*/*',
            'quickshopping/*/*',
            'review/*/*',
            'tag/*/*'
        ));
    }

    public function getIgnoredUrlParams()
    {
        return $this->_getMergedWithConfig('tmcache/extra/ignored_url_params', array(
            '___store',
            '___from_store',
            '___SID',
            'uenc',
            'gclid',
            'utm_source',
            'utm_medium',
            'utm_term',
            'utm_content',
            'utm_campaign'
        ));
    }

    public function getDynamicHeaders()
    {
        return $this->_getMergedWithConfig('tmcache/extra/dynamic_response_headers', array(
            'Http/1.1|404 Not Found',
            'Status|404 File not found'
        ));
    }

    public function getDynamicParams()
    {
        return $this->_getMergedWithConfig('tmcache/extra/dynamic_request_params', array());
    }

    /**
     * @param string $path Config path
     * @param array $data Initial data, that will be merged with parsed config data
     * @param string $delimiter Config data delimiter
     * @param string $trim Symbols that should be trimmed from parsed config value
     * @return array
     */
    protected function _getMergedWithConfig(
        $path, $data, $delimiter = "\n", $trim = "\n\r ", $allowEmpty = false)
    {
        $config = Mage::getStoreConfig($path);
        if (empty($config)) {
            return $data;
        }
        foreach (explode($delimiter, $config) as $line) {
            $value = trim($line, $trim);
            if (!empty($value) || $allowEmpty) {
                $data[] = $value;
            }
        }
        return $data;
    }

    /**
     * Retrive unified Magento version for both CE and EE versions
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        $version = Mage::getVersion();
        if (!Mage::getConfig()->getModuleConfig('Enterprise_Enterprise')) {
            return $version;
        }

        // $mapping = array(
            // '1.13.0.0' => '1.8.0.0',
            // '1.12.0.2' => '1.7.0.2',
            // '1.12.0.0' => '1.7.0.0',
            // '1.11.2.0' => '1.6.2.0',
            // '1.11.1.0' => '1.6.1.0',
            // '1.11.0.0' => '1.6.0.0',
            // '1.10.0.0' => '1.5.0.0'
        // );
        $info = explode('.', $version);
        $info[1] -= 5;
        $version = implode('.', $info);

        return $version;
    }
}
