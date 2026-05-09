<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/are_you_human.inc.php
	 */

	header('X-Robots-Tag: noindex');

	document::$layout = 'blank';
	document::$title[] = t('title_are_you_human', 'Are You Human?');
	document::$description = t('are_you_human:meta_description', 'Verify that you are human.');
	document::$canonical = document::ilink('are_you_human');

	breadcrumbs::add(t('title_are_you_human', 'Are You Human?'));

	if (!$_POST) {
		$_POST['is_human'] = '0';
	}

	if (!empty($_POST['confirm'])) {

		try {

			if (!empty($_POST['email'])) {
				session::$data['security']['stuck_in_honeypot'] = true;
				throw new Exception(t('error_stuck_in_honeypot', 'Stuck in the honeypot!'));
			}

			if (empty($_POST['is_human'])) {
				throw new Exception(t('error_must_confirm_human', 'You must confirm that you are a human'));
			}

			if (!f::captcha_validate('are_you_human')) {
				throw new Exception(t('error_captcha_failed', 'CAPTCHA failed verifying a human, try again'));
			}

			session::$data['security']['is_human'] = true;

			if (!empty($_GET['redirect_url'])) {
				$redirect_url = new ent_link($_GET['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink('f:');
			}

			redirect($redirect_url);
			exit;

		} catch(Exception $e) {

			if (!empty(customer::$data['id'])) {
				database::query(
					"update ". DB_TABLE_PREFIX ."customers
					set num_human_fails = num_human_fails + 1
					where id =". (int)customer::$data['id'] ."
					limit 1;"
				);
			}

			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/are_you_human.inc.php');
	echo $_page->render();
