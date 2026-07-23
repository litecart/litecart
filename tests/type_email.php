<?php

	include_once __DIR__.'/../public_html/includes/types/type_email.inc.php';

	try {

		########################################################################
		## TC-01: Constructor parses bare address
		########################################################################

		$email = new type_email('john@example.com');

		if ($email->local !== 'john') {
			throw new Exception('TC-01: local not parsed (got "'. $email->local .'")');
		}

		if ($email->domain !== 'example.com') {
			throw new Exception('TC-01: domain not parsed (got "'. $email->domain .'")');
		}

		if ($email->name !== '') {
			throw new Exception('TC-01: name should be empty (got "'. $email->name .'")');
		}

		if ((string)$email !== 'john@example.com') {
			throw new Exception('TC-01: __toString wrong (got "'. (string)$email .'")');
		}

		########################################################################
		## TC-02: Constructor parses "Name <addr>" form
		########################################################################

		$email = new type_email('John Doe <john@example.com>');

		if ($email->name !== 'John Doe') {
			throw new Exception('TC-02: display name not parsed (got "'. $email->name .'")');
		}

		if ($email->address !== 'john@example.com') {
			throw new Exception('TC-02: address not parsed (got "'. $email->address .'")');
		}

		########################################################################
		## TC-03: Quoted display name with special chars round-trips
		########################################################################

		$email = new type_email('"John, Jr." <john@example.com>');

		if ($email->name !== 'John, Jr.') {
			throw new Exception('TC-03: quoted name not parsed (got "'. $email->name .'")');
		}

		if ((string)$email !== '"John, Jr." <john@example.com>') {
			throw new Exception('TC-03: quoted name round-trip failed (got "'. (string)$email .'")');
		}

		########################################################################
		## TC-04: Bracketed-only form ("<addr>") drops the brackets
		########################################################################

		$email = new type_email('<just@addr.com>');

		if ($email->name !== '' || $email->address !== 'just@addr.com') {
			throw new Exception('TC-04: bracketed-only form not parsed correctly');
		}

		########################################################################
		## TC-05: Surrounding whitespace is trimmed
		########################################################################

		$email = new type_email('  spaced@example.com  ');

		if ($email->local !== 'spaced' || $email->domain !== 'example.com') {
			throw new Exception('TC-05: whitespace not trimmed');
		}

		########################################################################
		## TC-06: Domain is normalised to lowercase, local preserves case
		########################################################################

		$email = new type_email('Mixed@CASE.com');

		if ($email->local !== 'Mixed') {
			throw new Exception('TC-06: local case should be preserved (got "'. $email->local .'")');
		}

		if ($email->domain !== 'case.com') {
			throw new Exception('TC-06: domain should be lowercase (got "'. $email->domain .'")');
		}

		########################################################################
		## TC-07: Array constructor for drop-in compatibility with ent_email
		########################################################################

		$email = new type_email(['email' => 'a@b.c', 'name' => 'X']);

		if ($email->address !== 'a@b.c' || $email->name !== 'X') {
			throw new Exception('TC-07: array constructor failed');
		}

		########################################################################
		## TC-08: type_email constructor argument copies all components
		########################################################################

		$original = new type_email('Alice <alice@example.com>');
		$copy = new type_email($original);

		if ($copy->address !== 'alice@example.com' || $copy->name !== 'Alice') {
			throw new Exception('TC-08: type_email copy constructor failed');
		}

		########################################################################
		## TC-09: jsonSerialize returns ent_email-compatible shape
		########################################################################

		$email = new type_email('John <john@x.com>');
		$serialized = $email->jsonSerialize();

		if (!is_array($serialized) || ($serialized['email'] ?? null) !== 'john@x.com' || ($serialized['name'] ?? null) !== 'John') {
			throw new Exception('TC-09: jsonSerialize shape mismatch (got '. json_encode($serialized) .')');
		}

		########################################################################
		## TC-10: Setting address re-splits local/domain
		########################################################################

		$email = new type_email('old@old.com');
		$email->address = 'new@new.com';

		if ($email->local !== 'new' || $email->domain !== 'new.com') {
			throw new Exception('TC-10: address setter did not re-split');
		}

		########################################################################
		## TC-11: Setting local or domain alone updates the composed address
		########################################################################

		$email = new type_email('john@example.com');
		$email->local = 'jane';

		if ($email->address !== 'jane@example.com') {
			throw new Exception('TC-11a: local setter did not update address (got "'. $email->address .'")');
		}

		$email->domain = 'OTHER.com';

		if ($email->address !== 'jane@other.com') {
			throw new Exception('TC-11b: domain setter should normalise to lowercase (got "'. $email->address .'")');
		}

		########################################################################
		## TC-12: is_valid reflects the standard validate_email regex
		########################################################################

		if (!(new type_email('test@example.com'))->is_valid) {
			throw new Exception('TC-12a: well-formed address should be valid');
		}

		if ((new type_email('not-an-email'))->is_valid) {
			throw new Exception('TC-12b: malformed input should not be valid');
		}

		if ((new type_email(''))->is_valid) {
			throw new Exception('TC-12c: empty input should not be valid');
		}

		########################################################################
		## TC-13: Name setter strips CRLF / tabs (header-injection guard)
		########################################################################

		$email = new type_email('john@example.com');
		$email->name = "Evil\r\nBcc: leaked@attacker.com";

		if (preg_match('#[\r\n\t]#', $email->name)) {
			throw new Exception('TC-13: name setter must strip CRLF/tabs (got '. json_encode($email->name) .')');
		}

		########################################################################
		## TC-14: Empty input stays empty (no defaults injected)
		########################################################################

		$email = new type_email('');

		if ($email->address !== '' || $email->name !== '' || (string)$email !== '') {
			throw new Exception('TC-14: empty constructor should yield empty components');
		}

		########################################################################
		## TC-15: Unknown component access triggers a warning (defense)
		########################################################################

		set_error_handler(function($severity, $message) {
			throw new ErrorException($message, 0, $severity);
		}, E_USER_WARNING);

		try {
			$email = new type_email('john@example.com');
			$_ = $email->unknown_property;
			throw new Exception('TC-15: expected E_USER_WARNING on unknown component');
		} catch (ErrorException $ex) {
			if (!str_contains($ex->getMessage(), 'Unknown email component')) {
				throw new Exception('TC-15: wrong warning message: '. $ex->getMessage());
			}
		} finally {
			restore_error_handler();
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {
		// No DB writes — no rollback needed
	}
