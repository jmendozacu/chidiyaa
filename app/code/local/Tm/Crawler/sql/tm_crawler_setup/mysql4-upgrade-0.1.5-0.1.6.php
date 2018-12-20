<?php

$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('tmcrawler/crawler'),
    'crawled_urls',
    array(
        'after'    => 'limit',
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => false,
        'default'  => 0,
        'comment'  => 'Crawled Urls Count'
    )
);
