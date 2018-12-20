<?php

$installer = $this;

$typeText = defined('Varien_Db_Ddl_Table::TYPE_TEXT')
    ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR;

$installer->getConnection()->addColumn(
    $installer->getTable('tmcrawler/crawler'),
    'user_agents',
    array(
        'type'      => $typeText,
        'length'    => 255,
        'default'   => '',
        'nullable'  => false,
        'comment'   => 'User Agents'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('tmcrawler/crawler'),
    'incompleted_user_agents',
    array(
        'type'      => $typeText,
        'length'    => 255,
        'default'   => '',
        'nullable'  => false,
        'comment'   => 'Incompleted User Agents'
    )
);
