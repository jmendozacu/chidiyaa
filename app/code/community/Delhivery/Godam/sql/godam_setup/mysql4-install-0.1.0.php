<?php
/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Godam
 * @copyright  Copyright (c) 2014 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 * @purpose    Mysql Install Script for module
 */
$installer = $this;

$installer->startSetup();
/**
* installer query to setup database table at the time of module installation
*/
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('delhivery_godam')};
CREATE TABLE {$this->getTable('delhivery_godam')} (
  `godam_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `state` tinyint(1) NOT NULL DEFAULT '2' COMMENT '2= Submitted, 1= Not Submitted',
  `orderid` varchar(20) DEFAULT NULL,
  `suborderid` varchar(255) DEFAULT NULL,
  `orderincid` varchar(20) DEFAULT NULL,
  `awb` varchar(20) DEFAULT NULL,
  `courier` varchar(20) DEFAULT NULL,
  `courierstatus` varchar(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `request_id` int(11) NOT NULL,
  `created_time` DATETIME NULL,
  `update_time` DATETIME NULL,
  PRIMARY KEY (`godam_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- DROP TABLE IF EXISTS delhivery_inventorylog;
CREATE TABLE delhivery_inventorylog (
  `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(255) DEFAULT NULL,
  `qty` varchar(20) DEFAULT NULL,
  `created_time` DATETIME NULL,
  `update_time` DATETIME NULL,
  PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->run("
ALTER TABLE {$this->getTable('delhivery_godam')}   ADD `courier_lsd` VARCHAR(100) NOT NULL,  ADD `courier_last_scan_location` VARCHAR(100) NOT NULL");
$installer->endSetup();
$installer->run("
ALTER TABLE {$this->getTable('delhivery_godam')} CHANGE `status` `godamstatus` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
$installer->endSetup();
$installer->run("
ALTER TABLE {$this->getTable('delhivery_godam')} CHANGE `state` `godamstate` TINYINT( 1 ) NOT NULL DEFAULT '2' COMMENT '2= Submitted, 1= Not Submitted'");

$installer->endSetup();
