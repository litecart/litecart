CREATE TABLE `lc_rate_limiting` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `action` VARCHAR(64) NOT NULL DEFAULT '',
  `scope_type` VARCHAR(32) DEFAULT NULL,
  `scope_key` VARCHAR(128) DEFAULT NULL,
  `ip_address` VARCHAR(39) NOT NULL DEFAULT '',
  `hostname` VARCHAR(128) NOT NULL DEFAULT '',
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `scope` (`scope_type`,`scope_key`,`action`,`date_created`),
  KEY `record` (`ip_address`,`action`,`date_created`)
) ENGINE=MyISAM;
-- -----
UPDATE `lc_modules`
SET module_id = 'job_cleaner'
WHERE module_id = 'job_cache_cleaner';
