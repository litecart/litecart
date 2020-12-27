<?php

	if (!empty($_POST['subscribe'])) {

		try {

			if (empty($_POST['email'])) throw new Exception(language::translate('error_missing_email', 'You must provide an email address'));

			$_POST['email'] = strtolower($_POST['email']);

			database::query(
				"insert ignore into ". DB_TABLE_PREFIX ."newsletter_recipients
				(email, date_created)
				values ('". database::input($_POST['email']) ."', '". date('c') ."');"
			);

			notices::add('success', language::translate('success_subscribed_to_newsletter', 'Thank you for subscribing to our newsletter'));
			header('Location: '. $_SERVER['REQUEST_URI']);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($_POST['unsubscribe'])) {

		try {

			if (empty($_POST['email'])) throw new Exception(language::translate('error_missing_email', 'You must provide an email address'));

			$_POST['email'] = strtolower($_POST['email']);

			database::query(
				"delete from ". DB_TABLE_PREFIX ."newsletter_recipients
				where email like '". database::input($_POST['email']) ."';"
			);

			notices::add('success', language::translate('success_unsubscribed_from_newsletter', 'You have been unsubscribed from the newsletter'));
			header('Location: '. $_SERVER['REQUEST_URI']);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$box_newsletter_subscribe = new ent_view();
	echo $box_newsletter_subscribe->stitch('views/box_newsletter_subscribe');
