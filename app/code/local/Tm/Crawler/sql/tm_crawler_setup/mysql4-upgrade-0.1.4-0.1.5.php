<?php

$installer = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('tmcrawler/crawler'),
    'last_activity_at',
    array(
        'after'    => 'started_at',
        'type'     => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'nullable' => true,
        'default'  => null,
        'comment'  => 'Time of last activity'
    )
);
