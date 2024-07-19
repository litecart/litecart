<?php

	document::$layout = 'ajax';

	try {

			if (empty($_GET['query'])) {
				throw new Exception('Nothing to search for');
			}

		$apps = functions::admin_get_apps();
		$app_themes = array_column($apps, 'theme', 'code');

		$search_results = [];

		foreach (array_column($apps, 'search_results', 'id') as $app => $file) {

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
	echo json_encode($search_results, JSON_UNESCAPED_SLASHES);
	exit;
