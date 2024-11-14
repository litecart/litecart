<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/newsletter.inc.php
	 */

  if (!empty($_POST['subscribe'])) {

    try {

      if (empty($_POST['email'])) {
				throw new Exception(language::translate('error_missing_email', 'You must provide an email address'));
			}

			if (settings::get('captcha_enabled') && !functions::captcha_validate('newsletter_subscribe')) {
				throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
			}

      $_POST['email'] = strtolower($_POST['email']);

 	  	// Collect scraps
      if (empty(customer::$data['id'])) {
        customer::$data = array_replace(customer::$data, array_intersect_key(array_filter(array_diff_key($_POST, array_flip(['id']))), customer::$data));
      }

      if (database::query(
        "select * from ". DB_TABLE_PREFIX ."newsletter_recipients
        where email = '". database::input($_POST['email']) ."'
        limit 1;"
      )->num_rows) {
        $newsletter_recipient = new ent_newsletter_recipient($_POST['email']);
      } else {
        $newsletter_recipient = new ent_newsletter_recipient();
      }

      foreach ([
        'email',
        'firstname',
        'lastname',
        'country_code',
        'language_code',
      ] as $field) {
        if (isset($_POST[$field])) {
          $newsletter_recipient->data[$field] = $_POST[$field];
        }
      }

      $newsletter_recipient->data['subscribe'] = 1;
      $newsletter_recipient->data['client_id'] = $_SERVER['REMOTE_ADDR'];
      $newsletter_recipient->data['hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
      $newsletter_recipient->data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

      $aliases = [
        '%ipaddress' => $_SERVER['REMOTE_ADDR'],
        '%hostname' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
        '%datetime' => language::strftime(language::$selected['format_datetime']),
        '%store_name' => settings::get('store_name'),
        '%store_link' => document::ilink(),
        '%unsubscribe_link' => document::ilink('newsletter', ['email' => $_POST['email']]),
      ];

      $message = strtr(language::translate('email_body:newsletter_subscription_confirmation', implode("\r\n", [
        'This is a confirmation that we have recieved your request to subscribe to our newsletter.',
        '',
        'If this was not you, click the link to unsubscribe: %unsubscribe_link',
        'IP address: %ipaddress',
        'Date: %datetime',
      ])), $aliases);

      $email = new ent_email();
      $email->add_recipient($_POST['email'])
          ->set_subject(language::translate('email_subject:newsletter_subscription_confirmation', 'Confirmation of newsletter subscription'))
          ->add_body($message)
          ->send();

      notices::add('success', language::translate('success_subscribed_to_newsletter', 'Thank you for subscribing to our newsletter'));
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (!empty($_POST['unsubscribe'])) {

    try {

      if (empty($_POST['email'])) {
        throw new Exception(language::translate('error_missing_email', 'You must provide an email address'));
      }

			if (settings::get('captcha_enabled') && !functions::captcha_validate('newsletter_unsubscribe')) {
				throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
			}

      $_POST['email'] = strtolower($_POST['email']);

      if (!database::query(
        "select id from ". DB_TABLE_PREFIX ."newsletter_recipients
        where email = '". database::input($_POST['email']) ."'
        and subscribed = 1
        limit 1;"
      )->num_rows) {
        throw new Exception(language::translate('error_given_email_not_subscribed_to_newsletter', 'The given email address is not subscribed to our newsletter'));
      }

    // Collect scraps
      if (empty(customer::$data['id'])) {
        customer::$data = array_replace(customer::$data, array_intersect_key(array_filter(array_diff_key($_POST, array_flip(['id']))), customer::$data));
      }

      $newsletter_recipient = new ent_newsletter_recipient($_POST['email']);

      $newsletter_recipient->data['subscribe'] = 0;
      $newsletter_recipient->data['client_id'] = $_SERVER['REMOTE_ADDR'];
      $newsletter_recipient->data['hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
      $newsletter_recipient->data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

      $newsletter_recipient->save();

      $aliases = [
        '%ipaddress' => $_SERVER['REMOTE_ADDR'],
        '%hostname' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
        '%datetime' => language::strftime(language::$selected['format_datetime']),
        '%store_name' => settings::get('store_name'),
        '%store_link' => document::ilink(),
        '%subscribe_link' => document::ilink('newsletter', ['email' => $_POST['email']]),
      ];

      $message = strtr(language::translate('email_body:newsletter_subscription_confirmation', implode("\r\n", [
        'This is a confirmation that we have recieved your request to unsubscribe to our newsletter.',
        '',
        'You can subscribe again at any time using the link:',
        '%subscribe_link',
        'IP-address: %ipaddress',
        'Date: %datetime',
      ])), $aliases);

      $email = new ent_email();
      $email->add_recipient($_POST['email'])
          ->set_subject(language::translate('email_subject:newsletter_subscription_confirmation', 'Confirmation of newsletter subscription'))
          ->add_body($message)
          ->send();

      notices::add('success', language::translate('success_unsubscribed_from_newsletter', 'You have been unsubscribed from the newsletter'));
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/newsletter.inc.php');

  $_page->snippets = [
    'consent' => null,
  ];

  if ($privacy_policy_id = settings::get('privacy_policy')) {
    $aliases = [
      '%privacy_policy_link' => document::href_ilink('information', ['page_id' => $privacy_policy_id]),
    ];
    $_page->snippets['consent'] = strtr(language::translate('consent:privacy_policy', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and I consent.'), $aliases);
  }

  echo $_page->stitch();
