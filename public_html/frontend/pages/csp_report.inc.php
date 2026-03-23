<?php

/*
	CSP Report Example:
	{
		"csp-report": {
			"document-uri": "http://litecart.tld/path/to/resource?query",
			"referrer": "",
			"violated-directive": "style-src-attr",
			"effective-directive": "style-src-attr",
			"original-policy": "base-uri 'self';default-src 'self';frame-ancestors 'self';script-src 'nonce-badff56304104d0e62c526527b5bb7d7' 'strict-dynamic';img-src 'self';form-action 'self';report-uri https://www.example.com/csp_report.php",
			"disposition": "enforce",
			"blocked-uri": "inline",
			"line-number": 50,
			"source-file": "http://litecart.tld/path/to/resource",
			"status-code": 200,
			"script-sample": ""
		}
	}
*/

	try {

		if (!$request = file_get_contents('php://input')) {
			throw new Exception('No data', 400);
		}

		if (!$result = json_decode($request, true)) {
			throw new Exception('Invalid JSON', 400);
		}

		if (empty($result['csp-report'])) {
			throw new Exception('Missing CSP report', 400);
		}

		$report = $result['csp-report'];

		error_log(implode(PHP_EOL, [
			'CSP Violation for '. $report['document-uri'],
			f::format_json($report),
			!empty($_SERVER['REMOTE_ADDR']) ? 'Client: '. $_SERVER['REMOTE_ADDR'] .' ('. gethostbyaddr($_SERVER['REMOTE_ADDR']) .')' : '',
			!empty($_SERVER['HTTP_USER_AGENT']) ? 'User Agent: '. $_SERVER['HTTP_USER_AGENT'] : '',
		]) . PHP_EOL);

		echo 'OK';
		exit;

	} catch (Exception $e) {
		http_response_code($e->getCode());
		echo $e->getMessage();
		exit;
	}
