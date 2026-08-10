<?php

	ignore_user_abort(true);
	@set_time_limit(300);

	header('X-Robots-Tag: noindex');
	header('Content-type: text/plain; charset='. mb_http_output());

	if ($_SERVER['SERVER_SOFTWARE'] != 'CLI') {
		if ($last_push = settings::get('jobs_last_push')) {
			$last_push = strtotime($last_push);
			if (date('Ymdh', $last_push) == date('Ymdh') && floor(date('i', $last_push)/5) == floor(date('i')/5)) {
				header('HTTP/1.1 429 Too Many Requests');
				die('Zzz...');
			}
		}
	}

	database::query(
		"update ". DB_TABLE_PREFIX ."settings
		set value = '". date('Y-m-d H:i:s') ."'
		where `key` = 'jobs_last_push'
		limit 1;"
	);

	$jobs = new mod_jobs();
	$jobs->process();

	echo 'OK';
	exit;
