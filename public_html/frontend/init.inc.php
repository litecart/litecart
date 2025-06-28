<?php

	// Set Default OpenGraph Content
	document::$opengraph = [
		'title' => settings::get('store_name'),
		'type' => 'website',
		'url' => document::href_ilink(''),
		'image' => document::href_rlink('storage://images/logotype.png'),
	];

	// Set Default Schema Data
	document::$schema['website'] = [
		'@context' => 'https://schema.org/',
		'@type' => 'Website',
		'name' => settings::get('store_name'),
		'url' => document::ilink(''),
		'countryOfOrigin' => settings::get('store_country_code'),
	];

	// Set Default Organization Schema Data
	document::$schema['organization'] = [
		'@context' => 'https://schema.org/',
		'@type' => 'Organization',
		'name' => settings::get('store_name'),
		'url' => document::ilink(''),
		'logo' => document::rlink(FS_DIR_STORAGE . 'images/logotype.png'),
		'email' => settings::get('store_email'),
		'availableLanguage' => array_column(language::$languages, 'name'),
	];

	// Favicons
	document::$head_tags['favicon'] = implode(PHP_EOL, [
		'<link rel="icon" href="'. document::href_rlink('storage://images/favicons/favicon.ico') .'" type="image/x-icon" sizes="32x32 48x48 64x64 96x96">',
		'<link rel="icon" href="'. document::href_rlink('storage://images/favicons/favicon-128x128.png') .'" type="image/png" sizes="128x128">',
		'<link rel="icon" href="'. document::href_rlink('storage://images/favicons/favicon-192x192.png') .'" type="image/png" sizes="192x192">',
		'<link rel="icon" href="'. document::href_rlink('storage://images/favicons/favicon-256x256.png') .'" type="image/png" sizes="256x256">',
	]);

	// Hreflang
	(function() {
		$hreflangs = [];

		foreach (language::$languages as $language) {
			if ($language['url_type'] == 'none') continue;
			$hreflangs[] = '<link rel="alternate" hreflang="'. $language['code'] .'" href="'. document::href_ilink(route::$selected['resource'], [], true, ['page', 'sort'], $language['code']) .'">';
		}

		document::$head_tags['hreflang'] = implode(PHP_EOL, $hreflangs);
	})();

	// Privacy Consents
	document::$jsenv['cookie_consents'] = [
		'classes' => !empty($_COOKIE['cookie_consents']['classes']) ? explode(',', $_COOKIE['cookie_consents']['classes']) : [],
		'third_parties' => !empty($_COOKIE['cookie_consents']['third_parties']) ? explode(',', $_COOKIE['cookie_consents']['third_parties']) : [],
	];

	$privacy_consents = [];

	if (!empty($_COOKIE['privacy_consents'])) {
		foreach (preg_split('#\s*\|\s*#', $_COOKIE['privacy_consents'], -1, PREG_SPLIT_NO_EMPTY) as $consent) {
			list($privacy_class, $third_parties) = preg_split('#:#', $consent, -1, PREG_SPLIT_NO_EMPTY);
		}
	}

	// Site Tags
	database::query(
		"select * from ". DB_TABLE_PREFIX ."site_tags
		where status
		order by priority desc, name asc;"
	)->each(function($site_tag) use ($privacy_consents) {

		// Check if consent is required
		if (!empty($site_tag['require_consent'])) {

			// Honour browser "Do Not Track" setting
			if (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1) {
				return;
			}

			// Check if privacy policy is accepted
			if (settings::get('cookie_policy') && empty($_COOKIE['cookies_accepted'])) {
				return;
			}

			list($privacy_class, $third_party) = preg_split('#:#', $site_tag['require_consent'], 2);

			// Check if consent is collected for either the third party or all in the privacy class
			if (empty($privacy_consents['privacy_class']) || (!in_array('all', $privacy_consents['privacy_class']) && !in_array($third_party, $privacy_consents['third_parties']))) {
				return;
			}
		}

		switch ($site_tag['position']) {

			case 'head':
				self::$head_tags[] = $site_tag['content'];
				break;

			case 'body':
				self::$foot_tags[] = $site_tag['content'];
				break;
		}
	});

	// Maintenance Mode
	if (settings::get('maintenance_mode')) {

		// If logged in as administrator
		if (administrator::check_login()) {

			// Show notice
			notices::add('notices', strtr('%message [<a href="%link">%preview</a>]', [
				'%message' => t('reminder_store_in_maintenance_mode', 'The store is in maintenance mode.'),
				'%preview' => t('title_preview', 'Preview'),
				'%link' => document::href_ilink('maintenance_mode'),
			]), 'maintenance_mode');

		} else {

			http_response_code(503);

			// Show maintenance mode page
			include 'app://frontend/pages/maintenance_mode.inc.php';
			require_once 'app://includes/app_footer.inc.php';
			exit;
		}
	}
