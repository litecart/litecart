<?php

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex">';

  document::$snippets['title'][] = language::translate('title_withdrawal_request', 'Withdrawal Request');

  if (!empty(customer::$data['id'])) {
    breadcrumbs::add(language::translate('title_account', 'Account'), document::ilink('order_history'));
    breadcrumbs::add(language::translate('title_order_history', 'Order History'), document::ilink('order_history'));
  }
  breadcrumbs::add(language::translate('title_withdrawal_request', 'Withdrawal Request'));

  $withdrawal_window_days = settings::get('withdrawal_window_days', 14);

  $order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
  $public_key = isset($_GET['public_key']) ? (string)$_GET['public_key'] : '';

  try {

    if (empty($order_id)) {
      throw new Exception(language::translate('error_missing_order', 'Missing order'), 404);
    }

    try {
      $order = new ent_order($order_id);
    } catch (Exception $e) {
      throw new Exception(language::translate('error_invalid_order', 'Invalid order'), 404);
    }

    if (empty($order->data['id'])) {
      throw new Exception(language::translate('error_invalid_order', 'Invalid order'), 404);
    }

  // Authorize the request: order owner, valid access key from the order confirmation link, or order identified by number and email address
    $authorized = false;

    if (!empty($public_key) && $public_key === $order->data['public_key']) {
      $authorized = true;
    } elseif (!empty(customer::$data['id']) && !empty($order->data['customer']['id']) && customer::$data['id'] == $order->data['customer']['id']) {
      $authorized = true;
    }

    if (!$authorized) {
      throw new Exception(language::translate('error_not_authorized_to_view_order', 'You are not authorized to view this order'), 403);
    }

    if (empty($order->data['date_created']) || strtotime($order->data['date_created']) < strtotime('-'. $withdrawal_window_days .' days')) {
      notices::add('warnings', strtr(language::translate('warning_withdrawal_period_expired', 'The withdrawal period of :days days has expired for this order'), [
        ':days' => $withdrawal_window_days,
      ]));
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

    // Rate limit the number of withdrawal requests sent per IP address to prevent mailbox flooding
      $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

      if (database::query(
        "select count(*) as num_attempts from ". DB_TABLE_PREFIX ."rate_limiting
        where action = 'withdrawal_request_sent'
        and ip_address = '". database::input($ip_address) ."'
        and date_created >= '". date('Y-m-d H:i:s', strtotime('-5 minutes')) ."'"
      )->fetch('num_attempts') > 3) {
        throw new Exception(language::translate('error_too_many_attempts', 'Too many failed attempts. Please try again later.'));
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

    // Record the sent request for rate limiting purposes
      database::query(
        "insert into ". DB_TABLE_PREFIX ."rate_limiting
        (action, scope_type, scope_key, ip_address, hostname, user_agent, date_created)
        values ('withdrawal_request_sent', 'order_id', '". database::input($order->data['id']) ."', '". database::input($ip_address) ."', '". database::input($ip_address !== '' ? gethostbyaddr($ip_address) : 'unknown') ."', '". database::input(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') ."', '". date('Y-m-d H:i:s') ."')"
      );

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
