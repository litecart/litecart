<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		// Prepare some example data
		$link_data = '//example.com/path/to/resource';

		// Create a new entity
		$link = new ent_link($link_data);

		// Check if the entity was created
		if ((string)$link != 'http://example.com/path/to/resource') {
			throw new Exception('Failed to create link');
		}

		########################################################################
		## Load and check the link
		########################################################################

		// Update some data
		$link->host = 'newdomain.com';
		$link->path = '/new/path';

		// Check if data was set correctly
		if ((string)$link != 'http://newdomain.com/new/path') {
			throw new Exception('The link data was not updated correctly');
		}

		echo '  Test passed successfully!' . PHP_EOL;
		return true;

	} catch (Exception $e) {
		echo 'Test failed: '. $e->getMessage();
		return false;

	} finally {
		// Rollback changes to the database
		database::query("rollback;");
	}
