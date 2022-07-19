<?php

  if (!empty($_POST['subscribe'])) {

    try {

      if (empty($_POST['email'])) throw new Exception(language::translate('error_missing_email', 'You must provide an email address'));

      $_POST['email'] = strtolower($_POST['email']);

      database::query(
        "insert ignore into ". DB_TABLE_PREFIX ."newsletter_recipients
        (email, client_ip, date_created)
        values ('". database::input($_POST['email']) ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". date('c') ."');"
      );

      if (empty(customer::$data['email'])) {
        customer::$data['email'] = $_POST['email'];
      }

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
        where email like '". addcslashes(database::input($_POST['email']), '%_') ."';"
      );

      if (empty(customer::$data['email'])) {
        customer::$data['email'] = $_POST['email'];
      }

      notices::add('success', language::translate('success_unsubscribed_from_newsletter', 'You have been unsubscribed from the newsletter'));
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $box_newsletter_subscribe = new ent_view(FS_DIR_TEMPLATE . 'partials/box_newsletter_subscribe.inc.php');

  $box_newsletter_subscribe->snippets = [
    'privacy_policy_link' => null,
  ];

  if ($privacy_policy_id = settings::get('privacy_policy')) {
      $box_newsletter_subscribe->snippets['privacy_policy_link'] = document::href_ilink('information', ['page_id' => $privacy_policy_id]);
  }

  echo $box_newsletter_subscribe;
