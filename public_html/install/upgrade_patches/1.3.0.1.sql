INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
VALUES ('', 'global', 'Jobs Last Push', 'Time when background jobs where last pushed for execution.', 'jobs_last_push', now(), 'input()', 0, NOW(), NOW());
