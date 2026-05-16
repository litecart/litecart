<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## Defense-in-depth: <script> tags stripped from subject
		########################################################################

		$email = new ent_email();
		$email->set_subject('Hello <script>alert(1)</script> world');

		if (str_contains($email->data['subject'], '<script>') || str_contains($email->data['subject'], '</script>')) {
			throw new Exception('set_subject() must strip <script> tags. Got: '. $email->data['subject']);
		}

		if (!str_contains($email->data['subject'], 'Hello') || !str_contains($email->data['subject'], 'world')) {
			throw new Exception('set_subject() must keep surrounding text intact. Got: '. $email->data['subject']);
		}

		########################################################################
		## Defense-in-depth: <style> tags stripped from subject
		########################################################################

		$email->set_subject('Promo <style>body{display:none}</style> Sale');

		if (str_contains($email->data['subject'], '<style>') || str_contains($email->data['subject'], '</style>')) {
			throw new Exception('set_subject() must strip <style> tags. Got: '. $email->data['subject']);
		}

		########################################################################
		## Tag stripping is case-insensitive
		########################################################################

		$email->set_subject('Test <SCRIPT>x</SCRIPT> case');

		if (stripos($email->data['subject'], '<script') !== false || stripos($email->data['subject'], '</script') !== false) {
			throw new Exception('set_subject() must strip <script> tags case-insensitively. Got: '. $email->data['subject']);
		}

		########################################################################
		## Existing CRLF / tab sanitization remains intact
		########################################################################

		$email->set_subject("Subject with\r\ninjected headers");

		if (preg_match('#[\r\n\t]#', $email->data['subject'])) {
			throw new Exception('set_subject() must keep CRLF/tab sanitization. Got: '. $email->data['subject']);
		}

		########################################################################
		## Benign HTML entities pass through (escape happens at render layer)
		########################################################################

		$email->set_subject('Order #1234 — confirmation');

		if ($email->data['subject'] !== 'Order #1234 — confirmation') {
			throw new Exception('set_subject() must not alter benign content. Got: '. $email->data['subject']);
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {
		// No DB writes — no rollback needed
	}
