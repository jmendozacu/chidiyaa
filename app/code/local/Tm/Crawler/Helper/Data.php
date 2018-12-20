<?php

class TM_Crawler_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param  string UserAgent
     * @return int
     */
    public function getCrawlerId($userAgent = null)
    {
        if (null === $userAgent) {
            $userAgent = Mage::helper('core/http')->getHttpUserAgent();
        }

        if (!$this->isCrawler($userAgent)) {
            return 0;
        }

        $crawlerRegexp = '/' . trim(Mage::getStoreConfig('tmcrawler/user_agent/regexp'), '/') . '/';
        preg_match($crawlerRegexp, $userAgent, $matches);
        $crawlerId = 0;
        if (isset($matches[1])) {
            $crawlerId = $matches[1];
        }
        return $crawlerId;
    }

    /**
     * [getShouldCleanCache description]
     * @param  string $userAgent [description]
     * @return boolean
     */
    public function getShouldCleanCache($userAgent = null)
    {
        if (null === $userAgent) {
            $userAgent = Mage::helper('core/http')->getHttpUserAgent();
        }

        if (!$this->isCrawler($userAgent)) {
            return false;
        }

        $preserveCacheAgent = Mage::getStoreConfig('tmcrawler/user_agent/preserve_cache_value');
        if (false === strpos($userAgent, $preserveCacheAgent)) {
            return true;
        }
        return false;
    }

    /**
     * @param  string  $userAgent [description]
     * @return boolean
     */
    public function isCrawler($userAgent = null)
    {
        if (null === $userAgent) {
            $userAgent = Mage::helper('core/http')->getHttpUserAgent();
        }
        $crawlerAgent = Mage::getStoreConfig('tmcrawler/user_agent/value');
        if (false === strpos($userAgent, $crawlerAgent)) {
            return false;
        }
        return true;
    }
}
