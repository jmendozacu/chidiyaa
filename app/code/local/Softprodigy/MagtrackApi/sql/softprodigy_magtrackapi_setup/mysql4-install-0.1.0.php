<?php
/**
 * Softprodigy
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Softprodigy.com license that is
 * available through the world-wide-web at this URL:
 * http://www.Softprodigy.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagTrack
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create simisalestracking table
 */
 
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('softprodigy_magtrackapi/bestsellers_daily')};

CREATE TABLE {$this->getTable('softprodigy_magtrackapi/bestsellers_daily')} (
  `id` int(11) UNSIGNED NOT NULL auto_increment,
  `store_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL COMMENT 'Store Id',
  `product_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Product Id',
  `product_name` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Product Name',
  `sku` varchar(255) NOT NULL default '',
  `qty` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Qty Ordered',
  `sales` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Product Price',
  `status` varchar(25) NOT NULL default '',
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  PRIMARY KEY (`id`),
  INDEX `IDX_MAGTRACKAPI_BEST_AGGR_DAILY_STORE_ID` (`store_id`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;

DROP TABLE IF EXISTS {$this->getTable('softprodigy_magtrackapi/bestsellers_monthly')};

CREATE TABLE {$this->getTable('softprodigy_magtrackapi/bestsellers_monthly')} (
  `id` int(11) UNSIGNED NOT NULL auto_increment,
  `store_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL COMMENT 'Store Id',
  `product_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Product Id',
  `product_name` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Product Name',
  `sku` varchar(255) NOT NULL default '',
  `qty` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Qty Ordered',
  `sales` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Product Price',
  `status` varchar(25) NOT NULL default '',
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  PRIMARY KEY (`id`),
  INDEX `IDX_MAGTRACKAPI_BEST_AGGR_MONTHLY_STORE_ID` (`store_id`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;

DROP TABLE IF EXISTS {$this->getTable('softprodigy_magtrackapi/bestsellers_yearly')};

CREATE TABLE {$this->getTable('softprodigy_magtrackapi/bestsellers_yearly')} (
  `id` int(11) UNSIGNED NOT NULL auto_increment,
  `store_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL COMMENT 'Store Id',
  `product_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Product Id',
  `product_name` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Product Name',
  `sku` varchar(255) NOT NULL default '',
  `qty` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Qty Ordered',
  `sales` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Product Price',
  `status` varchar(25) NOT NULL default '',
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  PRIMARY KEY (`id`),
  INDEX `IDX_MAGTRACKAPI_BEST_AGGR_YEARLY_STORE_ID` (`store_id`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;

DROP TABLE IF EXISTS {$this->getTable('softprodigy_magtrackapi/bestsellers_orderchange')};

CREATE TABLE {$this->getTable('softprodigy_magtrackapi/bestsellers_orderchange')} (
  `id` int(11) UNSIGNED NOT NULL auto_increment,
  `order_id` INT(10) UNSIGNED NOT NULL,
  `before_status` varchar(25) NOT NULL default '',
  `after_status` varchar(25) NOT NULL default '',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UNQ_MAGTRACKAPI_BEST_ORDER_PRD_ID` (`order_id`),
  INDEX `IDX_MAGTRACKAPI_BEST_CHANGE_ORDER_ID` (`order_id`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;

CREATE TABLE {$this->getTable('softprodigy_magtrackapi/push_notifications')} (
  `id` int(11) UNSIGNED NOT NULL auto_increment,
  `new_order` INT(10) UNSIGNED NOT NULL,
  `sales_over_100` INT(10) UNSIGNED NOT NULL,
  `sales_over_1000` INT(10) UNSIGNED NOT NULL,
  `orders_above_10` INT(10) UNSIGNED NOT NULL,
  `orders_above_50` INT(10) UNSIGNED NOT NULL,
  `registration_key` varchar(255) NOT NULL default '',
  PRIMARY KEY (`id`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
INSERT INTO `{$this->getTable('softprodigy_magtrackapi/push_notifications')}` (`new_order`, `sales_over_100`, `sales_over_1000`, `orders_above_10`, `orders_above_50`) VALUES ('1','1','1','1','1');

");

$installer->endSetup();
    

