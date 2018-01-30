-- -----------------------------------------------------
-- Tables
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `colocations` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `customers_id` int(11) unsigned NOT NULL COMMENT 'FK customers',
  `name` varchar(50) NOT NULL,
  `description` text,
  `location` varchar(50),
  `activation_date` date NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `customers_id` (`customers_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ip_objects` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `server_class` varchar(100) NOT NULL,
  `server_id` int(11) unsigned NOT NULL,
  `version` tinyint(1) unsigned NOT NULL DEFAULT '4' COMMENT 'IP version 4 or 6',
  `type` tinyint(1) unsigned NOT NULL COMMENT '1:IpAddress, 2:IpRange, 3:IpNet',
  `value1` varchar(39) NOT NULL COMMENT 'IpAddress or start-IpAddress if IpRange',
  `value2` varchar(39) DEFAULT NULL COMMENT 'netmask(IpAddress), end-IpAddress(IpRange) or IpNet prefix(IpNet)',
  `allocated` tinyint(1) NOT NULL COMMENT '1:reserved IP, 2:allocated IP, 3:allocated IP automatic',
  `main` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Main IP (for monitoring)',
  `comment` varchar(50) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `physical_servers` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(40) NOT NULL COMMENT 'Beschreibender Name',
  `description` text DEFAULT NULL,
  `customers_id` int(11) unsigned NOT NULL COMMENT 'FK customers',
  `colocations_id` int(11) unsigned NOT NULL COMMENT 'FK colocations',
  `root_public_key` text DEFAULT NULL,
  `job_public_key` text DEFAULT NULL,
  `lxd` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'ist LXD-Host',
  `lxd_images` text DEFAULT NULL,
  `fqdn` varchar(50) NOT NULL COMMENT 'Funktionaler FQDN',
  `core` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `memory` int(11) unsigned NOT NULL DEFAULT '1024' COMMENT 'Arbeitsspeicher in MB',
  `space` int(11) unsigned NOT NULL DEFAULT '100' COMMENT 'Speicherplatz in GB',
  `activation_date` date NOT NULL,
  `pending` text,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `customers_id` (`customers_id`),
  KEY `colocations_id` (`colocations_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `virtual_servers` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(40) NOT NULL COMMENT 'Beschreibender Name, früher FQDN!',
  `description` text,
  `customers_id`  int(11) unsigned NOT NULL COMMENT 'FK customers',
  `physical_servers_id` int(11) unsigned NOT NULL COMMENT 'FK physical_servers',
  `job_public_key` text DEFAULT NULL,
  `lxd` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'ist LXD-Guest',
  `lxd_snapshots` text,
  `fqdn` varchar(50),
  `core` tinyint(2) DEFAULT '1' NOT NULL,
  `memory` int(11) unsigned NOT NULL DEFAULT '1024' COMMENT 'Arbeitsspeicher in MB',
  `space` int(11) unsigned NOT NULL DEFAULT '100' COMMENT 'Speicherplatz in GB',
  `activation_date` date NOT NULL,
  `pending` text,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `customers_id` (`customers_id`),
  KEY `physical_servers_id` (`physical_servers_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mon_jobs` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `server_id` int(11) unsigned NOT NULL,
  `server_class` varchar(100) NOT NULL,
  `mon_type` varchar(10) NOT NULL COMMENT 'local or remote',
  `main_ip` varchar(39),
  `mon_behavior_class` varchar(100) NOT NULL,
  `mon_behavior_params` varchar(100),
  `period` int(11) NOT NULL DEFAULT 5,
  `status` varchar(16) DEFAULT 'normal',
  `last_status_change` datetime NOT NULL DEFAULT '0001-01-01 01:01:01',
  `uptime` text,
  `warning_value` varchar(32),
  `maximal_value` varchar(32),
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `healing` tinyint(1) NOT NULL DEFAULT 0,
  `alarm` tinyint(1) NOT NULL DEFAULT 1,
  `alarmed` tinyint(1) NOT NULL DEFAULT 0,
  `muted` tinyint(1) NOT NULL DEFAULT 0,
  `last_alarm` datetime NOT NULL DEFAULT '0001-01-01 01:01:01',
  `alarm_period` int(11) NOT NULL DEFAULT 15,
  `mon_contacts_message` text NOT NULL COMMENT 'FK logins, comma separated value',
  `mon_contacts_alarm` text NOT NULL COMMENT 'FK logins, comma separated value',
  `last_run` datetime NOT NULL DEFAULT '0001-01-01 01:01:01',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mon_logs` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `mon_jobs_id` int(11) unsigned NOT NULL COMMENT 'FK mon_jobs_id',
  `value` text NOT NULL,
  `heal_job` int(11) unsigned DEFAULT NULL COMMENT 'FK jobs if set',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'modified-field update by model',
  KEY `mon_jobs_id` (`mon_jobs_id`),
  KEY `modified` (`modified`),
  KEY `heal_job` (`heal_job`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mon_uptimes` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `mon_jobs_id` int(11) unsigned NOT NULL COMMENT 'FK mon_remote_jobs_id',
  `year_month` char(6) NOT NULL COMMENT 'YYYYMM',
  `max_seconds` int(11) NOT NULL,
  `up_seconds` int(11) NOT NULL,
  `up_percentage` decimal(9,8) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `mon_jobs_id` (`mon_jobs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mon_local_daily_logs` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `mon_jobs_id` int(11) unsigned NOT NULL COMMENT 'FK mon_local_jobs_id',
  `day` date NOT NULL,
  `value` text NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `mon_jobs_id` (`mon_jobs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Constraints
-- -----------------------------------------------------
ALTER TABLE `virtual_servers` CHANGE `physical_servers_id` `physical_servers_id` INT(11) UNSIGNED NOT NULL;
ALTER TABLE `virtual_servers` ADD CONSTRAINT `Constraint_Customers_VirtualServer` FOREIGN KEY (`customers_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `virtual_servers` ADD CONSTRAINT `Constraint_PhysicalServer_CirtualServer` FOREIGN KEY (`physical_servers_id`) REFERENCES `physical_servers`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `physical_servers` ADD CONSTRAINT `Constraint_Customers_PhysicalServers` FOREIGN KEY (`customers_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `physical_servers` ADD CONSTRAINT `Constraint_Colocation_PhysicalServer` FOREIGN KEY (`colocations_id`) REFERENCES `colocations`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `colocations` ADD CONSTRAINT `Constraint_Customers_Colocations` FOREIGN KEY (`customers_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE `mon_logs` ADD CONSTRAINT `Constraint_monjobs_monlogs` FOREIGN KEY (`mon_jobs_id`) REFERENCES `mon_jobs`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE `mon_logs` ADD CONSTRAINT `Constraint_jobs_healjobs` FOREIGN KEY (`heal_job`) REFERENCES `jobs`(`id`) ON DELETE SET NULL ON UPDATE NO ACTION;