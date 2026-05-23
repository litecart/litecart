<?php

	### Environment > Set #########################################

	error_reporting(E_ALL);
	ini_set('ignore_repeated_errors', 'Off');
	ini_set('log_errors', 'On');
	ini_set('display_errors', 'On');
	ini_set('html_errors', 'On');

	date_default_timezone_set(!empty($_REQUEST['timezone']) ? $_REQUEST['timezone'] : ini_get('date.timezone'));
