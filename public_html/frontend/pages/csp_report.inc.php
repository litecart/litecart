<?php

	try {

		if (!$_POST) {
			throw new Exception('No data', 400);
		}

		if (empty($_POST['csp-report'])) {
			throw new Exception('Missing CSP report', 400);
		}

		if (!$result = json_decode($_POST['csp_report'], true)) {
			throw new Exception('Invalid JSON', 400);
		}

		error_log(implode(PHP_EOL, [
			'CSP Violation for '. $result['document-uri'],
			functions::format_json($result),
			!empty($_SERVER['REMOTE_ADDR']) ? 'Client: '. $_SERVER['REMOTE_ADDR'] .' ('. gethostbyaddr($_SERVER['REMOTE_ADDR']) .')' : '',
			!empty($_SERVER['HTTP_USER_AGENT']) ? 'User Agent: '. $_SERVER['HTTP_USER_AGENT'] : '',
		]) . PHP_EOL);
		exit;

	} catch (Exception $e) {
		http_response_code($e->getCode());
		echo $e->getMessage();
		exit;
	}
