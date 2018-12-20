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
        'nullable' => false
        ), 'Status')
    ->addColumn('state', $typeText, 32, array(
        'nullable' => false
        ), 'State')
    ->addColumn('offset', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false
        ), 'Last Offset')
    ->addColumn('limit', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false
        ), 'Urls Limit Per Request')
    ->addColumn('started_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
        'default' => null
        ), 'Started At')
    ->addColumn('completed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
        'default' => null
        ), 'Completed At')
    // ->addColumn('paused_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    //     'nullable' => true,
    //     'default' => null
    //     ), 'Paused At')
    // ->addColumn('resumed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    //     'nullable' => true,
    //     'default' => null
    //     ), 'Resumed At')
    ->addIndex($installer->getIdxName('tmcrawler/crawler', array('status')),
        array('status'))
    ->setComment('TM Crawler');
$installer->getConnection()->createTable($table);

$installer->endSetup();
