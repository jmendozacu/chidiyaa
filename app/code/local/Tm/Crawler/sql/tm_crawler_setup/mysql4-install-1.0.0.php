<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'tmcrawler/crawler'
 */
$typeText = defined('Varien_Db_Ddl_Table::TYPE_TEXT')
    ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR;

$table = $installer->getConnection()
    ->newTable($installer->getTable('tmcrawler/crawler'))
    ->addColumn('crawler_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true
        ), 'Crawler Id')
    ->addColumn('identifier', $typeText, 64, array(
        'nullable' => false
        ), 'Identifier')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => 1
        ), 'Status')
    ->addColumn('state', $typeText, 32, array(
        'nullable' => false,
        'default'  => TM_Crawler_Model_Crawler::STATE_NEW
        ), 'State')
    ->addColumn('offset', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => 0
        ), 'Last Offset')
    ->addColumn('limit', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => 20
        ), 'Urls Limit Per Request')
    ->addColumn('crawled_urls', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => 0
        ), 'Crawled Urls Count')
    ->addColumn('started_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
        'default' => null
        ), 'Started At')
    ->addColumn('last_activity_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
        'default' => null
        ), 'Time of last activity')
    ->addColumn('completed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
        'default' => null
        ), 'Completed At')
    ->addColumn('interval', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => 24
        ), 'Interval')
    ->addColumn('concurrency', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false
        ), 'Concurrency')
    ->addColumn('clean_cache', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => 1
        ), 'Clean Cache')
    ->addColumn('max_response_time', Varien_Db_Ddl_Table::TYPE_DECIMAL, '3,1', array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => 10
        ), 'Max Avg. Response Time')
    ->addColumn('type', $typeText, 64, array(
        'default'  => 'category',
        'nullable' => false
        ), 'Type')
    ->addColumn('incompleted_types', $typeText, 64, array(
        'default'  => '',
        'nullable' => false
        ), 'Incompleted Types')
    ->addColumn('store_ids', $typeText, 64, array(
        'nullable' => false
        ), 'Stores')
    ->addColumn('incompleted_store_ids', $typeText, 64, array(
        'default'  => '',
        'nullable' => false
        ), 'Incompleted Stores')
    ->addColumn('currencies', $typeText, 64, array(
        'nullable' => false
        ), 'Currencies')
    ->addColumn('incompleted_currencies', $typeText, 64, array(
        'default'  => '',
        'nullable' => false
        ), 'Incompleted Currencies')
    ->addColumn('user_agents', $typeText, 255, array(
        'default'  => '',
        'nullable' => false
        ), 'User Agents')
    ->addColumn('incompleted_user_agents', $typeText, 255, array(
        'default'  => '',
        'nullable' => false
        ), 'Incompleted User Agents')
    ->addIndex($installer->getIdxName('tmcrawler/crawler', array('status')),
        array('status'))
    ->setComment('TM Crawler');
$installer->getConnection()->createTable($table);

/**
 * Create table 'tmcrawler/report'
 */
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
