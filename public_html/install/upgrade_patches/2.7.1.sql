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
-- -----
INSERT IGNORE INTO `lc_settings` (`setting_group_key`, `type`, `key`, `value`, `title`, `description`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('security', 'global', 'csrf_protection', '0', 'CSRF-Protection', 'Enable CSRF (Cross-Site Request Forgery) protection for form submissions.', 'toggle()', 10, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('security', 'global', 'bot_challenge', '0', 'Bot Challenge', 'Challenge clients with a simple JavaScript challenge', 'toggle()', 30, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('security', 'global', 'whitelisted_user_agents', 'adsbot-google, adidxbot, ahrefsbot, amazonbot, anthropic-ai, applebot, baiduspider, bingbot, bingpreview, ccbot, chatgpt-user, chrome-lighthouse, claudebot, claude-searchbot, claude-user, claude-web, crisp, dixa, dotbot, duckassistbot, duckduckbot, exabot, facebookbot, facebookexternalhit, facebot, google-inspectiontool, googlebot, gptbot, ia_archiver, librecrawl, mediapartners-google, meta-externalads, meta-externalagent, microsoftpreview, mistralai-user, msnbot, oai-searchbot, perplexity-user, perplexitybot, petalbot, rsiteauditor, salesmanago, semrushbot, slurp, sogou', 'Whitelisted User Agents', 'A comma-separated list of substrings for matching User-Agent headers that are exempt from bot detection.', 'bigtext()', 40, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('security', 'global', 'whitelisted_hostnames', '.apple.com\n.baidu.com\n.bing.com\n.cloudflare.com\n.duckduckgo.com\n.facebook.com\n.fbsv.net\n.google.com\n.googlebot.com\n.meta.com\n.paypal.com\n.stripe.com\n.telegram.org\n.yahoo.com\n.yandex.com\n.yandex.net\n.yandex.ru', 'Whitelisted Hostnames', 'A list of hostnames that are exempt from bot detection. One hostname suffix per line (e.g. ".googlebot.com").', 'bigtext()', 41, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
