<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## Valid email addresses
		########################################################################

		$valid_emails = [
			'user@example.com',
			'user.name@example.com',
			'user+tag@example.com',
			'user@sub.domain.com',
			'user123@example.co.uk',
		];

		foreach ($valid_emails as $email) {
			if (!f::validate_email($email)) {
				throw new Exception('validate_email rejected valid email: '. $email);
			}
		}

		########################################################################
		## Invalid email addresses
		########################################################################

		$invalid_emails = [
			'',
			'@example.com',
			'user@',
			'user@.com',
			'user example.com',
			'user@@example.com',
		];

		foreach ($invalid_emails as $email) {
			if (f::validate_email($email)) {
				throw new Exception('validate_email accepted invalid email: '. $email);
			}
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
