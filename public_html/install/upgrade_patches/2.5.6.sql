UPDATE `lc_modules`
SET `settings` = REPLACE(settings, 'report_frequency', 'frequency')
WHERE `key` = 'job_error_reporter'
LIMIT 1;
