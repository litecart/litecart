<?php

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex">';

  document::$snippets['title'][] = language::translate('title_withdraw_from_purchase', 'Withdraw from Purchase');

  breadcrumbs::add(language::translate('title_withdraw_from_purchase', 'Withdraw from Purchase'));

  $withdrawal_window_days = settings::get('withdrawal_window_days') ? (int)settings::get('withdrawal_window_days') : 14;

  $_page = new ent_view();
  $_page->snippets = [
    'order_groups' => [],
    'withdrawal_window_days' => $withdrawal_window_days,
    'signed_in' => !empty(customer::$data['id']),
  ];

  if (!empty(customer::$data['id'])) {

  // The customer's own orders, grouped by withdrawal period
    $orders_query = database::query(
      "select o.id, o.date_created, o.payment_due, o.currency_code, o.currency_value, osi.name as order_status_name from ". DB_TABLE_PREFIX ."orders o
      left join ". DB_TABLE_PREFIX ."order_statuses_info osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". database::input(language::$selected['code']) ."')
      where o.customer_id = ". (int)customer::$data['id'] ."
      order by o.date_created desc
      limit 100;"
    );

    $within_window = [];
    $outside_window = [];

    while ($order = database::fetch($orders_query)) {

      if (empty($order['date_created']) || strtotime($order['date_created']) === false) continue;

      $order_status = !empty($order['order_status_name']) ? functions::escape_html($order['order_status_name']) : '';

      $option = [
        language::translate('title_order', 'Order').' #'. $order['id']
          .(!empty($order_status) ? ' - '. $order_status : '')
          .' - '. language::strftime(language::$selected['format_datetime'], strtotime($order['date_created']))
          .' - '. currency::format($order['payment_due'], false, $order['currency_code'], $order['currency_value']),
        $order['id'],
      ];

      if (strtotime($order['date_created']) >= strtotime('-'. $withdrawal_window_days .' days')) {
        $within_window[] = $option;
      } else {
        $outside_window[] = $option;
      }
    }

    if (!empty($within_window)) {
      $_page->snippets['order_groups'][] = [
        'label' => language::translate('title_within_withdrawal_period', 'Within the withdrawal period'),
        'options' => $within_window,
      ];
    }

    if (!empty($outside_window)) {
      $_page->snippets['order_groups'][] = [
        'label' => language::translate('title_outside_withdrawal_period', 'Outside the withdrawal period'),
        'options' => $outside_window,
      ];
    }

    // A signed in customer gets the account email prefilled
    if (empty($_POST['email']) && !empty(customer::$data['email'])) {
      $_POST['email'] = customer::$data['email'];
    }
  }

  // Identify the order to withdraw from
  if (!empty($_POST['identify'])) {

    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

    $generic_error_msg = language::translate('error_order_not_found', 'No order was found matching the details given');
    $too_many_attempts_msg = language::translate('error_too_many_attempts', 'Too many failed attempts. Please try again later.');

    $rate_limit_scope = "ip_address = '". database::input($ip_address) ."'"
      . (!empty($order_id) ? " or (scope_type = 'order_id' and scope_key = '". database::input($order_id) ."')" : '');

    $record_failed_attempt = function() use ($order_id, $ip_address) {
      database::query(
        "insert into ". DB_TABLE_PREFIX ."rate_limiting
        (action, scope_type, scope_key, ip_address, hostname, user_agent, date_created)
        values ('withdrawal_lookup_failed', 'order_id', '". database::input($order_id) ."', '". database::input($ip_address) ."', '". database::input($ip_address !== '' ? gethostbyaddr($ip_address) : 'unknown') ."', '". database::input(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') ."', '". date('Y-m-d H:i:s') ."')"
      );
    };

    try {

    // Rate limit checking (defence in depth on top of the session-based counter further down)
      if (database::query(
        "select count(*) as num_attempts from ". DB_TABLE_PREFIX ."rate_limiting
        where action = 'withdrawal_lookup_failed'
        and (". $rate_limit_scope .")
        and date_created >= '". date('Y-m-d H:i:s', strtotime('-5 minutes')) ."'"
      )->fetch('num_attempts') > 5) {
        throw new Exception($too_many_attempts_msg);
      }

      $rate_token = cache::token('withdrawal_lookup_attempts', [$order_id, $ip_address], 'session', 900);
      $cached_attempts = cache::get($rate_token);
      $attempts = (int)(isset($cached_attempts) ? $cached_attempts : 0);
      if ($attempts >= 5) {
        throw new Exception($too_many_attempts_msg);
      }

      if (empty($order_id) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception($generic_error_msg);
      }

      if (settings::get('captcha_enabled')) {
        $captcha = functions::captcha_get('withdrawal_lookup');
        if (empty($captcha) || !hash_equals((string)$captcha, (string)(isset($_POST['captcha']) ? $_POST['captcha'] : ''))) {
          throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
        }
      }

    // Match the order number and the email address in a single query, so that an unknown order number and a mismatched email address are indistinguishable
      $order = database::query(
        "select o.id, o.public_key from ". DB_TABLE_PREFIX ."orders o
        where o.id = ". (int)$order_id ."
        and lower(o.customer_email) = lower('". database::input($email) ."')
        limit 1;"
      )->fetch();

      if (empty($order)) {
        cache::set($rate_token, $attempts + 1);
        $record_failed_attempt();
        throw new Exception($generic_error_msg);
      }

    // Grant access to the order for this session
      session::$data['withdrawal_access'][$order['id']] = $order['public_key'];

    // Clear the recorded attempts for this scope
      database::query(
        "delete from ". DB_TABLE_PREFIX ."rate_limiting
        where action = 'withdrawal_lookup_failed'
        and (". $rate_limit_scope .")"
      );

      header('Location: '. document::ilink('withdrawal_request', ['order_id' => $order['id']]));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  echo $_page->stitch('pages/withdrawal');
