<?php

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
			functions::format_json($report),
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
