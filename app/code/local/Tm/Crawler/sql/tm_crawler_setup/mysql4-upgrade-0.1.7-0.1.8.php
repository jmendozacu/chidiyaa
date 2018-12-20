<?php

$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('tmcrawler/crawler'),
    'clean_cache',
    array(
        'after'    => 'concurrency',
        'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable' => false,
        'default'  => 1,
        'comment'  => 'Clean Cache'
    )
);
