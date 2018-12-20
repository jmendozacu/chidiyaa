<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'tmcache/log'
 */
$typeText = defined('Varien_Db_Ddl_Table::TYPE_TEXT')
    ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR;

$table = $installer->getConnection()
    ->newTable($installer->getTable('tmcache/log'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true
        ), 'Entity Id')
    ->addColumn('full_action_name', $typeText, 255, array(
        'nullable' => false
        ), 'Full Action Name')
    ->addColumn('is_hit', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false
        ), 'Is Hit')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false
        ), 'Store ID')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false
        ), 'Customer Group ID')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => false,
        ), 'Creation Time')
    ->addColumn('params', $typeText, '64k', array(
        ), 'Parameters')
    ->addIndex($installer->getIdxName('tmcache/log', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('tmcache/log', array('customer_group_id')),
        array('customer_group_id'))
    ->addIndex($installer->getIdxName('tmcache/log', array('created_at')),
        array('created_at'))
    ->setComment('TM Cache Log');
$installer->getConnection()->createTable($table);

$installer->endSetup();
