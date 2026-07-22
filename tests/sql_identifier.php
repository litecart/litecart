<?php

	// Platform test for PROJ-22: database::identifier() helper.
	// Purely static logic — no DB connection needed. The helper only
	// calls preg_match() and in_array(), so we can exercise it without
	// bootstrapping the whole app.

	if (!class_exists('database', false)) {
		require_once __DIR__ . '/../public_html/includes/nodes/nod_database.inc.php';
	}

	try {

		########################################################################
		## AC-1: plain identifier pattern accepts alnum/underscore, rejects rest
		########################################################################

		echo 'Testing database::identifier pattern enforcement...';

		$accepted = ['en', 'de', 'zh_cn', 'zh-cn', 'pt-br', 'en-gb', 'text_en', 'foo_bar_123', 'A', '_x'];
		foreach ($accepted as $name) {
			$result = database::identifier($name);
			if ($result !== $name) {
				throw new Exception("Expected identifier($name) to return unchanged, got " . var_export($result, true));
			}
		}

		$rejected = [
			'',                                // empty
			'en`',                             // backtick
			'en;',                             // semicolon
			'en drop table',                   // space + keyword
			"en\0",                            // null byte
			'x`,(select 1)--',                 // injection payload from audit finding
			"\xf0\x9f\x98\x80",                // emoji
			'en.us',                           // dot
			'en us',                           // whitespace
		];
		foreach ($rejected as $name) {
			$threw = false;
			try {
				database::identifier($name);
			} catch (InvalidArgumentException $e) {
				$threw = true;
			}
			if (!$threw) {
				throw new Exception("Expected identifier(" . var_export($name, true) . ") to throw, but it did not");
			}
		}

		########################################################################
		## AC-2: allowlist enforcement
		########################################################################

		echo 'Testing allowlist enforcement...';

		// Legitimate membership.
		$result = database::identifier('en', ['en', 'de', 'fr']);
		if ($result !== 'en') {
			throw new Exception('Allowlisted name should pass through');
		}

		// Not a member → reject.
		$threw = false;
		try {
			database::identifier('it', ['en', 'de', 'fr']);
		} catch (InvalidArgumentException $e) {
			$threw = true;
		}
		if (!$threw) {
			throw new Exception('Non-allowlisted name should throw');
		}

		// Empty allowlist → everything rejected.
		$threw = false;
		try {
			database::identifier('en', []);
		} catch (InvalidArgumentException $e) {
			$threw = true;
		}
		if (!$threw) {
			throw new Exception('Empty allowlist should reject every identifier');
		}

		// Case-sensitive on purpose: SQL identifiers in MySQL are
		// case-insensitive, but our app consistently uses lowercase.
		$threw = false;
		try {
			database::identifier('EN', ['en', 'de']);
		} catch (InvalidArgumentException $e) {
			$threw = true;
		}
		if (!$threw) {
			throw new Exception('Allowlist must be strict / case-sensitive');
		}

		// Null allowlist → only regex check applies.
		$result = database::identifier('anything_valid', null);
		if ($result !== 'anything_valid') {
			throw new Exception('Null allowlist should fall back to regex-only');
		}

		echo ' [OK]' . PHP_EOL;

		########################################################################
		## AC-3/4/5/6/7/8/9: verify the callers have been migrated
		########################################################################

		echo 'Cross-checking call sites use database::identifier()...';

		$expectations = [
			'public_html/backend/apps/localization/translations/translations.inc.php' => [
				'required' => [
					'database::identifier($_lang_code, $allowed_language_codes)', // early validate
					'database::identifier($language_code)',                       // identifier context
				],
				'forbidden' => [
					'`text_$language_code`', // raw PHP interpolation into backtick context
					'`text_". database::input($language_code) ."`', // old escape-only pattern
				],
			],
			'public_html/backend/apps/localization/translations/csv.inc.php' => [
				'required' => [
					'database::identifier($_lang_code, $allowed_language_codes)',
					'database::identifier($language_code)',
				],
				'forbidden' => [
					'`text_". database::input($language_code) ."`',
				],
			],
			'public_html/backend/apps/localization/languages/edit_language.inc.php' => [
				'required' => [
					"preg_match('#^[a-z]{2,5}",
					'database::identifier($language->data[\'code\'])',
				],
				'forbidden' => [
					'`text_". database::input($language->data[\'code\']) ."`',
				],
			],
			'public_html/includes/entities/ent_language.inc.php' => [
				'required' => [
					'database::identifier($this->previous[\'code\'])',
					'database::identifier($this->data[\'code\'])',
				],
				'forbidden' => [
					'`text_". database::input($this->data[\'code\']) ."`',
					'`text_". database::input($this->previous[\'code\']) ."`',
				],
			],
		];

		foreach ($expectations as $file => $expect) {
			$content = file_get_contents(__DIR__ . '/../' . $file);
			if ($content === false) {
				throw new Exception("Could not read $file");
			}
			foreach ($expect['required'] as $needle) {
				if (strpos($content, $needle) === false) {
					throw new Exception("Missing expected migration marker in $file: $needle");
				}
			}
			foreach ($expect['forbidden'] as $needle) {
				if (strpos($content, $needle) !== false) {
					throw new Exception("Forbidden legacy pattern still present in $file: $needle");
				}
			}
		}

		echo ' [OK]' . PHP_EOL;

		echo PHP_EOL . 'All PROJ-22 identifier tests passed.' . PHP_EOL;
		return true;

	} catch (Throwable $e) {
		echo PHP_EOL . 'FAILED: ' . $e->getMessage() . PHP_EOL;
		return false;
	}
