<?php

	// Prevent indexing by search engines
	header('X-Robots-Tag: noindex');

	// Don't require login for login page
	if (!in_array(route::$selected['resource'], ['b:login', 'b:manifest.json'])) {
		administrator::require_login();
	}

	// Add site manifest
	document::$head_tags['manifest'] = '<link rel="manifest" href="'. document::href_ilink('manifest.json') .'">';

	// Assign backend favicons
	document::$head_tags['favicon'] = implode(PHP_EOL, [
		'<link rel="icon" href="'. document::href_rlink('app://backend/template/images/favicons/favicon.ico') .'" type="image/x-icon" sizes="32x32 48x48 64x64 96x96">',
		'<link rel="icon" href="'. document::href_rlink('app://backend/template/favicons/favicon-128x128.png') .'" type="image/png" sizes="128x128">',
		'<link rel="icon" href="'. document::href_rlink('app://backend/template/favicons/favicon-192x192.png') .'" type="image/png" sizes="192x192">',
		'<link rel="icon" href="'. document::href_rlink('app://backend/template/favicons/favicon-256x256.png') .'" type="image/png" sizes="256x256">',
	]);

	// Fetch apps
	$apps = functions::admin_get_apps();

	// Identify app and document
	if (preg_match('#'. preg_quote(BACKEND_ALIAS, '#') .'/(?<app>[^/]*)/(?<doc>.*)$#', route::$request, $matches)) {

		// If request matches an app, define __APP__
		if (!empty($matches['app']) && in_array($matches['app'], array_column($apps, 'id'))) {
			define('__APP__', $matches['app']);

			// If request matches an app document, define __DOC__
			if (!empty($matches['doc']) && in_array($matches['doc'], array_keys($apps[__APP__]['docs']))) {
				define('__DOC__', $matches['doc']);
			}
		}
	}
