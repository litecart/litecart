<?php

// Returns the last point in time by step interval
	// Returns the last point in time by step interval
	function datetime_last_by_interval($interval, $timestamp=null) {

		if ($timestamp === null) {
			$timestamp = time();

		} else if (!is_numeric($timestamp)) {
			$timestamp = strtotime($timestamp);
		}

		$y = date('Y', $timestamp);
		$m = date('m', $timestamp);
		$d = date('m', $timestamp);

		switch (true) {

			case (strcasecmp($interval, '5 min')):       return mktime(date('H'), floor(date('i', $timestamp) /5)  *5, 0, $m, $d, $y);
			case (strcasecmp($interval, '10 min')):      return mktime(date('H'), floor(date('i', $timestamp) /10) *10, 0, $m, $d, $y);
			case (strcasecmp($interval, '15 min')):      return mktime(date('H'), floor(date('i', $timestamp) /15) *15, 0, $m, $d, $y);
			case (strcasecmp($interval, '30 min')):      return mktime(date('H'), floor(date('i', $timestamp) /30) *30, 0, $m, $d, $y);
			case (strcasecmp($interval, 'Hourly')):      return mktime(date('H'), 0, 0, $m, $d, $y);
			case (strcasecmp($interval, '2 hours')):     return mktime(floor(date('H', $timestamp) /2)  *2,  0, 0, $m, $d, $y);
			case (strcasecmp($interval, '3 hours')):     return mktime(floor(date('H', $timestamp) /3)  *3,  0, 0, $m, $d, $y);
			case (strcasecmp($interval, '6 hours')):     return mktime(floor(date('H', $timestamp) /6)  *6,  0, 0, $m, $d, $y);
			case (strcasecmp($interval, '12 hours')):    return mktime(floor(date('H', $timestamp) /12) *12, 0, 0, $m, $d, $y);
			case (strcasecmp($interval, 'Daily')):       return mktime(0, 0, 0, $m, $d, $y);
			case (strcasecmp($interval, 'Weekly')):      return strtotime('This week 00:00:00', $timestamp);
			case (strcasecmp($interval, 'Monthly')):     return mktime(0, 0, 0, null, 1, $y);
			case (strcasecmp($interval, 'Quarterly')):   return mktime(0, 0, 0, ((ceil(date('n', $timestamp) /3) -1) *3) +1, $d, $y);
			case (strcasecmp($interval, 'Half-Yearly')): return mktime(0, 0, 0, ((ceil(date('n', $timestamp) /6) -1) *6) +1, $d, $y);
			case (strcasecmp($interval, 'Yearly')):      return mktime(0, 0, 0, 1, 1, $y);

			default: trigger_error('Unknown step interval ('. $interval .')', E_USER_WARNING); return false;
		}
	}
