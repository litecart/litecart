<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Get the current auto increment ID - this will be used to revert the ID after the test
		$auto_increment_id = database::query(
			"SHOW TABLE STATUS LIKE '". DB_TABLE_PREFIX ."newsletter_recipients';"
		)->fetch('Auto_increment');

		// Start a MySQL transaction - so we can rollback the changes
		database::query("start transaction;");

		// Define some example data
		$data = [
			'subscribed' => 1,
			'email' => 'test-newsletter@example.com',
			'firstname' => 'Test',
			'lastname' => 'Subscriber',
			'language_code' => 'en',
			'country_code' => 'US',
			'ip_address' => '127.0.0.1',
			'hostname' => 'localhost',
			'user_agent' => 'LiteCart Test Runner',
		];

		########################################################################
		## Creating a new newsletter recipient
		########################################################################

		$recipient = new ent_newsletter_recipient();
		$recipient->data = f::array_update($recipient->data, $data);
		$recipient->save();

		if (!$recipient_id = $recipient->data['id']) {
			throw new Exception('Failed to create newsletter recipient');
		}

		########################################################################
		## Load and check the recipient
		########################################################################

		$recipient = new ent_newsletter_recipient($recipient_id);

		if ($recipient->data['id'] != $recipient_id) {
			throw new Exception('Failed to load newsletter recipient by ID');
		}

		if ($recipient->data['email'] != $data['email']) {
			throw new Exception('Recipient email was not stored correctly');
		}

		if ($recipient->data['firstname'] != $data['firstname']) {
			throw new Exception('Recipient firstname was not stored correctly');
		}

		########################################################################
		## Load by email address
		########################################################################

		$recipient_by_email = new ent_newsletter_recipient($data['email']);

		if ($recipient_by_email->data['id'] != $recipient_id) {
			throw new Exception('Failed to load newsletter recipient by email');
		}

		########################################################################
		## Update the recipient
		########################################################################

		$update_data = [
			'subscribed' => 0,
			'email' => 'updated-newsletter@example.com',
			'firstname' => 'Updated',
			'lastname' => 'User',
		];

		$recipient->data = f::array_update($recipient->data, $update_data);
		$recipient->save();

		$recipient = new ent_newsletter_recipient($recipient_id);

		if ($recipient->data['email'] != $update_data['email']) {
			throw new Exception('Recipient email was not updated correctly');
		}

		if ($recipient->data['subscribed'] != 0) {
			throw new Exception('Recipient subscribed status was not updated correctly');
		}

		########################################################################
		## Delete the recipient
		########################################################################

		$recipient->delete();

		if (database::query(
			"select * from ". DB_TABLE_PREFIX ."newsletter_recipients
			where id = ". (int)$recipient_id ."
			limit 1;"
		)->num_rows) {
			throw new Exception('Failed to delete newsletter recipient');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {

		// Rollback changes to the database
		database::query('rollback;');

		// Revert the auto increment ID
		database::query(
			"ALTER TABLE ". DB_TABLE_PREFIX ."newsletter_recipients AUTO_INCREMENT = ". (int)$auto_increment_id .";"
		);
	}
