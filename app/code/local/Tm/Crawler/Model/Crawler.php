<?php

if (!@class_exists('RollingCurl')) {
    include_once('joshfraser/rolling-curl/RollingCurl.php');
}

class TM_Crawler_Model_Crawler extends Mage_Core_Model_Abstract
{
    const DELIMITER = ',';

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED  = 1;

    const STATE_NEW       = 'new';
    const STATE_RUNNING   = 'running';
    const STATE_PENDING   = 'pending';
    const STATE_COMPLETED = 'completed';

    const TYPE_CATEGORY = 'category';
    const TYPE_PRODUCT  = 'product';
    const TYPE_CMS      = 'cms';

    /**
     * Session entity
     *
     * @var Mage_Core_Model_Session_Abstract
     */
    protected $_session;

    /**
     * @var TM_Core_Model_Timer
     */
    protected $_timer = null;

    /**
     * @var array
     */
    protected $_log = array();

    protected function _construct()
    {
        $this->_init('tmcrawler/crawler');
    }

    public function _afterSave()
    {
        $this->_log = array();
        return parent::_afterSave();
    }

    public function getTimer()
    {
        if (null === $this->_timer) {
            $this->_timer = Mage::getModel('tmcore/timer', array('name' => 'tm_crawler'));
        }
        return $this->_timer;
    }

    public function run()
    {
        $this->getTimer()->increaseTimeLimitTo(3600);
        $this->getTimer()->startOrResume();

        if (!@class_exists('RollingCurl')) {
            throw new Exception("RollingCurl library doesn't found");
        }

        $this->_beforeRun();

        $store   = Mage::app()->getStore($this->getCurrentStoreId());
        $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        $limit   = (int)$this->getLimit();
        $offset  = (int)$this->getOffset();
        // limit + 1 is used to check completed state without additional query
        $urls    = $this->getUrls($limit + 1, $offset);
        if (count($urls)) {
            $curlOptions = $this->_getCurlOptions();
            $rc = new RollingCurl(array($this, 'requestCallback'));
            $i  = 0;
            foreach ($urls as $url) {
                $i++;
                $rc->get($baseUrl . $url, null, $curlOptions);

                if ($i == $limit) { // limit + 1 fix
                    break;
                }
            }
            $rc->execute($this->getConcurrency());
        }

        $this->_afterRun($urls);
    }

    protected function _getCurlOptions()
    {
        return array(
            CURLOPT_COOKIE         => $this->getCookieString(),
            CURLOPT_HEADER         => 1,
            CURLOPT_NOBODY         => 1,
            CURLOPT_USERAGENT      => $this->getCurrentHttpUserAgent(),
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FOLLOWLOCATION => 0,
            CURLOPT_COOKIEFILE     => 'tm_crawler.txt',
            CURLOPT_COOKIEJAR      => 'tm_crawler.txt'
        );
    }

    public function _beforeRun()
    {
        $now = Mage::app()->getLocale()->date(null, null, null, false)
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        if (in_array($this->getState(), array(self::STATE_NEW, self::STATE_COMPLETED))) {
            $this->clearLog();
            $this->clearReport();
            $this->setStartedAt($now);
            $this->setCompletedAt(null);
            $this->setOffset(0);
            $this->setCrawledUrls(0);
            $this->setIncompletedTypes($this->getType());
            $this->setIncompletedUserAgents($this->getUserAgents());
            $this->setIncompletedStoreIds($this->getStoreIds());
            $this->setIncompletedCurrencies($this->getCurrencies());
        }

        $this->setState(self::STATE_RUNNING);
        $this->setLastActivityAt($now);
        $this->save();
    }

    public function _afterRun($urls)
    {
        $limit = $this->getLimit();
        $urlsCount = count($urls);
        $isCompleted = ($urlsCount <= $limit); // @see (limit + 1) above

        $this->setOffset($this->getOffset() + $limit);
        $this->setCrawledUrls($this->getCrawledUrls() + min($urlsCount, $limit));

        $now = Mage::app()->getLocale()->date(null, null, null, false)
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        if ($isCompleted) {
            $types = explode(self::DELIMITER, $this->getIncompletedTypes());
            unset($types[0]);
            $this->setIncompletedTypes(implode(self::DELIMITER, $types));

            // url types are done, switch to the next currency
            $currencies = explode(self::DELIMITER, $this->getIncompletedCurrencies());
            if (!count($types)) {
                unset($currencies[0]);
                $this->setIncompletedCurrencies(implode(self::DELIMITER, $currencies));
                if (count($currencies)) {
                    $this->setIncompletedTypes($this->getType());
                }
            }

            // user agent simulation
            $userAgents = explode(self::DELIMITER, $this->getIncompletedUserAgents());
            if (!count($types) && !count($currencies) && count($userAgents)) {
                unset($userAgents[0]);
                $this->setIncompletedUserAgents(implode(self::DELIMITER, $userAgents));
                if (count($userAgents)) {
                    $this->setIncompletedTypes($this->getType());
                    $this->setIncompletedCurrencies($this->getCurrencies());
                }
            }

            // urls and currencies are done, switch to the next store
            $storeIds = explode(self::DELIMITER, $this->getIncompletedStoreIds());
            if (!count($types) && !count($currencies) && !count($userAgents)) {
                unset($storeIds[0]);
                $this->setIncompletedStoreIds(implode(self::DELIMITER, $storeIds));
                if (count($storeIds)) {
                    $this->setIncompletedTypes($this->getType());
                    $this->setIncompletedCurrencies($this->getCurrencies());
                    $this->setIncompletedUserAgents($this->getUserAgents());
                }
            }

            if (!count($types) && !count($storeIds) && !count($currencies)) {
                $this->setCompletedAt($now);
                $this->setState(self::STATE_COMPLETED);
            } else {
                $this->setState(self::STATE_PENDING);
            }

            $this->setOffset(0);
        } else {
            $this->setState(self::STATE_PENDING);
        }

        if (self::STATE_PENDING === $this->getState()) {
            if ($this->getTimer()->getElapsedSecs() < $this->getTimeLimit()) {
                $avgResponseTime = $this->getAverageResponseTime();
                if ($avgResponseTime <= $this->getMaxResponseTime()) {
                    return $this->run();
                } else {
                    $log = sprintf(
                        "Average response time is too high. Crawler paused. (%s - %s)",
                        $avgResponseTime,
                        $this->getIdentifier()
                    );
                    Mage::log($log, null, 'tm_crawler.log', true);
                }
            }
        }

        $this->setLastActivityAt($now);
        $this->save();
    }

    public function requestCallback($response, $info, $request)
    {
        $item = array(
            'store_id' => $this->getCurrentStoreId(),
            'currency' => $this->getCurrentCurrency(),
            'crawler_id' => $this->getId(),
            'http_user_agent' => $this->getCurrentHttpUserAgent(),
            'created_at' => Mage::app()->getLocale()->date(null, null, null, false)
                ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
        );
        $additionalInfo = array('url', 'http_code', 'total_time');
        foreach ($additionalInfo as $key) {
            $item[$key] = $info[$key];
        }

        $this->_log[] = $item;
    }

    /**
     * Get items with non 200 response code or with long response time
     *
     * @return array
     */
    public function getItemsToLog()
    {
        if (!$this->_log) {
            return array();
        }

        $items = array();
        $codes = Mage::getStoreConfig('tmcrawler/report/http_codes_to_skip');
        $codes = explode(self::DELIMITER, $codes);
        foreach ($this->_log as $info) {
            if (in_array($info['http_code'], $codes)
                && $info['total_time'] <= $this->getMaxResponseTime()) {

                continue;
            }
            $items[] = $info;
        }
        return $items;
    }

    /**
     * Get average response time of the lat crawled urls
     *
     * @return float
     */
    public function getAverageResponseTime()
    {
        if (!$this->_log) {
            return 0;
        }

        $avgResponseTime = 0;
        foreach ($this->_log as $info) {
            $avgResponseTime += (float) $info['total_time'];
        }
        return $avgResponseTime / count($this->_log);
    }

    public function getUserAgents()
    {
        $agents = trim($this->getData('user_agents'), ', ');
        if (!empty($agents)) {
            $agents = ',' . $agents; // prepend empty user agent to emulate usual agent first
        }
        return $agents;
    }

    /**
     * Clear crawler's log
     *
     * @return number of deleted rows
     */
    public function clearLog()
    {
        return Mage::getResourceModel('tmcache/log')->clear(array(
            'crawler_id = ?' => $this->getId()
        ));
    }

    public function clearReport()
    {
        return Mage::getResourceModel('tmcrawler/report')->clear(array(
            'crawler_id = ?' => $this->getId()
        ));
    }

    /**
     * Retrieve execution time limit in seconds
     *
     * @return int
     */
    public function getTimeLimit()
    {
        return $this->getTimer()->getTimeLimit() / 3;
    }

    public function getCurrentHttpUserAgent()
    {
        $userAgent = Mage::getStoreConfig('tmcrawler/user_agent/value');
        $codes = array(
            $userAgent,
            $userAgent . '_' . $this->getId()
        );
        if (!$this->getCleanCache()) {
            $codes[] = Mage::getStoreConfig('tmcrawler/user_agent/preserve_cache_value');
        }

        $additional = explode(self::DELIMITER, $this->getIncompletedUserAgents());
        if ($additional) {
            $codes[] = $additional[0];
        }

        return implode(';', $codes);
    }

    public function getUrls($limit, $offset)
    {
        return $this->getUrlFactory()
            ->setLimit($limit)
            ->setOffset($offset)
            ->setStoreId($this->getCurrentStoreId())
            ->getUrls();
    }

    public function getUrlFactory()
    {
        return Mage::getModel('tmcrawler/urlFactory_' . $this->getCurrentType());
    }

    public function getCurrentType()
    {
        $type = $this->getData('current_type');
        if (null === $type) {
            $types = explode(self::DELIMITER, $this->getIncompletedTypes());
            $type  = $types[0];
            $this->setCurrentType($type);
        }
        return $type;
    }

    public function getCurrentStoreId()
    {
        $storeId = $this->getData('current_store_id');
        if (null === $storeId) {
            $storeIds = explode(self::DELIMITER, $this->getIncompletedStoreIds());
            if ($storeIds) {
                $storeId = $storeIds[0];
                $this->setCurrentStoreId($storeId);
            }
        }
        return $storeId;
    }

    public function getCurrentCurrency()
    {
        $currency = $this->getData('current_currency');
        if (null === $currency) {
            $currencies = explode(self::DELIMITER, $this->getIncompletedCurrencies());
            if ($currencies) {
                $currency = $currencies[0];
                $this->setCurrentCurrency($currency);
            }
        }
        return $currency;
    }

    public function setIncompletedTypes($types)
    {
        $this->setData('incompleted_types', $types);
        $this->setData('current_type', null);
    }

    /**
     * @param [type] $currencies [description]
     */
    public function setIncompletedStoreIds($ids)
    {
        $this->setData('incompleted_store_ids', $ids);
        $this->setData('current_store_id', null);

        if ($this->getCurrentStoreId()) {
            $store = Mage::app()->getStore($this->getCurrentStoreId());
            $this->_updateCookieString(array(
                "store={$store->getCode()}"
            ));
        }
    }

    /**
     * [setIncompletedCurrencies description]
     * @param [type] $currencies [description]
     */
    public function setIncompletedCurrencies($currencies)
    {
        // crawl currencies that are enabled for current store
        $allowedCurrencies = Mage::getStoreConfig(
            'currency/options/allow', $this->getCurrentStoreId()
        );
        if ($allowedCurrencies) {
            $allowedCurrencies = explode(',', $allowedCurrencies);
            $currencies = explode(self::DELIMITER, $currencies);
            $currencies = array_intersect($currencies, $allowedCurrencies);
            $currencies = implode(self::DELIMITER, $currencies);
        }

        $this->setData('incompleted_currencies', $currencies);
        $this->setData('current_currency', null);

        $this->_switchCurrency();
    }

    public function getCookieString()
    {
        return (string) $this->_getSession()->getTmCrawlerCookieString();
    }

    public function setCookieString($value)
    {
        return $this->_getSession()->setTmCrawlerCookieString($value);
    }

    /**
     * Retrieve store session object
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    protected function _getSession()
    {
        if (!$this->_session) {
            $this->_session = Mage::getModel('core/session')
                ->init('tm_crawler_' . $this->getId());
        }
        return $this->_session;
    }

    protected function _switchCurrency()
    {
        $code = $this->getCurrentCurrency();
        if (!$code) {
            return;
        }
        // @todo if same currency already set - return;
        $url = Mage::helper('directory/url')->getSwitchCurrencyUrl(array('currency' => $code));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_COOKIE, $this->getCookieString());
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->getCurrentHttpUserAgent());
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'tm_crawler.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'tm_crawler.txt');
        $response = curl_exec($ch);
        curl_close($ch);
        $this->_updateCookieString($response);
    }

    /**
     * Call this method every time after session change
     *
     * @param  mixed $headers Response headers
     * @return void
     */
    protected function _updateCookieString($headers)
    {
        if (is_array($headers)) {
            $parsedHeaders['Set-Cookie'] = implode('; ', $headers);
        } else {
            $lines = explode("\r\n", $headers);
            $lines = array_filter($lines);
            $parsedHeaders = array();
            foreach ($lines as $line) {
                if (false === strpos($line, ':')) {
                    continue;
                }
                list($key, $value) = explode(':', $line, 2);
                if (!isset($parsedHeaders[$key])) {
                    $parsedHeaders[$key] = trim($value);
                } else {
                    $parsedHeaders[$key] .= '; ' . trim($value);
                }
            }
        }

        $cookie = $this->getCookieString();

        if (empty($cookie)) {
            if (Mage::getStoreConfig('web/cookie/cookie_restriction')) {
                $restrictionCookie = array();
                foreach (Mage::app()->getWebsites() as $websiteId => $website) {
                    $restrictionCookie[$websiteId] = 1;
                }
                $cookie = Mage_Core_Helper_Cookie::IS_USER_ALLOWED_SAVE_COOKIE.'='.json_encode($restrictionCookie);
            }
        }

        if (!empty($parsedHeaders['Set-Cookie'])) {
            if (empty($cookie)) {
                $cookie = $parsedHeaders['Set-Cookie'];
            } else {
                $cookie .= '; ' . $parsedHeaders['Set-Cookie'];
            }

            // remove duplicates, that may be created by magento
            // - multiple 'Set-Cookie' directives returned in curl
            $parts = explode(';', $cookie);
            $cookie = array();
            foreach ($parts as $keyValue) {
                if (false !== strpos($keyValue, '=')) {
                    list($key, $value) = explode('=', $keyValue);
                    $value = trim($value);
                } else {
                    $key = $keyValue;
                    $value = false;
                }
                $key = trim($key);
                $cookie[$key] = $key;
                if (false !== $value) {
                    $cookie[$key] .= '=' . $value;
                }
            }
            $cookie = implode('; ', $cookie);
        }

        $this->setCookieString($cookie);
        return $cookie;
    }
}
