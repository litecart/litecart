<?php
  header('X-Robots-Tag: noindex');
  document::$layout = 'checkout';

  if (settings::get('catalog_only_mode')) return;

  if (empty(session::$data['order'])) {
    notices::add('errors', 'Missing order object');
    header('Location: '. document::ilink('checkout'));
    exit;
  }

  $order = &session::$data['order'];

  if (empty($order->data['processable'])) {
    notices::add('errors', 'The order is not yet processable');
    header('Location: '. document::ilink('checkout'));
    exit;
  }

  if ($error_message = $order->validate()) {
    notices::add('errors', $error_message);
    header('Location: '. document::ilink('checkout'));
    exit;
  }

// If there is an amount to pay
  if (currency::format_raw($order->data['payment_due'], $order->data['currency_code'], $order->data['currency_value']) > 0) {

  // Refresh the order if it's in the database in case a callback might have tampered with it
    if (!empty($order->data['id'])) {
      $order->load($order->data['id']);
    }

  // Verify transaction
    if (!empty($order->payment->modules) && count($order->payment->options($order->data['items'], $order->data['currency_code'], $order->data['customer'])) > 0) {
      $result = $order->payment->verify($order);

    // If payment error
      if (!empty($result['error'])) {
        if (!empty($order->data['id'])) {
          $order->data['comments'][] = [
            'author' => 'system',
            'text' => 'Payment Error: '. $result['error'],
            'hidden' => true,
          ];
          $order->save();
        }
        notices::add('errors', $result['error']);
        header('Location: '. document::ilink('checkout'));
        exit;
      }

    // Set order status id
      if (isset($result['order_status_id'])) $order->data['order_status_id'] = $result['order_status_id'];

    // Set transaction id
      if (isset($result['transaction_id'])) $order->data['payment_transaction_id'] = $result['transaction_id'];
    }
  }

// Save order
  $order->data['unread'] = true;
  $order->save();

// Clean up cart
  cart::clear();

// Send order confirmation email
  if (settings::get('send_order_confirmation')) {
    $bccs = [];

    if (settings::get('email_order_copy')) {
      foreach (preg_split('#[\s;,]+#', settings::get('email_order_copy')) as $email) {
        if (empty($email)) continue;
        $bccs[] = $email;
      }
    }

    $order->email_order_copy($order->data['customer']['email'], $bccs, $order->data['language_code']);
  }

// Run after process operations
  $order->shipping->after_process($order);
  $order->payment->after_process($order);

  $order_process = new mod_order();
  $order_process->after_process($order);

  header('Location: '. document::ilink('order_success'));
  exit;
