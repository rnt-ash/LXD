/* 
SQL has to be executed manually right now.
Is at the moment separated from the normal install.sql because of the own feature branch.
Will be merged together as soon as the monitoring branch is released.
*/

CREATE TABLE IF NOT EXISTS `local_mon_jobs` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `physical_servers_id` int(11) unsigned,
  `virtual_servers_id` int(11) unsigned,
  `mon_services_id` int(11) unsigned NOT NULL,
  `mon_services_case` varchar(32) NOT NULL,
  `period` int(11) NOT NULL DEFAULT 5,
  `status` varchar(16) DEFAULT 'normal',
  `last_status_change` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `warning_value` varchar(32) NOT NULL,
  `maximal_value` varchar(32) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `alarm` tinyint(1) NOT NULL DEFAULT 1,
  `alarmed` tinyint(1) NOT NULL DEFAULT 0,
  `muted` tinyint(1) NOT NULL DEFAULT 0,
  `last_alarm` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `alarm_period` int(11) NOT NULL DEFAULT 15,
  `mon_contacts_message` text NOT NULL COMMENT 'FK mon_contacts, comma separated value',
  `mon_contacts_alarm` text NOT NULL COMMENT 'FK mon_contacts, comma separated value',
  `last_run` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_rrd_run` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `remote_mon_jobs` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `physical_servers_id` int(11) unsigned,
  `virtual_servers_id` int(11) unsigned,
  `colocations_id` int(11) unsigned,
  `main_ip` varchar(39),
  `mon_services_id` int(11) unsigned NOT NULL,
  `mon_services_case` varchar(32) NOT NULL,
  `period` int(11) NOT NULL DEFAULT 5,
  `status` varchar(16) DEFAULT 'normal',
  `last_status_change` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `uptime` text,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `healing` tinyint(1) NOT NULL DEFAULT 0,
  `alarm` tinyint(1) NOT NULL DEFAULT 1,
  `alarmed` tinyint(1) NOT NULL DEFAULT 0,
  `muted` tinyint(1) NOT NULL DEFAULT 0,
  `last_alarm` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `alarm_period` int(11) NOT NULL DEFAULT 15,
  `mon_contacts_message` text NOT NULL COMMENT 'FK mon_contacts, comma separated value',
  `mon_contacts_alarm` text NOT NULL COMMENT 'FK mon_contacts, comma separated value',
  `last_run` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `local_mon_logs` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `local_mon_jobs_id` int(11) NOT NULL COMMENT 'FK local_mon_jobs',
  `value` text NOT NULL,
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `local_mon_jobs_id` (`local_mon_jobs_id`),
  KEY `modified` (`modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `remote_mon_logs` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `remote_mon_jobs_id` int(11) NOT NULL COMMENT 'FK remote_mon_jobs',
  `value` text NOT NULL,
  `heal_job` int(11) DEFAULT NULL COMMENT 'FK jobs',
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `remote_mon_jobs_id` (`remote_mon_jobs_id`),
  KEY `modified` (`modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mon_services` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `graph` tinyint(1) NOT NULL,
  `status_type` text NOT NULL,
  `check_type` varchar(32) NOT NULL,
  `log_value_format` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mon_contacts` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` varchar(16) NOT NULL COMMENT 'mail or sms',
  `value` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mon_uptimes` (
  `id` int(11) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `remote_mon_jobs_id` int(11) NOT NULL COMMENT 'FK remote_mon_jobs_id',
  `year_month` char(6) NOT NULL COMMENT 'YYYYMM',
  `max_seconds` int(11) NOT NULL,
  `up_seconds` int(11) NOT NULL,
  `up_percentage` decimal(9,8) NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `remote_mon_jobs_id` (`remote_mon_jobs_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;