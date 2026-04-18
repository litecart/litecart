<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## edit.inc.php must run password_check_strength on new_password,
		## never on the current-password field.
		########################################################################

		$path = f::file_resolve_path(__DIR__.'/../public_html/frontend/pages/account/edit.inc.php');
		$content = file_get_contents($path);

		if ($content === false) {
			throw new Exception('Could not read edit.inc.php');
		}

		if (!preg_match('/password_check_strength\(\s*\$_POST\[\s*\'new_password\'\s*\]/', $content)) {
			throw new Exception('edit.inc.php does not call password_check_strength on $_POST[new_password]');
		}

		if (preg_match('/password_check_strength\(\s*\$_POST\[\s*\'password\'\s*\]/', $content)) {
			throw new Exception('edit.inc.php still calls password_check_strength on $_POST[password] (typo regression)');
		}

		########################################################################
		## Behavioural contract: weak new password must be rejected,
		## strong new password must be accepted by the helper.
		########################################################################

		if (f::password_check_strength('weak')) {
			throw new Exception('password_check_strength accepted a weak password');
		}

		if (!f::password_check_strength('Str0ng!Pass99')) {
			throw new Exception('password_check_strength rejected a strong password');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
