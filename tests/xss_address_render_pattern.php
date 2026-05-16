<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## format_address() returns raw text — caller must escape (documented contract)
		########################################################################

		$address = [
			'firstname'    => '<img onerror=alert(1) src=x>',
			'lastname'     => 'Doe',
			'company'      => '',
			'address1'     => '"><svg/onload=alert(2)>',
			'address2'     => '',
			'city'         => 'Test',
			'postcode'     => '12345',
			'country_code' => 'DE',
			'zone_code'    => '',
		];

		$raw = f::format_address($address);

		// Sanity: format_address embeds raw values (current contract)
		if (!str_contains($raw, '<img onerror=alert(1) src=x>')) {
			throw new Exception('format_address() unexpectedly stripped raw payload (contract changed?). Got: '. $raw);
		}

		########################################################################
		## The canonical safe-render pattern: nl2br(escape_html(format_address(...)))
		########################################################################

		$safe = nl2br(f::escape_html($raw));

		// Payloads must be escaped — no raw <img / <svg in safe output
		if (str_contains($safe, '<img') || str_contains($safe, '<svg')) {
			throw new Exception('Safe-render pattern leaked unescaped HTML tags. Got: '. $safe);
		}

		// Escape entities must be present
		if (!str_contains($safe, '&lt;img') || !str_contains($safe, '&lt;svg')) {
			throw new Exception('Safe-render pattern did not produce HTML entities. Got: '. $safe);
		}

		// Quotes inside attributes must be escaped (ENT_QUOTES)
		if (str_contains($safe, '"><svg') || str_contains($safe, "'><svg")) {
			throw new Exception('Safe-render pattern did not escape quotes (ENT_QUOTES). Got: '. $safe);
		}

		########################################################################
		## Line breaks survive escape — nl2br adds <br /> tags after escape
		########################################################################

		// nl2br runs AFTER escape_html — so <br /> tags must be present and intact
		if (!str_contains($safe, '<br />')) {
			throw new Exception('nl2br must run after escape_html — <br /> missing. Got: '. $safe);
		}

		// <br /> itself must not be double-escaped
		if (str_contains($safe, '&lt;br')) {
			throw new Exception('<br /> tag was incorrectly escaped — wrong nl2br/escape_html order. Got: '. $safe);
		}

		########################################################################
		## Order/reverse: escape_html(nl2br(...)) is WRONG — verify failure mode
		########################################################################

		$wrong = f::escape_html(nl2br($raw));

		// The wrong order produces escaped <br /> tags — verify the test catches this
		if (!str_contains($wrong, '&lt;br') && str_contains($wrong, '<br />')) {
			throw new Exception('Test assumption broken: reverse order should produce escaped <br /> tags');
		}

		########################################################################
		## Empty / null inputs must not break the pattern
		########################################################################

		$empty_safe = nl2br(f::escape_html(f::format_address([
			'country_code' => 'DE',
		])));

		if ($empty_safe === false || $empty_safe === null) {
			throw new Exception('Safe-render pattern broke on near-empty address');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {
		// No DB writes — no rollback needed
	}
