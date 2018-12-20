<?php

class TM_Cache_Block_Adminhtml_Dashboard_Benchmark extends Mage_Adminhtml_Block_Dashboard_Grid
{
    protected function _prepareCollection()
    {
        $records = array(
            array(
                'title'    => Mage::helper('tmcache')->__('TM Cache'),
                'instance' => Mage::getSingleton('tmcache/cache')->getCacheInstance(),
                'tag'      => 'TM_CACHE'
            ),
            array(
                'title'    => Mage::helper('tmcache')->__('Magento Cache'),
                'instance' => Mage::app()->getCacheInstance(),
                'tag'      => 'MAGE'
            )
        );

        $collection   = new Varien_Data_Collection();
        $timerName    = 'cache_ids_matching_tags';
        $tmCacheCount = 0;
        foreach ($records as $record) {
            $cache    = $record['instance'];
            $frontend = $cache->getFrontend();
            $ids      = array();

            Varien_Profiler::enable();
            Varien_Profiler::reset($timerName);
            Varien_Profiler::start($timerName);
            try {
                $count = count($frontend->getIdsMatchingTags(array($record['tag'])));
            } catch (Exception $e) {
                $count = 0;
            }
            Varien_Profiler::stop($timerName);
            Varien_Profiler::disable();

            $getIdsTime = Varien_Profiler::fetch($timerName);

            if ('TM_CACHE' === $record['tag']) {
                $tmCacheCount = $count;
            } elseif ('MAGE' === $record['tag']
                && Mage::getSingleton('tmcache/cache')->isMagentoCacheInstanceUsed()) {

                $count -= $tmCacheCount;
            }

            $collection->addItem(new Varien_Object(array(
                'title'        => $record['title'],
                'count'        => $count,
                'backend'      => get_class($frontend->getBackend()),
                'get_ids_time' => number_format($getIdsTime, 4)
            )));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header' => Mage::helper('tmcache')->__('Cache'),
            'index'  => 'title',
            'sortable' => false
        ));
        $this->addColumn('backend', array(
            'header' => Mage::helper('catalog')->__('Backend Model'),
            'index'  => 'backend',
            'sortable' => false
        ));
        $this->addColumn('count', array(
            'header' => Mage::helper('tmcache')->__('Records Count'),
            'index'  => 'count',
            'sortable' => false
        ));
        $this->addColumn('get_ids_time', array(
            'header' => Mage::helper('tmcache')->__('getIdsMatchingTags time, seconds'),
            'index'  => 'get_ids_time',
            'sortable' => false
        ));

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return false;
    }

    public function getRowUrl($row)
    {
        return false;
    }
}
