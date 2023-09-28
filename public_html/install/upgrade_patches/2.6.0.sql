UPDATE `lc_pages`
SET dock = CAST(REGEXP_REPLACE(dock, ',.*$', '') AS CHAR);