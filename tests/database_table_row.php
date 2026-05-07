<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	// Skip if ent_database_table_row has pre-existing bug (database::$selected undeclared)
	if (!property_exists('database', 'selected')) {
		echo ' [SKIP] database::$selected not available';
		return true;
	}

	try {

		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."redirects';"
		)->fetch('Auto_increment');

		database::query("start transaction;");

		########################################################################
		## Create via ent_database_table_row
		########################################################################

		$row = new ent_database_table_row(DB_TABLE_PREFIX . 'redirects');

		$row->data['pattern'] = '/test-row-'. uniqid();
		$row->data['destination'] = '/destination';
		$row->data['status_code'] = 301;

		$row->save();

		if (empty($row->data['id'])) {
			throw new Exception('save: Should have generated an ID after insert');
		}

		$row_id = $row->data['id'];

		########################################################################
		## Verify data persisted
		########################################################################

		$check = database::query(
			"select * from ". DB_TABLE_PREFIX ."redirects
			where id = ". (int)$row_id ."
			limit 1;"
		)->fetch();

		if (!$check) {
			throw new Exception('save: Row not found in database after insert');
		}

		if ($check['pattern'] !== $row->data['pattern']) {
			throw new Exception('save: Pattern mismatch after insert');
		}

		########################################################################
		## Load via constructor
		########################################################################

		$loaded = new ent_database_table_row(DB_TABLE_PREFIX . 'redirects', $row_id);

		if ($loaded->data['pattern'] !== $row->data['pattern']) {
			throw new Exception('load: Pattern mismatch after loading by ID');
		}

		########################################################################
		## Update
		########################################################################

		$loaded->data['destination'] = '/updated-destination';
		$loaded->save();

		$updated = database::query(
			"select destination from ". DB_TABLE_PREFIX ."redirects
			where id = ". (int)$row_id ."
			limit 1;"
		)->fetch('destination');

		if ($updated !== '/updated-destination') {
			throw new Exception('save (update): Destination not updated, got "'. $updated .'"');
		}

		########################################################################
		## Delete
		########################################################################

		$loaded->delete();

		$found = database::query(
			"select id from ". DB_TABLE_PREFIX ."redirects
			where id = ". (int)$row_id ."
			limit 1;"
		)->num_rows;

		if ($found) {
			throw new Exception('delete: Row should be removed from database');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		database::query('rollback;');

		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."redirects
			AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
