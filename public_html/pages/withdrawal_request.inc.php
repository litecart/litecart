<?php

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex">';

  customer::require_login();

  document::$snippets['title'][] = language::translate('title_withdrawal_request', 'Withdrawal Request');

  breadcrumbs::add(language::translate('title_account', 'Account'), document::ilink('order_history'));
  breadcrumbs::add(language::translate('title_order_history', 'Order History'), document::ilink('order_history'));
  breadcrumbs::add(language::translate('title_withdrawal_request', 'Withdrawal Request'));

  try {

    if (empty($_GET['order_id']) || empty($_GET['public_key'])) {
      throw new Exception(language::translate('error_missing_order', 'Missing order'), 404);
    }

    $order = new ent_order($_GET['order_id']);

    if (empty($order->data['id']) || $_GET['public_key'] != $order->data['public_key']) {
      throw new Exception(language::translate('error_invalid_order', 'Invalid order'), 400);
    }

    if (empty($order->data['customer']['id']) || $order->data['customer']['id'] != customer::$data['id']) {
      throw new Exception(language::translate('error_not_authorized_to_view_order', 'You are not authorized to view this order'), 403);
    }

    if (empty($order->data['date_created']) || strtotime($order->data['date_created']) < strtotime('-14 days')) {
      //throw new Exception(language::translate('error_withdrawal_period_expired', 'The withdrawal period of 14 days has expired'), 410);
    }

  } catch (Exception $e) {
    http_response_code($e->getCode() ? $e->getCode() : 500);
    notices::add('errors', $e->getMessage());
    include vmod::check(FS_DIR_APP . 'pages/error_document.inc.php');
    return;
  }

  if (!$_POST) {
    $_POST = [
      'firstname' => $order->data['customer']['firstname'],
      'lastname' => $order->data['customer']['lastname'],
      'email' => $order->data['customer']['email'],
      'phone' => $order->data['customer']['phone'],
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

      if (empty($_POST['email'])) {
        throw new Exception(language::translate('error_missing_email', 'You must provide a valid email address'));
      }

      if (empty($_POST['reason'])) {
        throw new Exception(language::translate('error_missing_reason', 'You must provide a reason for the withdrawal'));
      }

      if (isset($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name']) && !empty($_FILES['image']['error'])) {
        throw new Exception(language::translate('error_uploaded_image_rejected', 'An uploaded image was rejected for unknown reason'));
      }

      if (settings::get('captcha_enabled')) {
        $captcha = functions::captcha_get('withdrawal_request');
        if (empty($captcha) || $captcha != $_POST['captcha']) throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
      }

      $items_summary = [];
      if (!empty($order->data['items'])) {
        foreach ($order->data['items'] as $item) {
          $items_summary[] = '- '. $item['sku'] .' | '. $item['name'] .' x '. (float)$item['quantity'];
        }
      }

      $message = strtr(language::translate('email_withdrawal_request', implode("\r\n", [
        '** Withdrawal Request **',
        '',
        'Order ID: %order_id',
        'Order Date: %order_date',
        'Customer: %customer_name <%customer_email>',
        'Phone: %customer_phone',
        '\r\nItems:',
        '%items',
        '',
        'Reason for withdrawal:',
        '%reason',
        '',
        'Additional message:',
        '%message',
      ])), [
        '%order_id' => $order->data['id'],
        '%order_date' => $order->data['date_created'],
        '%customer_name' => $_POST['firstname'] .' '. $_POST['lastname'],
        '%customer_email' => $_POST['email'],
        '%customer_phone' => !empty($_POST['phone']) ? $_POST['phone'] : '-',
        '%items' => !empty($items_summary) ? implode("\r\n", $items_summary) : '-',
        '%reason' => $_POST['reason'],
        '%message' => !empty($_POST['message']) ? $_POST['message'] : '-',
      ]);

      $subject = strtr(language::translate('email_withdrawal_request_subject', 'Withdrawal Request for Order #%order_id'), [
        '%order_id' => $order->data['id'],
      ]);

      $email = (new ent_email())
        ->set_sender($_POST['email'], $_POST['firstname'] .' '. $_POST['lastname'])
        ->add_recipient(settings::get('store_email'), settings::get('store_name'))
        ->add_cc(settings::get('store_email'), settings::get('store_name'))
        ->set_subject($subject)
        ->add_body($message);

      if (!empty($_FILES['attachment']['tmp_name'])) {
        $email->add_attachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
      }

      $result = $email->send();

      if (!$result) {
        throw new Exception(language::translate('error_sending_email_for_unknown_reason', 'The email could not be sent for an unknown reason'));
      }

      notices::add('success', language::translate('success_withdrawal_request_sent', 'Your withdrawal request has been sent successfully'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $_page = new ent_view();
  $_page->snippets = [
    'order' => $order->data,
  ];

  echo $_page->stitch('pages/withdrawal_request');
