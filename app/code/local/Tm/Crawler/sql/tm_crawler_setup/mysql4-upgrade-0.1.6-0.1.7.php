<?php

$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('tmcrawler/crawler'),
    'interval',
    array(
        'after'    => 'completed_at',
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => false,
        'default'  => 24,
        'comment'  => 'Interval'
    )
);
