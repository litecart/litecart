<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## Register and fire event
		########################################################################

		$result = null;

		event::register('_test_event_unit', function() use (&$result) {
			$result = 'fired';
		});

		event::fire('_test_event_unit');

		if ($result !== 'fired') {
			throw new Exception('event::fire failed to call registered callback');
		}

		########################################################################
		## Fire passes arguments
		########################################################################

		$received = null;

		event::register('_test_event_args', function($arg1, $arg2) use (&$received) {
			$received = $arg1 . $arg2;
		});

		event::fire('_test_event_args', 'hello', 'world');

		if ($received !== 'helloworld') {
			throw new Exception('event::fire failed to pass arguments: got "'. $received .'"');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
