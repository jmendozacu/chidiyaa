<?php

class TM_Cache_Model_Cache
{
    const TAG = 'TM_CACHE';

    protected static $_cache = null;

    protected static $_isMagentoCacheInstanceUsed;

    /**
     * Retrieve system cache model
     *
     * @return Mage_Core_Model_Cache
     */
    public function getCacheInstance()
    {
        if (null === self::$_cache) {
            $this->_initCache();
        }
        return self::$_cache;
    }

    public function load($id)
    {
        return $this->getCacheInstance()->load($id);
    }

    public function save($data, $id, $tags = array(), $lifeTime = false)
    {
        $tags[] = self::TAG;
        return $this->getCacheInstance()->save($data, $id, $tags, $lifeTime);
    }

    public function remove($id)
    {
        return $this->getCacheInstance()->remove($id);
    }

    public function clean($tags=array())
    {
        return $this->getCacheInstance()->clean($tags);
    }

    public function invalidateType($typeCode)
    {
        return $this->getCacheInstance()->invalidateType($typeCode);
    }

    public function isMagentoCacheInstanceUsed()
    {
        $this->getCacheInstance();
        return self::$_isMagentoCacheInstanceUsed;
    }

    protected function _initCache()
    {
        $node = Mage::getConfig()->getNode('global/tmcache');
        if (!$node) {
            self::$_cache = Mage::app()->getCacheInstance();
            self::$_isMagentoCacheInstanceUsed = true;
        } else {
            $options = $node->asArray();
            if (!empty($options['backend_options']['cache_dir'])) {
                $options['backend_options']['cache_dir'] = Mage::getBaseDir('var') . DS
                    . $options['backend_options']['cache_dir'];

                Mage::app()->getConfig()->getOptions()->createDirIfNotExists($options['backend_options']['cache_dir']);
            }

            if (!empty($options['slow_backend_options']['cache_dir'])) {
                $options['slow_backend_options']['cache_dir'] = Mage::getBaseDir('var') . DS
                    . $options['slow_backend_options']['cache_dir'];

                Mage::app()->getConfig()->getOptions()->createDirIfNotExists($options['slow_backend_options']['cache_dir']);
            }

            self::$_cache = Mage::getModel('core/cache', $options);
            self::$_isMagentoCacheInstanceUsed = false;
        }
    }
}
