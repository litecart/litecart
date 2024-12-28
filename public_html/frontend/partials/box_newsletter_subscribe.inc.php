<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_newsletter_subscribe.inc.php
	 */

	if (customer::$data['email'] && database::query(
		"select id from ". DB_TABLE_PREFIX ."newsletter_recipients
		where lower(email) = '". database::input(strtolower(customer::$data['email'])) ."'
		limit 1;"
	)->num_rows) {
		return;
	}

	$box_newsletter_subscribe = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_newsletter_subscribe.inc.php');

	echo $box_newsletter_subscribe->render();
