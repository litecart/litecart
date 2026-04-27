<?php

	try {

		if (empty($_POST['from'])) {
			throw new Exception('Missing language to translate from');
		}

		if (empty($_POST['to'])) {
			throw new Exception('Missing language to translate to');
		}

		if (empty($_POST['translations']) || !is_array($_POST['translations'])) {
			throw new Exception('Nothing to translate');
		}

		$result = (new mod_translation)->translate($_POST['from'], $_POST['to'], $_POST['translations'], !empty($_POST['html']));

	} catch (Exception $e) {
		$result = [
			'error' => $e->getMessage(),
		];
	}

	ob_end_clean();
	header('Content-Type: application/json');
	echo f::format_json($result);
	exit;
