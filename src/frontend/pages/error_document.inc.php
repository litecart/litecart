<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/pages/error_document.inc.php
	*/

	document::$layout = 'blank';

	if (preg_match('#\.(a?png|avif|gif|jpe?g|jxl|png|svg|webp)$#', route::$request)) {
		header('Content-Type: image/svg+xml');
		echo file_get_contents('storage://images/no_image.svg');
		exit;
	}

	switch (http_response_code()) {

		case 400:
			$title = 'Bad Request';
			$description = t('error_400_bad_request', 'The server cannot or will not process the request due to a client error.');
			break;

		case 401:
			$title = 'Unauthorized';
			$description = t('error_401_unauthorized', 'You are not authorized to view the requested file.');
			break;

		case 403:
			$title = 'Forbidden';
			$description = t('error_403_forbidden', 'Access to the requested file is forbidden.');
			break;

		case 404:
			$title = 'Not Found';
			$description = t('error_404_not_found', 'The requested file could not be found.');
			break;

		case 410:
			$title = 'Gone';
			$description = t('error_410_gone', 'The requested page is no longer available.');
			break;

		default:
			http_response_code(500);
			$title = 'Internal Server Error';
			$description = t('error_500_internal_server_error', 'That was not meant to happen.');
			break;
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/error_document.inc.php', [
		'title' => $title,
		'description' => $description,
		'code' => http_response_code(),
	]);

	echo $_page->render('app://frontend/templates/'.settings::get('template').'/pages/error_document.inc.php');
