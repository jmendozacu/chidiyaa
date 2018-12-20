<?php

$installer = $this;

$typeText = defined('Varien_Db_Ddl_Table::TYPE_TEXT')
    ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR;

$installer->getConnection()->addColumn(
    $installer->getTable('tmcrawler/crawler'),
    'currencies',
    array(
        'type'      => $typeText,
        'length'    => 64,
        'nullable'  => false,
        'comment'   => 'Currencies'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('tmcrawler/crawler'),
    'incompleted_currencies',
    array(
        'type'      => $typeText,
        'length'    => 64,
        'default'   => '',
        'comment'   => 'Incompleted Currencies'
    )
);
