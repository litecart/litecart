<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Store original state
		$original = notices::$data;

		########################################################################
		## Add and get notices
		########################################################################

		notices::reset();

		notices::add('errors', 'Something went wrong');
		notices::add('success', 'Item saved');

		$errors = notices::get('errors');
		if (empty($errors) || $errors[0] !== 'Something went wrong') {
			throw new Exception('notices::add/get failed for errors');
		}

		$success = notices::get('success');
		if (empty($success) || $success[0] !== 'Item saved') {
			throw new Exception('notices::add/get failed for success');
		}

		########################################################################
		## Add with key
		########################################################################

		notices::add('warnings', 'Duplicate entry', 'duplicate');

		$warnings = notices::get('warnings');
		if (empty($warnings['duplicate']) || $warnings['duplicate'] !== 'Duplicate entry') {
			throw new Exception('notices::add with key failed');
		}

		########################################################################
		## Remove specific notice
		########################################################################

		notices::remove('warnings', 'duplicate');

		$warnings = notices::get('warnings');
		if (isset($warnings['duplicate'])) {
			throw new Exception('notices::remove failed');
		}

		########################################################################
		## Dump (get and clear)
		########################################################################

		notices::add('notices', 'FYI message');

		$dumped = notices::dump('notices');
		if (empty($dumped) || $dumped[0] !== 'FYI message') {
			throw new Exception('notices::dump failed to return notices');
		}

		$after_dump = notices::get('notices');
		if (!empty($after_dump)) {
			throw new Exception('notices::dump failed to clear notices');
		}

		########################################################################
		## Reset specific type
		########################################################################

		notices::add('errors', 'Error 1');
		notices::add('success', 'Success 1');

		notices::reset('errors');

		$errors = notices::get('errors');
		$success = notices::get('success');

		if (!empty($errors)) {
			throw new Exception('notices::reset(type) failed to clear errors');
		}

		if (empty($success)) {
			throw new Exception('notices::reset(type) cleared other types');
		}

		########################################################################
		## Reset all
		########################################################################

		notices::reset();

		$errors = notices::get('errors');
		$success = notices::get('success');

		if (!empty($errors) || !empty($success)) {
			throw new Exception('notices::reset() failed to clear all');
		}

		########################################################################
		## Non-existent type
		########################################################################

		$result = notices::get('nonexistent');
		if ($result !== false) {
			throw new Exception('notices::get for non-existent type should return false');
		}

		// Restore original state
		notices::$data = $original;

		return true;

	} catch (Exception $e) {

		notices::$data = $original ?? [];
		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
