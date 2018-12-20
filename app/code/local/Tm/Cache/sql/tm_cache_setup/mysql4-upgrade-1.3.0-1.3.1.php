<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$typeText = defined('Varien_Db_Ddl_Table::TYPE_TEXT')
    ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR;

$installer->getConnection()
    ->addColumn(
        $installer->getTable('tmcache/log'),
        'cache_id',
        array(
            'type'     => $typeText,
            'length'   => 32, // @see TM_Cache_Helper_Data::getCacheKey
            'nullable' => false,
            'default'  => '',
            'comment'  => 'Cache ID'
        )
    );

$installer->endSetup();
