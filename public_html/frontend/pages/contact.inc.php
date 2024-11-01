<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/contact.inc.php
	 */

	document::$title[] = language::translate('contact:head_title', 'Contact');
	document::$description = language::translate('contact:meta_description', '');

	breadcrumbs::add(language::translate('title_contact', 'Contact'), document::ilink('contact'));

	if (!$_POST) {
		$_POST = [
			'firstname' => customer::$data['firstname'],
			'lastname' => customer::$data['lastname'],
			'email' => customer::$data['email'],
		];
	}

	if (!empty($_POST['send'])) {

		try {

			if (empty($_POST['firstname'])) {
				throw new Exception(language::translate('error_missing_firstname', 'You must provide a firstname'));
			}

			if (empty($_POST['lastname'])) {
				throw new Exception(language::translate('error_missing_lastname', 'You must provide a lastname'));
			}

			if (empty($_POST['subject'])) {
				throw new Exception(language::translate('error_missing_subject', 'You must provide a subject'));
			}

			if (empty($_POST['email'])) {
				throw new Exception(language::translate('error_missing_email', 'You must provide a valid email address'));
			}

			if (empty($_POST['message'])) {
				throw new Exception(language::translate('error_missing_message', 'You must provide a message'));
			}

			if (settings::get('captcha_enabled') && !functions::captcha_validate('contact_us')) {
				throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
			}

			// Collect scraps
			if (empty(customer::$data['id'])) {
				customer::$data = array_replace(customer::$data, array_intersect_key(array_filter(array_diff_key($_POST, array_flip(['id']))), customer::$data));
			}

			$message = strtr(language::translate('email_customer_feedback', implode("\r\n", [
				'** This is an email message from %sender_name <%sender_email> **',
				'',
				'%message',
			]), [
				'%sender_name' => $_POST['firstname'] .' '. $_POST['lastname'],
				'%sender_email' => $_POST['email'],
				'%message' => $_POST['message'],
			]));

			$email = new ent_email();
			$email->set_sender($_POST['email'], $_POST['firstname'] .' '. $_POST['lastname'])
						->add_recipient(settings::get('store_email'), settings::get('store_name'))
						->set_subject($_POST['subject'])
						->add_body($message);

			$result = $email->send();

			if (!$result) {
				throw new Exception(language::translate('error_sending_email_for_unknown_reason', 'The email could not be sent for an unknown reason'));
			}

			notices::add('success', language::translate('success_your_email_was_sent', 'Your email has successfully been sent'));
			header('Location: '. document::link());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/contact.inc.php');

	echo $_page->render();
