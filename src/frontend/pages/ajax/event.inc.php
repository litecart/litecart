<?php

	header('X-Robots-Tag: noindex');

	try {

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			throw new Exception('Invalid request method ('. $_SERVER['REQUEST_METHOD'] .')', 405);
		}

		if (!($payload = json_decode(file_get_contents('php://input'), true))) {
			throw new Exception('Invalid JSON payload', 400);
		}

		if (empty($payload['type'])) {
			throw new Exception('Missing event type', 400);
		}

		if (!isset($payload['data'])) {
			throw new Exception('Missing event data', 400);
		}

		if (empty(session::$data['is_bot'])) {
			if ($payload['type'] == 'bot_detection' && isset($payload['data']['is_bot'])) {
				session::$data['is_bot'] = $payload['data']['is_bot'] ?? null;
			}
		}

		if (empty(session::$data['fingerprint'])) {
			if ($payload['type'] == 'create_fingerprint' && !empty($payload['data']['fingerprint'])) {
				session::$data['fingerprint'] = $payload['data']['fingerprint'];
			} else if (!empty($_SERVER['HTTP_CF_VISITOR'])) {
				session::$data['fingerprint'] = $_SERVER['HTTP_CF_VISITOR'];
			}
		}

		switch ($payload['type']) {

			case 'banner_click':

				if (empty($payload['data']['banner_id'])) {
					throw new Exception('You must provide banner ID');
				}

				database::query(
					"update ". DB_PREFIX ."banners
					set total_clicks = total_clicks + 1
					where status
					and id = ". (int)$payload['data']['banner_id'] ."
					limit 1;"
				);

				$event = [
					'type' => $payload['type'],
					'description' => $payload['description'] ?? 'User clicked a banner',
					'data' => $payload['data'] ?: [],
					'expires_at' => strtotime('+3 months'),
					'url' => $payload['url'] ?? ($_SERVER['HTTP_REFERER'] ?? null),
				];

				break;

			default:

				$event = [
					'type' => $payload['type'],
					'description' => $payload['description'],
					'data' => $payload['data'] ?: [],
					'expires_at' => strtotime('+3 months'),
					'url' => $payload['url'] ?? ($_SERVER['HTTP_REFERER'] ?? null),
				];

				break;
		}

		if (!empty($payload['fingerprint'])) {
			$event['fingerprint'] = $payload['fingerprint'];
		} elseif (!empty(session::$data['fingerprint'])) {
			$event['fingerprint'] = session::$data['fingerprint'];
		}

		customer::log($event);

		$result = ['success' => true];

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		$result = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-Type: application/json; charset=utf-8');
	echo f::format_json($result);
	exit;
