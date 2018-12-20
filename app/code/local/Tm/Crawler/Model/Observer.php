<?php

class TM_Crawler_Model_Observer
{
    public function crawl($observer)
    {
        if (!Mage::helper('tmcache')->isEnabled()) {
            return;
        }

        $limit = Mage::getStoreConfig('tmcrawler/cron/max_crawlers');
        $crawlers = Mage::getResourceModel('tmcrawler/crawler_collection')
            // ->addFieldToFilter('status', 1) // it may be disabled from backend but still running by cron
            ->addFieldToFilter('state', TM_Crawler_Model_Crawler::STATE_RUNNING)
            ->setPageSize($limit);
        if ($crawlers->count() >= $limit) {
            return;
        }

        $minInterval = Mage::getStoreConfig('tmcrawler/cron/min_interval');
        $dateTo = Mage::app()->getLocale()->date(null, null, null, false)
            ->sub($minInterval, Zend_Date::HOUR) // filter out crawlers completed less than a hour ago
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $crawlers = Mage::getResourceModel('tmcrawler/crawler_collection')
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('state', array('neq' => TM_Crawler_Model_Crawler::STATE_RUNNING))
            ->addFieldToFilter('completed_at', array('or' => array(
                0 => array('date' => true, 'to' => $dateTo),
                1 => array('is' => new Zend_Db_Expr('null')))
            ))
            ->setOrder('started_at', 'asc'); // finish old crawlers first

        $now = Mage::app()->getLocale()->date(null, null, null, false);
        foreach ($crawlers as $crawler) {
            if ($crawler->getCompletedAt()) {
                // check is it time to restart completed crawler
                $dateOfNextRun = Mage::app()->getLocale()
                    ->date($crawler->getCompletedAt(), Varien_Date::DATETIME_INTERNAL_FORMAT, null, false)
                    ->add((int)$crawler->getInterval(), Zend_Date::HOUR);

                if ($dateOfNextRun->isLater($now)) {
                    continue;
                }
            }
            $crawler->run();
        }
    }

    /**
     * Reset crawler state to pending if the state is running
     * and crawler wasn't saved for more than 30 minutes
     *
     *  Description:
     *      Crawler limit is 20 urls per run. This means that the crawler should
     *      be saved after each 20 urls. 30 minutes is more than enough to
     *      crawl 20 urls.
     *
     * @return void
     */
    public function checkState()
    {
        $dateTo = Mage::app()->getLocale()->date(null, null, null, false)
            ->sub(30, Zend_Date::MINUTE)
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $crawlers = Mage::getResourceModel('tmcrawler/crawler_collection')
            ->addFieldToFilter('state', TM_Crawler_Model_Crawler::STATE_RUNNING)
            ->addFieldToFilter('last_activity_at', array('or' => array(
                0 => array('date' => true, 'to' => $dateTo),
                1 => array('is' => new Zend_Db_Expr('null')))
            ));

        foreach ($crawlers as $crawler) {
            $log = sprintf(
                "Inactive crawler with 'running' state found. Changing state to pending. (%s)",
                $crawler->getIdentifier()
            );
            Mage::log($log, null, 'tm_crawler.log', true);
            $crawler->setState(TM_Crawler_Model_Crawler::STATE_PENDING)->save();
        }
    }

    public function removeOldReports()
    {
        $days  = 5;
        $date  = gmdate('Y-m-d H:i:s', strtotime('now - ' . $days . ' days'));
        $where = array('created_at < ?' => $date);
        return Mage::getResourceModel('tmcrawler/report')->clear($where);
    }
}
