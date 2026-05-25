<?php

	try {

		if (empty($_POST)) {
			throw new Exception('No data received');
		}

		$payload = file_get_contents('php://input');

		if (empty($payload)) {
			throw new Exception('No data received');
		}

		if (!$report = json_decode($payload, true)) {
			throw new Exception('Invalid JSON received');
		}

		if (strlen($payload) > 1000) {
			throw new Exception('Payload too large');
		}

		foreach ([
			'started',
			'events',
			'mouseMoves',
			'keyEvents',
			'touchEvents',
			'scrollEvents',
			'visibilityChanges',
			'focusChanges',
			'timings',
			'entropy',
			'webdriver',
			'plugins',
			'languages',
			'hardwareConcurrency',
			'deviceMemory',
			'canvas',
			'webgl',
			'suspicious',
		] as $key) {
			if (isset($report[$key])) {
				security::$data['bot_challenge'][$key] = $report[$key];
			}
		}

	} catch (Exception $e) {

		header('HTTP/1.1 400 Bad Request');
		header('Content-Type: application/json');

		echo json_encode([
			'success' => false,
			'error' => $e->getMessage(),
		]);

		exit;
	}
