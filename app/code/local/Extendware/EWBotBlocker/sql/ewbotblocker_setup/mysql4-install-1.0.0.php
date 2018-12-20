<?php

Mage::helper('ewcore/cache')->clean();
$installer = $this;
$installer->startSetup();

$command = "
DROP TABLE IF EXISTS `ewbotblocker_bot`;
CREATE TABLE `ewbotblocker_bot` (
  `bot_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('enabled','pending','disabled') NOT NULL DEFAULT 'enabled',
  `ip_address` varchar(255) NOT NULL,
  `user_agent` text NOT NULL,
  `num_visits` int(10) unsigned NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `first_seen_at` datetime NOT NULL,
  `last_seen_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`bot_id`),
  UNIQUE KEY `idx_ip_address` (`ip_address`) USING BTREE,
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

$command = @preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(ON\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(INTO\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(FROM\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

if ($command) $installer->run($command);
$installer->endSetup(); 

Mage::helper('ewbotblocker/config')->autoConfigure();
Mage::helper('ewbotblocker/config')->reload();