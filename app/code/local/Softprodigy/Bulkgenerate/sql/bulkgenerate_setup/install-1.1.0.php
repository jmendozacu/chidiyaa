<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('bulkgenerate_payment_options')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `website_id` int(11) NOT NULL default '0',
  `payment_code` varchar(255) NOT NULL default '',
  `capture_mode` varchar(4) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();
