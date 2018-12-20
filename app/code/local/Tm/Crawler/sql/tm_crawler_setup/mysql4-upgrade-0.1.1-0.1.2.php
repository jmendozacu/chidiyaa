<?php

$installer = $this;

$typeText = defined('Varien_Db_Ddl_Table::TYPE_TEXT')
    ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR;

$installer->getConnection()->addColumn(
    $installer->getTable('tmcrawler/crawler'),
    'type',
    array(
        'type'      => $typeText,
        'length'    => 64,
        'default'   => 'category',
        'nullable'  => false,
        'comment'   => 'Type'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('tmcrawler/crawler'),
    'incompleted_types',
    array(
        'type'      => $typeText,
        'length'    => 64,
        'default'   => '',
        'comment'   => 'Incompleted Types'
    )
);
