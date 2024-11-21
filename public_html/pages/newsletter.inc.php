<?php

  if (!empty($_POST['subscribe'])) {

    try {

      if (empty($_POST['email'])) {
        throw new Exception(language::translate('error_missing_email', 'You must provide an email address'));
      }

      if (settings::get('captcha_enabled')) {
        $captcha = functions::captcha_get('newsletter_subscribe');
        if (!$captcha || empty($_POST['captcha']) || $captcha != $_POST['captcha']) {
          throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
        }
      }

      $_POST['email'] = strtolower($_POST['email']);

    // Collect scraps
      if (empty(customer::$data['id'])) {
        customer::$data = array_replace(customer::$data, array_intersect_key(array_filter(array_diff_key($_POST, array_flip(['id']))), customer::$data));
      }

      database::query(
        "insert ignore into ". DB_TABLE_PREFIX ."newsletter_recipients
        (email, firstname, lastname, client_ip, hostname, user_agent, date_created)
        values ('". database::input(mb_strtolower($_POST['email'])) ."', '". (!empty($_POST['firstname']) ? database::input($_POST['firstname']) : '') ."', '". (!empty($_POST['lastname']) ? database::input($_POST['lastname']) : '') ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."', '". database::input($_SERVER['HTTP_USER_AGENT']) ."', '". date('Y-m-d H:i:s') ."');"
      );

      $aliases = [
        '%ipaddress' => $_SERVER['REMOTE_ADDR'],
        '%hostname' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
        '%datetime' => language::strftime(language::$selected['format_datetime']),
        '%store_name' => settings::get('store_name'),
        '%store_link' => document::ilink(),
        '%unsubscribe_link' => document::ilink('newsletter', ['email' => $_POST['email']]),
      ];

      $message = strtr(
        language::translate('email_body:newsletter_subscription_confirmation', "This is a confirmation that we have recieved your request to subscribe to our newsletter.\r\n\r\nIf this was not you, click the link to unsubscribe: %unsubscribe_link") . "\r\n\r\n" .
        language::translate('title_ip_address', 'IP Address') . ': %ipaddress' . "\r\n" .
        language::translate('title_date', 'Date') . ': %datetime',
      $aliases);

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

      if (settings::get('captcha_enabled')) {
        $captcha = functions::captcha_get('newsletter_unsubscribe');
        if (!$captcha || empty($_POST['captcha']) || $captcha != $_POST['captcha']) {
          throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
        }
      }

      $_POST['email'] = strtolower($_POST['email']);

    // Collect scraps
      if (empty(customer::$data['id'])) {
        customer::$data = array_replace(customer::$data, array_intersect_key(array_filter(array_diff_key($_POST, array_flip(['id']))), customer::$data));
      }

      database::query(
        "delete from ". DB_TABLE_PREFIX ."newsletter_recipients
        where email like '". addcslashes(database::input($_POST['email']), '%_') ."';"
      );

      notices::add('success', language::translate('success_unsubscribed_from_newsletter', 'You have been unsubscribed from the newsletter'));
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $_page = new ent_view();

  $_page->snippets = [
    'consent' => null,
  ];

  if ($privacy_policy_id = settings::get('privacy_policy')) {
    $aliases = [
      '%privacy_policy_link' => document::href_ilink('information', ['page_id' => $privacy_policy_id]),
    ];
    $_page->snippets['consent'] = strtr(language::translate('consent:privacy_policy', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and I consent.'), $aliases);
  }

  echo $_page->stitch('pages/newsletter');
