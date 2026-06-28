<?php

	document::$layout = 'ajax';

	try {

		if (empty($_GET['query'])) {
			throw new Exception('Nothing to search for');
		}

		$apps = f::admin_get_apps();
		$app_themes = array_column($apps, 'theme', 'code');

		$search_results = [];

		foreach (array_column($apps, 'search_results', 'id') as $app => $file) {

			// Skip apps the administrator doesn't have access to.
			// Empty apps map = unrestricted; non-empty = restricted to listed apps.
			if (!empty(administrator::$data['permissions']['apps']) && empty(administrator::$data['permissions']['apps'][$app]['status'])) continue;

			$results = (function($app, $file, $query) {
				return include 'app://backend/apps/' . $app .'/' . $file;
			})($app, $file, $_GET['query']);

			if (!$results) continue;

			foreach ($results as $result) {
				$search_results[] = [
					'app' => $app,
					'theme' => $apps[$app]['theme'],
					'name' => $result['name'],
					'results' => $result['results'],
				];
			}
		}

	} catch(Exception $e) {
		http_response_code(400);
		$search_results = ['error' => $e->getMessage()];
	}

	header('Content-Type: application/json; charset='. mb_http_output());
	echo f::format_json($search_results);
	exit;
