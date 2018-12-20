<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'crawler/crawler'
 */
$typeText = defined('Varien_Db_Ddl_Table::TYPE_TEXT')
    ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR;

$table = $installer->getConnection()
    ->newTable($installer->getTable('tmcrawler/report'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true
        ), 'Entity Id')
    ->addColumn('url', $typeText, 255, array(
        'nullable' => false
        ), 'Url')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false
        ), 'Store Id')
    ->addColumn('currency', $typeText, 3, array(
        'nullable' => false
        ), 'Currency')
    ->addColumn('crawler_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'unsigned' => true
        ), 'Crawler Id')
    ->addColumn('http_user_agent', $typeText, 255, array(
        'nullable' => false
        ), 'Http User Agent')
    ->addColumn('http_code', $typeText, 3, array(
        'nullable' => false
        ), 'Response Http Code')
    ->addColumn('total_time', Varien_Db_Ddl_Table::TYPE_DECIMAL, '5,3', array(
        'nullable' => false
        ), 'Response Time')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
        'default' => null
        ), 'Created At')
    ->addForeignKey($installer->getFkName('tmcrawler/report', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('tmcrawler/report', 'crawler_id', 'tmcrawler/crawler', 'crawler_id'),
        'crawler_id', $installer->getTable('tmcrawler/crawler'), 'crawler_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('TM Crawler Reports');
$installer->getConnection()->createTable($table);

$installer->endSetup();
