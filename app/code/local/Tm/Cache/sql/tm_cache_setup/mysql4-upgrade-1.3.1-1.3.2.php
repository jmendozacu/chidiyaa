<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addIndex(
        $installer->getTable('tmcache/log'),
        $installer->getIdxName('tmcache/log', array('cache_id')),
        array('cache_id')
    );

$installer->endSetup();
