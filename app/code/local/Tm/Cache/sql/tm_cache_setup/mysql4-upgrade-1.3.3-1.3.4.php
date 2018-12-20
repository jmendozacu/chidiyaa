<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('tmcache/log'),
        'crawler_id',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'default'  => 0,
            'nullable' => false,
            'comment'  => 'Crawler ID'
        )
    );

$installer->getConnection()
    ->addIndex(
        $installer->getTable('tmcache/log'),
        $installer->getIdxName('tmcache/log', array('crawler_id')),
        array('crawler_id')
    );

$installer->endSetup();
