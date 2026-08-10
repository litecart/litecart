<?php

	include_once __DIR__.'/../src/shared/app_header.inc.php';

	try {

		$store_locale = (string)settings::get('store_language_code');

		// Reusable translation map with the store locale + one more for cross-locale checks.
		$translation = new type_translation([
			$store_locale => 'Hello',
			'de'          => 'Hallo',
		]);

		########################################################################
		## TC-01: form_regional_text — bare string still works
		########################################################################

		$html = f::form_regional_text('title['. $store_locale .']', $store_locale, 'Hello');

		if (strpos($html, 'value="Hello"') === false) {
			throw new Exception('TC-01: bare string must render as input value (got: '. $html .')');
		}

		if (strpos($html, $store_locale) === false) {
			throw new Exception('TC-01: language code badge must appear in rendered output');
		}

		########################################################################
		## TC-02: form_regional_text — type_translation instance accepted
		########################################################################

		$html = f::form_regional_text('title['. $store_locale .']', $store_locale, $translation);

		if (strpos($html, 'value="Hello"') === false) {
			throw new Exception('TC-02: type_translation must render the value for $language_code (got: '. $html .')');
		}

		$html_de = f::form_regional_text('title[de]', 'de', $translation);

		if (strpos($html_de, 'value="Hallo"') === false) {
			throw new Exception('TC-02: type_translation must pick the de value when language_code=de (got: '. $html_de .')');
		}

		########################################################################
		## TC-03: form_regional_textarea — type_translation instance accepted
		########################################################################

		$html = f::form_regional_textarea('body[de]', 'de', $translation);

		if (strpos($html, 'Hallo') === false) {
			throw new Exception('TC-03: form_regional_textarea must render the localised value (got: '. $html .')');
		}

		########################################################################
		## TC-04: form_regional_wysiwyg — type_translation instance accepted
		########################################################################

		$html = f::form_regional_wysiwyg('content[de]', 'de', $translation);

		if (strpos($html, 'Hallo') === false) {
			throw new Exception('TC-04: form_regional_wysiwyg must render the localised value (got: '. $html .')');
		}

		########################################################################
		## TC-05: form_regional (the master with $type param) — type_translation accepted
		########################################################################

		$html = f::form_regional('h1[de]', 'de', $translation, 'text');

		if (strpos($html, 'value="Hallo"') === false) {
			throw new Exception('TC-05: form_regional must render the localised value (got: '. $html .')');
		}

		########################################################################
		## TC-06: Empty $language_code falls back to store_language_code
		########################################################################

		$html = f::form_regional_text('title', '', 'Hi');

		if (strpos($html, $store_locale) === false) {
			throw new Exception('TC-06: empty $language_code must fall back to store_language_code (got: '. $html .')');
		}

		########################################################################
		## TC-07: type_translation locale fallback chain when locale not in map
		########################################################################
		##
		## type_translation::in() falls through to store locale and then 'en'.
		## Form helper should hand off to in() and accept whatever it returns.
		########################################################################

		$partial = new type_translation(['en' => 'Fallback']);
		$html = f::form_regional_text('title[xx]', 'xx', $partial);

		// in('xx') falls back through store + 'en' — we accept either, but value
		// must not be empty and must reflect a fallback hit.
		if (strpos($html, 'value=""') !== false) {
			throw new Exception('TC-07: type_translation fallback must yield a non-empty value (got: '. $html .')');
		}

		########################################################################
		## TC-08: No E_USER_DEPRECATED on normal call patterns
		########################################################################

		set_error_handler(function($severity, $message) {
			throw new ErrorException($message, 0, $severity);
		}, E_USER_DEPRECATED);

		try {
			f::form_regional_text('name['. $store_locale .']', $store_locale, true);
			f::form_regional_textarea('body['. $store_locale .']', $store_locale, true);
			f::form_regional_wysiwyg('content['. $store_locale .']', $store_locale, true);
			f::form_regional('h1['. $store_locale .']', $store_locale, true, 'text');
			f::form_regional_text('name['. $store_locale .']', $store_locale, $translation);
		} catch (ErrorException $ex) {
			throw new Exception('TC-08: unexpected E_USER_DEPRECATED on normal call: '. $ex->getMessage());
		} finally {
			restore_error_handler();
		}

		########################################################################
		## TC-09: Legacy swap-hack signature no longer reorders args
		########################################################################
		##
		## Before: f::form_regional_textarea('de', 'body', $v) triggered the
		## ^[a-z]{2}$ swap and emitted E_USER_DEPRECATED. After: no swap, the
		## name is treated literally as 'de'. Verify silent + literal $name.
		########################################################################

		set_error_handler(function($severity, $message) {
			throw new ErrorException($message, 0, $severity);
		}, E_USER_DEPRECATED);

		try {
			$html = f::form_regional_textarea('de', 'body', 'X');
		} catch (ErrorException $ex) {
			throw new Exception('TC-09: deprecation-swap hack must be removed for form_regional_textarea (got: '. $ex->getMessage() .')');
		} finally {
			restore_error_handler();
		}

		if (strpos($html, 'name="de"') === false) {
			throw new Exception('TC-09: $name must be honoured verbatim post-refactor (got: '. $html .')');
		}

		// Same check for form_regional_wysiwyg
		set_error_handler(function($severity, $message) {
			throw new ErrorException($message, 0, $severity);
		}, E_USER_DEPRECATED);

		try {
			$html = f::form_regional_wysiwyg('de', 'content', 'X');
		} catch (ErrorException $ex) {
			throw new Exception('TC-09: deprecation-swap hack must be removed for form_regional_wysiwyg (got: '. $ex->getMessage() .')');
		} finally {
			restore_error_handler();
		}

		// Same check for form_regional itself
		set_error_handler(function($severity, $message) {
			throw new ErrorException($message, 0, $severity);
		}, E_USER_DEPRECATED);

		try {
			$html = f::form_regional('de', 'title', 'X', 'text');
		} catch (ErrorException $ex) {
			throw new Exception('TC-09: deprecation-swap hack must be removed for form_regional (got: '. $ex->getMessage() .')');
		} finally {
			restore_error_handler();
		}

		########################################################################
		## TC-10: Array attributes still work alongside type_translation
		########################################################################

		$html = f::form_regional_text('title[de]', 'de', $translation, ['data-test' => 'ok']);

		if (strpos($html, 'data-test="ok"') === false) {
			throw new Exception('TC-10: array attributes must merge through (got: '. $html .')');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {
		// No DB writes — no rollback needed
	}
