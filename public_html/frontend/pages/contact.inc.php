<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/contact.inc.php
	 */

	document::$title[] = t('contact:head_title', 'Contact');
	document::$description = t('contact:meta_description', '');

	breadcrumbs::add(t('title_contact', 'Contact'), document::ilink('contact'));

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
				throw new Exception(t('error_must_provide_firstname', 'You must provide a firstname'));
			}

			if (empty($_POST['lastname'])) {
				throw new Exception(t('error_must_provide_lastname', 'You must provide a lastname'));
			}

			if (empty($_POST['subject'])) {
				throw new Exception(t('error_must_provide_subject', 'You must provide a subject'));
			}

			if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				throw new Exception(t('error_must_provide_email', 'You must provide a valid email address'));
			}

			if (empty($_POST['message'])) {
				throw new Exception(t('error_must_provide_message', 'You must provide a message'));
			}

			if (settings::get('captcha_enabled') && !functions::captcha_validate('contact_us')) {
				throw new Exception(t('error_invalid_captcha', 'Invalid CAPTCHA given'));
			}

			if (!empty($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
				foreach ($_FILES['attachments']['tmp_name'] as $i => $tmp_name) {
					$filename = $_FILES['attachments']['name'][$i];
					$filesize = $_FILES['attachments']['size'][$i];
					$filetype = pathinfo($filename, PATHINFO_EXTENSION);
					if (!is_uploaded_file($tmp_name)) {
						throw new Exception(t('error_invalid_attachment', 'Invalid attachment'));
					}

					$accepted_filetypes = [
						'jpg', 'png', 'gif', 'mp4', 'pdf', 'txt', 'doc', 'docx',  'xls', 'xlsx'
					];

					if (!in_array($filetype, $accepted_filetypes)) {
						throw new Exception(strtr(t('error_invalid_attachment_type', '{filename} is not of an accepted file type ({accepted_filetypes})'), [
							'{filename}' => $filename,
							'{accepted_filetypes}' => implode(', ', $accepted_filetypes)
						]));
					}

					if ($filesize > 4*1024*1024) { // 4MB limit
						throw new Exception(t('error_attachments_cannot_exceed_size', 'Attachments cannot exceed {size}', ['{size}' => '4 MB']));
					}
				}
			}

			$message = strtr(t('email_customer_feedback', implode("\r\n", [
				'** This is an email message from {sender_name} <{sender_email}> **',
				'',
				'{message}',
			])), [
				'{sender_name}' => $_POST['firstname'] .' '. $_POST['lastname'],
				'{sender_email}' => $_POST['email'],
				'{message}' => $_POST['message'],
			]);

			$email = (new ent_email())
				->set_sender($_POST['email'], $_POST['firstname'] .' '. $_POST['lastname'])
				->add_recipient(settings::get('store_email'), settings::get('store_name'))
				->set_subject($_POST['subject'])
				->add_body($message);

			if (!empty($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
				foreach ($_FILES['attachments']['tmp_name'] as $i => $tmp_name) {
					if (!is_uploaded_file($tmp_name)) continue;
					$filename = $_FILES['attachments']['name'][$i];
					$filetype = $_FILES['attachments']['type'][$i];
					$email->add_attachment($tmp_name, $filename);
				}
			}

			$result = $email->send();

			if (!$result) {
				throw new Exception(t('error_sending_email_for_unknown_reason', 'The email could not be sent for an unknown reason'));
			}

			notices::add('success', t('success_your_email_was_sent', 'Your email has successfully been sent'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/contact.inc.php');

	echo $_page->render();
