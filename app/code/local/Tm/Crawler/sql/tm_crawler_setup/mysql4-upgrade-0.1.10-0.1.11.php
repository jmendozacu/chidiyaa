<?php

$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('tmcrawler/crawler'),
    'max_response_time',
    array(
        'after'    => 'clean_cache',
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '3,1',
        'nullable' => false,
        'default'  => 10,
        'comment'  => 'Max Avg. Response Time'
    )
);
