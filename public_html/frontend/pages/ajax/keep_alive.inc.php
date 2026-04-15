<?php

	header('X-Robots-Tag: noindex');
	header('Cache-Control: no-store');
	header('Content-Type: application/json; charset='. mb_http_output());

	echo f::format_json(['status' => 'ok']);
	exit;
