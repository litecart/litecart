<?php

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex">';

  document::$snippets['title'][] = language::translate('title_withdraw_from_purchase', 'Withdraw from Purchase');

  breadcrumbs::add(language::translate('title_withdraw_from_purchase', 'Withdraw from Purchase'));

  $withdrawal_window_days = settings::get('withdrawal_window_days') ? (int)settings::get('withdrawal_window_days') : 14;

  $signed_in = !empty(customer::$data['id']);

  $_page = new ent_view();
  $_page->snippets = [
    'order_groups' => [],
    'withdrawal_window_days' => $withdrawal_window_days,
    'signed_in' => $signed_in,
  ];

  if ($signed_in) {

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
  }

  // Identify the order to withdraw from
  if (!empty($_POST['identify'])) {

    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

    $generic_error_msg = language::translate('error_order_not_found', 'No order was found matching the details given');
    $too_many_attempts_msg = language::translate('error_too_many_attempts', 'Too many failed attempts. Please try again later.');
    $link_sent_msg = language::translate('success_withdrawal_link_sent', 'If the order exists, we have sent a link to the withdrawal request to the email address registered with it.');

    try {

    // A signed in customer picks one of their own orders and is sent straight to the withdrawal request
      if ($signed_in) {

        if (empty($order_id)) {
          throw new Exception($generic_error_msg);
        }

        $order = database::query(
          "select o.id from ". DB_TABLE_PREFIX ."orders o
          where o.id = ". (int)$order_id ."
          and o.customer_id = ". (int)customer::$data['id'] ."
          limit 1;"
        )->fetch();

        if (empty($order)) {
          throw new Exception($generic_error_msg);
        }

      // No public key is needed, the customer session authorizes the withdrawal request
        header('Location: '. document::ilink('withdrawal_request', ['order_id' => $order['id']]));
        exit;

      }

    // A guest is asked for the order number only, a direct link to the withdrawal request is emailed to the address registered with the order
      $rate_limit_scope = "ip_address = '". database::input($ip_address) ."'"
        . (!empty($order_id) ? " or (scope_type = 'order_id' and scope_key = '". database::input($order_id) ."')" : '');

      $record_attempt = function($action) use ($order_id, $ip_address) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."rate_limiting
          (action, scope_type, scope_key, ip_address, hostname, user_agent, date_created)
          values ('". database::input($action) ."', 'order_id', '". database::input($order_id) ."', '". database::input($ip_address) ."', '". database::input($ip_address !== '' ? gethostbyaddr($ip_address) : 'unknown') ."', '". database::input(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') ."', '". date('Y-m-d H:i:s') ."')"
        );
      };

    // Rate limit checking (defence in depth on top of the session-based counter further down)
      if (database::query(
        "select count(*) as num_attempts from ". DB_TABLE_PREFIX ."rate_limiting
        where action = 'withdrawal_lookup_attempt'
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

      if (empty($order_id)) {
        throw new Exception($generic_error_msg);
      }

      if (settings::get('captcha_enabled')) {
        $captcha = functions::captcha_get('withdrawal_lookup');
        $captcha_input = isset($_POST['captcha']) && is_string($_POST['captcha']) ? $_POST['captcha'] : '';
        if (empty($captcha) || !hash_equals((string)$captcha, $captcha_input)) {
          throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
        }
      }

    // Every attempt is counted, whether the order exists or not, so that the response never reveals whether an order id exists
      cache::set($rate_token, $attempts + 1);
      $record_attempt('withdrawal_lookup_attempt');

    // Look up the order by number only, the link is sent to the address registered with the order
      $order = database::query(
        "select o.id, o.public_key, o.customer_email, o.customer_firstname, o.language_code from ". DB_TABLE_PREFIX ."orders o
        where o.id = ". (int)$order_id ."
        limit 1;"
      )->fetch();

      if (empty($order) || empty($order['customer_email'])) {
        $record_attempt('withdrawal_lookup_failed');
      } else {

      // Clear the recorded failed attempts for this scope
        database::query(
          "delete from ". DB_TABLE_PREFIX ."rate_limiting
          where action = 'withdrawal_lookup_failed'
          and (". $rate_limit_scope .")"
        );

      // Limit the number of links emailed per order and per IP address to prevent mailbox flooding
        $links_sent = (int)database::query(
          "select count(*) as num_attempts from ". DB_TABLE_PREFIX ."rate_limiting
          where action = 'withdrawal_link_sent'
          and scope_type = 'order_id'
          and scope_key = '". database::input($order['id']) ."'
          and date_created >= '". date('Y-m-d H:i:s', strtotime('-1 hour')) ."'"
        )->fetch('num_attempts');

        $links_sent_per_ip = (int)database::query(
          "select count(*) as num_attempts from ". DB_TABLE_PREFIX ."rate_limiting
          where action = 'withdrawal_link_sent'
          and ip_address = '". database::input($ip_address) ."'
          and date_created >= '". date('Y-m-d H:i:s', strtotime('-1 hour')) ."'"
        )->fetch('num_attempts');

        if ($links_sent > 2 || $links_sent_per_ip > 10) {
          throw new Exception($too_many_attempts_msg);
        }

      // Fall back to the current language when the language of the order is not enabled on the shop
        $language_code = !empty($order['language_code']) && !empty(language::$languages[$order['language_code']])
          ? $order['language_code']
          : language::$selected['code'];

        $withdrawal_link = document::ilink('withdrawal_request', [
          'order_id' => $order['id'],
          'public_key' => $order['public_key'],
        ], false, [], $language_code);

        $subject = strtr(language::translate('email_withdrawal_link_subject', 'Your withdrawal request for order #%order_id', $language_code), [
          '%order_id' => $order['id'],
        ]);

        $message = strtr(language::translate('email_withdrawal_link', "Hello %firstname,\r\n\r\nUse the link below to submit your withdrawal request for order #%order_id:\r\n\r\n%withdrawal_link\r\n\r\nIf you did not place this order, you can safely ignore this message.\r\n\r\nRegards,\r\n%store_name\r\n%store_url", $language_code), [
          '%firstname' => !empty($order['customer_firstname']) ? $order['customer_firstname'] : language::translate('title_customer', 'Customer', $language_code),
          '%order_id' => $order['id'],
          '%withdrawal_link' => $withdrawal_link,
          '%store_name' => settings::get('store_name'),
          '%store_url' => document::ilink('', [], false, [], $language_code),
        ]);

        $email = new ent_email();
        $email->add_recipient($order['customer_email'], !empty($order['customer_firstname']) ? $order['customer_firstname'] : '')
          ->set_subject($subject)
          ->add_body($message);

      // A failed send is not reported to the guest, the notice has to be the same for every order id
        if ($email->send()) {
          $record_attempt('withdrawal_link_sent');
        }
      }

    // The same notice is shown whether the order exists or not
      notices::add('success', $link_sent_msg);

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  echo $_page->stitch('pages/withdrawal');
