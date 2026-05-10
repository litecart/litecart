<?php

	/*
		Router script for PHP built-in server.
		Replaces .htaccess rewrite rules for E2E testing.

		Usage: php -S localhost:8080 -t public_html router.php
	*/

	$docroot = $_SERVER['DOCUMENT_ROOT'];
	$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
	$path = $docroot . $uri;

	// Serve physical files directly
	if ($uri !== '/' && (is_file($path) || is_dir($path))) {

		// Block access to .inc.php files (like .htaccess FilesMatch rule)
		if (preg_match('#\.inc\.php$#', $uri)) {
			http_response_code(403);
			echo '403 Forbidden';
			return true;
		}

		return false;
	}

	// Rewrite /images/ and /cache/ to /storage/
	if (preg_match('#^/(cache|images)/#', $uri)) {
		$storage_path = $docroot . '/storage' . $uri;
		if (is_file($storage_path)) {
			$mime = mime_content_type($storage_path);
			header('Content-Type: ' . $mime);
			readfile($storage_path);
			return true;
		}
	}

	// Favicon fallback
	if (preg_match('#/favicon\.ico$#', $uri)) {
		$favicon = $docroot . '/storage/images/favicons/favicon.ico';
		if (is_file($favicon)) {
			header('Content-Type: image/x-icon');
			readfile($favicon);
			return true;
		}
	}

	// Route everything else to index.php
	require $docroot . '/index.php';
	return true;
