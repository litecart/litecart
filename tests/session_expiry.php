<?php

	include_once __DIR__.'/../src/shared/app_header.inc.php';

	try {

		database::begin_transaction();

		########################################################################
		## Expired session row must be rejected by session::load()
		########################################################################

		$session_id = bin2hex(random_bytes(24));
		$payload = f::format_json([
			'id' => $session_id,
			'customer' => ['id' => 999999, 'email' => 'ghost@example.com'],
			'administrator' => ['id' => 888888, 'username' => 'ghostadmin'],
		]);

		database::query(
			"insert into ". DB_TABLE_PREFIX ."sessions
			(id, data, expires_at, updated_at, created_at)
			values (
				'". database::input($session_id) ."',
				'". database::input($payload) ."',
				'". database::input(date('Y-m-d H:i:s', strtotime('-1 hour'))) ."',
				'". database::input(date('Y-m-d H:i:s', strtotime('-2 hours'))) ."',
				'". database::input(date('Y-m-d H:i:s', strtotime('-2 hours'))) ."'
			);"
		);

		$loaded = session::load($session_id);

		if ($loaded !== false) {
			throw new Exception('session::load() accepted an expired session row');
		}

		if (!empty(session::$data['customer']['id'])) {
			throw new Exception('Expired session payload leaked into session::$data');
		}

		if (empty(session::$data['id']) || session::$data['id'] === $session_id) {
			throw new Exception('Expired session load did not rotate the session id');
		}

		// Rebind the session-backed references the same way customer::init() / administrator::init()
		// would — session::reset() replaced the underlying array, so any earlier references are stale.
		if (!isset(session::$data['customer']) || !is_array(session::$data['customer'])) {
			session::$data['customer'] = [];
		}
		if (!isset(session::$data['administrator']) || !is_array(session::$data['administrator'])) {
			session::$data['administrator'] = [];
		}
		customer::$data = &session::$data['customer'];
		administrator::$data = &session::$data['administrator'];

		if (customer::check_login() === true) {
			throw new Exception('customer::check_login() returned true after expired session rejection');
		}

		if (administrator::check_login() === true) {
			throw new Exception('administrator::check_login() returned true after expired session rejection');
		}

		########################################################################
		## Fresh (non-expired) session row must still load correctly
		########################################################################

		$fresh_id = bin2hex(random_bytes(24));
		$fresh_payload = f::format_json([
			'id' => $fresh_id,
			'customer' => ['id' => 42, 'email' => 'alive@example.com'],
		]);

		database::query(
			"insert into ". DB_TABLE_PREFIX ."sessions
			(id, data, expires_at, updated_at, created_at)
			values (
				'". database::input($fresh_id) ."',
				'". database::input($fresh_payload) ."',
				'". database::input(date('Y-m-d H:i:s', strtotime('+1 hour'))) ."',
				'". database::input(date('Y-m-d H:i:s')) ."',
				'". database::input(date('Y-m-d H:i:s')) ."'
			);"
		);

		$loaded = session::load($fresh_id);

		if ($loaded !== true) {
			throw new Exception('session::load() rejected a non-expired session row');
		}

		if (empty(session::$data['customer']['id']) || session::$data['customer']['id'] !== 42) {
			throw new Exception('Fresh session payload did not restore correctly');
		}

		########################################################################
		## session::save() must populate expires_at on INSERT, not just UPDATE
		########################################################################

		$save_id = bin2hex(random_bytes(24));
		session::reset();
		session::$data['id'] = $save_id;
		session::$data['probe'] = 'insert-sets-expiry';
		session::save();

		$row = database::query(
			"select expires_at from ". DB_TABLE_PREFIX ."sessions
			where id = '". database::input($save_id) ."'
			limit 1;"
		)->fetch();

		if (empty($row['expires_at'])) {
			throw new Exception('session::save() did not populate expires_at on INSERT');
		}

		if (strtotime($row['expires_at']) <= time()) {
			throw new Exception('session::save() populated expires_at in the past');
		}

		session::reset();
		$loaded = session::load($save_id);

		if ($loaded !== true) {
			throw new Exception('session::load() could not reload a freshly saved session');
		}

		if (empty(session::$data['probe']) || session::$data['probe'] !== 'insert-sets-expiry') {
			throw new Exception('Freshly saved session payload did not round-trip');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		database::rollback();
	}
