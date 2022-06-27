<?php

  if (!empty($_POST['subscribe'])) {

    try {

      if (empty($_POST['email'])) throw new Exception(language::translate('error_missing_email', 'You must provide an email address'));

      $_POST['email'] = strtolower($_POST['email']);

    // Collect scraps
      if (empty(customer::$data['id'])) {
        customer::$data = array_replace(customer::$data, array_intersect_key(array_filter(array_diff_key($_POST, array_flip(['id']))), customer::$data));
      }

      database::query(
        "insert ignore into ". DB_TABLE_PREFIX ."newsletter_recipients
        (email, client_ip, date_created)
        values ('". database::input($_POST['email']) ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". date('c') ."');"
      );

      $aliases = [
        '%ip_address' => $_SERVER['REMOTE_ADDR'],
        '%hostname' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
        '%datetime' => language::strftime(language::$selected['format_datetime']),
      ];

      $message = strtr(
        language::translate('email_body:newsletter_subscription_confirmation', 'This is a confirmation that we have recieved your request to subscribe to our newsletter.') . "\r\n\r\n" .
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

      if (empty($_POST['email'])) throw new Exception(language::translate('error_missing_email', 'You must provide an email address'));

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

  $box_newsletter_subscribe = new ent_view();

  $box_newsletter_subscribe->snippets = [
    'privacy_policy_link' => null,
  ];

  if ($privacy_policy_id = settings::get('privacy_policy')) {
      $box_newsletter_subscribe->snippets['privacy_policy_link'] = document::href_ilink('information', ['page_id' => $privacy_policy_id]);
  }

  echo $box_newsletter_subscribe->stitch('views/box_newsletter_subscribe');
