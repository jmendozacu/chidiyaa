<?php

$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('tmcrawler/crawler'),
    'concurrency',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'comment'   => 'Concurrency'
    )
);
