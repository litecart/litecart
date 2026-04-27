<?php

	header('X-Robots-Tag: noindex');

	try {

		if (administrator::check_login()) {
			throw new Exception('Access denied', 403);
		}

		// Check if the request is an AJAX request, throw an exception if not.
		if (!is_ajax_request()) {
			throw new Exception('Invalid request', 400);
		}

		// Retrieve the raw input data from the request, throw an exception if missing.
		if (!($input = file_get_contents('php://input'))) {
			throw new Exception('Missing input', 400);
		}

		// Decode the JSON input, throw an exception if invalid.
		if (!($result = json_decode($input, true))) {
			throw new Exception('Invalid JSON', 400);
		}

		// Validate the presence of required fields in the payload, throw an exception if missing or invalid.
		if (!isset($result['message']) || !isset($result['file']) || !isset($result['line'])) {
			throw new Exception('Missing or invalid payload', 400);
		}

		// Log the error details including message, file, line, and request metadata.
		error_log(
			implode(PHP_EOL, [
				"$result[message] in $result[file] on line $result[line]",
				"Request: $_SERVER[REQUEST_METHOD] " . parse_url($result['url'], PHP_URL_PATH) . " $_SERVER[SERVER_PROTOCOL]",
				"Host: $_SERVER[HTTP_HOST]",
				"Client: $_SERVER[REMOTE_ADDR] (" . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ')',
				"User Agent: $_SERVER[HTTP_USER_AGENT]",
				!empty($_SERVER['HTTP_REFERER']) ? "Referer: {$_SERVER['HTTP_REFERER']}" : '',
			]),
		);

		// Set the response status to 'ok' if no exceptions were thrown.
		$result['status'] = 'ok';

	} catch (Exception $e) {
		// Handle exceptions by setting the HTTP response code and returning an error message.
		http_response_code($e->getCode() ?: 500);
		$result = ['error' => $e->getMessage()];
	}

	// Clear the output buffer, set the response header to JSON, and return the result.
	ob_clean();
	header('Content-Type: application/json; charset=utf-8');
	echo f::format_json($result);
	exit;
