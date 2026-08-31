<?php

	include_once __DIR__.'/../src/shared/app_header.inc.php';

	try {

		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_PREFIX ."customers';"
		)->fetch('Auto_increment');

		database::begin_transaction();

		########################################################################
		## Create a customer
		########################################################################

		$customer = new ent_customer();
		$customer->data = f::array_update($customer->data, [
			'status' => 1,
			'firstname' => 'Revoke',
			'lastname' => 'Target',
			'email' => 'revoke.target@example.com',
			'phone' => '555-0102',
			'password' => 'InitialPass!2',
		]);
		$customer->save();

		$customer_id = $customer->data['id'];

		if (!$customer_id) {
			throw new Exception('Failed to create test customer');
		}

		########################################################################
		## Simulate the reset flow: set_password + sessions_expiry = now + save
		########################################################################

		$customer = new ent_customer($customer_id);
		$customer->set_password('BrandNew!Pass43');
		$customer->data['sessions_expiry'] = date('Y-m-d H:i:s');
		$customer->save();

		$row = database::query(
			"select * from ". DB_PREFIX ."customers
			where id = ". (int)$customer_id ."
			limit 1;"
		)->fetch();

		if (empty($row['sessions_expiry'])) {
			throw new Exception('sessions_expiry was not persisted after save()');
		}

		if (abs(strtotime($row['sessions_expiry']) - time()) > 5) {
			throw new Exception('sessions_expiry is not near current time (got '. $row['sessions_expiry'] .')');
		}

		########################################################################
		## Verify the live enforcement helper (customer::is_session_expired)
		## — same code path that nod_customer::init() uses to revoke stale sessions.
		########################################################################

		// Older session timestamp → expired (another device still holding an old session).
		security::$data['timestamp'] = strtotime($row['sessions_expiry']) - 3600;
		if (customer::is_session_expired($row) !== true) {
			throw new Exception('is_session_expired() accepted an older customer_security_timestamp');
		}

		// Newer session timestamp → still valid (the device that actually performed the reset).
		security::$data['timestamp'] = strtotime($row['sessions_expiry']) + 3600;
		if (customer::is_session_expired($row) !== false) {
			throw new Exception('is_session_expired() rejected a newer customer_security_timestamp');
		}

		// Missing timestamp (legacy / never initialised) → expired.
		unset(security::$data['timestamp']);
		if (customer::is_session_expired($row) !== true) {
			throw new Exception('is_session_expired() accepted a missing customer_security_timestamp');
		}

		########################################################################
		## Baseline: a customer without sessions_expiry must not be flagged,
		## regardless of session state.
		########################################################################

		$baseline = ['sessions_expiry' => null];
		unset(security::$data['timestamp']);
		if (customer::is_session_expired($baseline) !== false) {
			throw new Exception('is_session_expired() incorrectly flagged a customer without sessions_expiry');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		database::rollback();

		database::query(
			"ALTER TABLE ". DB_PREFIX ."customers AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
