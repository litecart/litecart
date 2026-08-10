<?php

	include_once __DIR__.'/../src/shared/app_header.inc.php';

	try {

		########################################################################
		## start_watch / stop_watch — basic timing
		########################################################################

		stats::start_watch('test_timer');
		usleep(10000); // 10ms
		stats::stop_watch('test_timer');

		if (!isset(stats::$data['test_timer'])) {
			throw new Exception('stats::stop_watch failed to record elapsed time');
		}

		if (stats::$data['test_timer'] <= 0) {
			throw new Exception('stats::stop_watch recorded zero or negative time');
		}

		########################################################################
		## Accumulated timing
		########################################################################

		stats::start_watch('test_accumulate');
		usleep(5000);
		stats::stop_watch('test_accumulate');

		$first = stats::$data['test_accumulate'];

		stats::start_watch('test_accumulate');
		usleep(5000);
		stats::stop_watch('test_accumulate');

		if (stats::$data['test_accumulate'] <= $first) {
			throw new Exception('stats::stop_watch failed to accumulate time');
		}

		########################################################################
		## Cleanup test data
		########################################################################

		unset(stats::$data['test_timer']);
		unset(stats::$data['test_accumulate']);

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
