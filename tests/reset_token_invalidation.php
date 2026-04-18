<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."customers';"
		)->fetch('Auto_increment');

		database::query("start transaction;");

		########################################################################
		## Create a customer carrying an active reset token
		########################################################################

		$customer = new ent_customer();
		$customer->data = f::array_update($customer->data, [
			'status' => 1,
			'firstname' => 'Reset',
			'lastname' => 'Target',
			'email' => 'reset.target@example.com',
			'phone' => '555-0101',
			'password' => 'InitialPass!1',
		]);
		$customer->save();

		$customer_id = $customer->data['id'];

		if (!$customer_id) {
			throw new Exception('Failed to create test customer');
		}

		$reset_token_payload = f::format_json([
			'token' => bin2hex(random_bytes(24)),
			'expires' => date('Y-m-d H:i:s', strtotime('+15 minutes')),
		], false);

		database::query(
			"update ". DB_TABLE_PREFIX ."customers
			set password_reset_token = '". database::input($reset_token_payload) ."'
			where id = ". (int)$customer_id ."
			limit 1;"
		);

		$before = database::query(
			"select password_hash, password_reset_token
			from ". DB_TABLE_PREFIX ."customers
			where id = ". (int)$customer_id ."
			limit 1;"
		)->fetch();

		if (empty($before['password_reset_token'])) {
			throw new Exception('Pre-condition failed: reset token was not persisted');
		}

		########################################################################
		## set_password() must invalidate the reset token in the same update
		########################################################################

		$customer = new ent_customer($customer_id);
		$customer->set_password('BrandNew!Pass42');

		$after = database::query(
			"select password_hash, password_reset_token
			from ". DB_TABLE_PREFIX ."customers
			where id = ". (int)$customer_id ."
			limit 1;"
		)->fetch();

		if ($after['password_hash'] === $before['password_hash']) {
			throw new Exception('set_password() did not update password_hash');
		}

		if (!empty($after['password_reset_token'])) {
			throw new Exception('set_password() did not clear password_reset_token (got: '. var_export($after['password_reset_token'], true) .')');
		}

		if (!password_verify('BrandNew!Pass42', $after['password_hash'])) {
			throw new Exception('set_password() stored an unverifiable hash');
		}

		########################################################################
		## Reset token must not be reusable after password change
		########################################################################

		$stored = json_decode($reset_token_payload, true);

		$row = database::query(
			"select id from ". DB_TABLE_PREFIX ."customers
			where id = ". (int)$customer_id ."
			and password_reset_token = '". database::input($reset_token_payload) ."'
			limit 1;"
		)->fetch();

		if ($row) {
			throw new Exception('The old reset token is still resolvable after password change');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		database::query('rollback;');

		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."customers AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
