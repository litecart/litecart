<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## TC-01: Constructor accepts a {locale => string} map
		########################################################################

		$name = new type_translation([
			'en' => 'Rubber Ducks',
			'de' => 'Gummienten',
			'sv' => 'Gummiankor',
		]);

		if ($name->in('en') !== 'Rubber Ducks') {
			throw new Exception('TC-01: en lookup failed (got "'. $name->in('en') .'")');
		}

		if ($name->in('de') !== 'Gummienten') {
			throw new Exception('TC-01: de lookup failed');
		}

		########################################################################
		## TC-02: Missing locale falls back through the documented chain
		########################################################################

		$name = new type_translation([
			'en' => 'Rubber Ducks',
			'de' => 'Gummienten',
		]);

		// French not present — chain is: fr → store_default → en → first
		$result = $name->in('fr');
		if ($result === '' || ($result !== 'Rubber Ducks' && $result !== 'Gummienten')) {
			throw new Exception('TC-02: fr fallback should yield a populated value (got "'. $result .'")');
		}

		########################################################################
		## TC-03: Bug check — fallback loop uses the loop variable, not the member
		########################################################################

		// Tim's original draft had a bug where the fallback loop read
		// $this->_language_code instead of the loop var, silently disabling
		// the fallback. Verify by constructing a translation without the
		// active locale entry and confirming we still get a value.
		$active = isset(language::$selected['code']) ? language::$selected['code'] : 'en';
		$other_locale = ($active === 'en') ? 'de' : 'en';

		$name = new type_translation([
			$other_locale => 'Only here',
		]);

		// Active locale ('en' or whatever) isn't in the map — must fall back.
		if ($name->in($active) !== 'Only here') {
			throw new Exception('TC-03: fallback regression — expected "Only here", got "'. $name->in($active) .'"');
		}

		########################################################################
		## TC-04: __toString renders the active locale
		########################################################################

		$active = isset(language::$selected['code']) ? language::$selected['code'] : 'en';

		$name = new type_translation([
			$active => 'Active locale value',
			'xx'    => 'Other locale value',
		]);

		if ((string)$name !== 'Active locale value') {
			throw new Exception('TC-04: __toString should render the active locale (got "'. (string)$name .'")');
		}

		########################################################################
		## TC-05: String constructor seeds the requested locale
		########################################################################

		$name = new type_translation('Hello', 'en');

		if ($name->in('en') !== 'Hello') {
			throw new Exception('TC-05: string constructor did not seed the requested locale');
		}

		########################################################################
		## TC-06: type_translation copy constructor clones the payload
		########################################################################

		$original = new type_translation(['en' => 'A', 'de' => 'B']);
		$copy = new type_translation($original);

		if ($copy->in('en') !== 'A' || $copy->in('de') !== 'B') {
			throw new Exception('TC-06: copy constructor lost entries');
		}

		########################################################################
		## TC-07: set() updates one locale without disturbing others
		########################################################################

		$name = new type_translation(['en' => 'Hello', 'de' => 'Hallo']);
		$name->set('fr', 'Bonjour');

		if ($name->in('fr') !== 'Bonjour' || $name->in('en') !== 'Hello' || $name->in('de') !== 'Hallo') {
			throw new Exception('TC-07: set() should add without disturbing existing entries');
		}

		########################################################################
		## TC-08: has() reports populated entries only
		########################################################################

		$name = new type_translation(['en' => 'Hello', 'de' => '']);

		if (!$name->has('en')) {
			throw new Exception('TC-08: has(en) should be true');
		}

		if ($name->has('de')) {
			throw new Exception('TC-08: has(de) should be false for empty string');
		}

		if ($name->has('fr')) {
			throw new Exception('TC-08: has(fr) should be false for missing');
		}

		########################################################################
		## TC-09: locales() lists populated codes only
		########################################################################

		$name = new type_translation(['en' => 'Hello', 'de' => 'Hallo', 'fr' => '']);
		$locales = $name->locales();

		if (!in_array('en', $locales) || !in_array('de', $locales)) {
			throw new Exception('TC-09: populated entries missing from locales()');
		}

		if (in_array('fr', $locales)) {
			throw new Exception('TC-09: empty entries should not appear in locales()');
		}

		########################################################################
		## TC-10: ArrayAccess — read pattern $name['de'] matches in()
		########################################################################

		$name = new type_translation(['en' => 'Hello', 'de' => 'Hallo']);

		if ($name['de'] !== 'Hallo') {
			throw new Exception('TC-10: ArrayAccess read failed (got "'. $name['de'] .'")');
		}

		if (!isset($name['de'])) {
			throw new Exception('TC-10: isset($name[de]) should be true');
		}

		if (isset($name['fr'])) {
			throw new Exception('TC-10: isset($name[fr]) should be false');
		}

		########################################################################
		## TC-11: ArrayAccess — write pattern $name['de'] = '...' calls set
		########################################################################

		$name = new type_translation(['en' => 'Hello']);
		$name['de'] = 'Hallo';

		if ($name->in('de') !== 'Hallo') {
			throw new Exception('TC-11: ArrayAccess write failed');
		}

		########################################################################
		## TC-12: jsonSerialize returns the locale-map shape unchanged
		########################################################################

		$payload = ['en' => 'Hello', 'de' => 'Hallo', 'sv' => 'Hej'];
		$name = new type_translation($payload);

		$serialized = $name->jsonSerialize();
		if ($serialized !== $payload) {
			throw new Exception('TC-12: jsonSerialize shape mismatch (got '. json_encode($serialized) .')');
		}

		// Round-trip through json_encode / json_decode
		$decoded = json_decode(json_encode($name), true);
		$rebuilt = new type_translation($decoded);

		if ($rebuilt->in('en') !== 'Hello' || $rebuilt->in('de') !== 'Hallo' || $rebuilt->in('sv') !== 'Hej') {
			throw new Exception('TC-12: json round-trip lost entries');
		}

		########################################################################
		## TC-13: Empty constructor stays empty
		########################################################################

		$name = new type_translation();

		if ((string)$name !== '' || !empty($name->locales())) {
			throw new Exception('TC-13: empty constructor should yield empty state');
		}

		########################################################################
		## TC-14: offsetUnset removes a single locale entry
		########################################################################

		$name = new type_translation(['en' => 'Hello', 'de' => 'Hallo']);
		unset($name['de']);

		if ($name->has('de')) {
			throw new Exception('TC-14: offsetUnset should remove the locale');
		}

		if (!$name->has('en')) {
			throw new Exception('TC-14: offsetUnset should not disturb other locales');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {
		// No DB writes — no rollback needed
	}
