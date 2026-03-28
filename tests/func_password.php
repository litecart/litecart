<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## password_generate — length
		########################################################################

		$pw = f::password_generate(12);

		if (strlen($pw) !== 12) {
			throw new Exception('password_generate returned wrong length: '. strlen($pw) .' (expected 12)');
		}

		########################################################################
		## password_generate — character classes
		########################################################################

		$pw = f::password_generate(16, 2, 2, 2, 2);

		if (!preg_match('#[a-z]#', $pw)) {
			throw new Exception('password_generate missing lowercase characters');
		}

		if (!preg_match('#[A-Z]#', $pw)) {
			throw new Exception('password_generate missing uppercase characters');
		}

		if (!preg_match('#\d#', $pw)) {
			throw new Exception('password_generate missing numbers');
		}

		if (!preg_match('#[^\w]#', $pw)) {
			throw new Exception('password_generate missing special characters');
		}

		########################################################################
		## password_generate — uniqueness (CSPRNG)
		########################################################################

		$pw1 = f::password_generate(20);
		$pw2 = f::password_generate(20);

		if ($pw1 === $pw2) {
			throw new Exception('password_generate produced identical passwords');
		}

		########################################################################
		## password_check_strength
		########################################################################

		if (!f::password_check_strength('Str0ng!Pass')) {
			throw new Exception('password_check_strength rejected a strong password');
		}

		if (f::password_check_strength('weak')) {
			throw new Exception('password_check_strength accepted a weak password');
		}

		if (f::password_check_strength('')) {
			throw new Exception('password_check_strength accepted empty string');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
