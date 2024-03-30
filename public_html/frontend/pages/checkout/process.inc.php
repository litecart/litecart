<?php

  header('X-Robots-Tag: noindex');

  document::$layout = 'checkout';

  if (settings::get('catalog_only_mode')) return;

  if (empty(session::$data['checkout']['shopping_cart'])) {
    notices::add('errors', 'Missing shopping cart object');
    header('Location: '. document::ilink('checkout/index'));
    exit;
  }

  $shopping_cart = &session::$data['checkout']['shopping_cart'];

  if (empty($shopping_cart->data['processable'])) {
    notices::add('errors', 'The shopping cart is not yet processable for creating an order');
    header('Location: '. document::ilink('checkout/index'));
    exit;
  }

  if ($error_message = $shopping_cart->validate()) {
    notices::add('errors', $error_message);
    header('Location: '. document::ilink('checkout/index'));
    exit;
  }

// If there is an amount to pay
  if (currency::format_raw($shopping_cart->data['total'], $shopping_cart->data['currency_code'], $shopping_cart->data['currency_value']) > 0) {

  // Refresh the shopping cart if it's in the database in case a callback have tampered with it
    if (!empty($shopping_cart->data['id'])) {
      $shopping_cart->load($shopping_cart->data['id']);
    }

  // Verify transaction
    if ($payment->modules && count($payment->options($shopping_cart)) > 0) {
      $result = $shopping_cart->payment->verify($shopping_cart);

    // If payment error
      if (!empty($result['error'])) {
        notices::add('errors', $result['error']);
        header('Location: '. document::ilink('checkout/index'));
        exit;
      }
    }
  }

// Save order
  $order = new ent_order();

  $fields = [
    'customer',
    'currency_code',
    'language_code',
    'shipping_option',
    'payment_option',
  ];

  $order->data = array_replace($shopping_cart->data, array_intersect_key($shopping_cart->data, array_flip($fields)));
  $order->data['currency_value'] = currency::$currencies[$order->data['currency_code']]['value'];
  $order->data['unread'] = true;

// Set items
  foreach ($order->data['items'] as $item) {
    $order->add_item($item);
  }

// Set order status id
  if (isset($result['order_status_id'])) {
    $order->data['order_status_id'] = $result['order_status_id'];
  }

// Set transaction id
  if (isset($result['transaction_id'])) {
    $order->data['payment_transaction_id'] = $result['transaction_id'];
  }

// Set transaction date
  if (isset($result['receipt_url'])) {
    $order->data['payment_receipt_url'] = $result['receipt_url'];
  }

// Set payment terms
  if (isset($result['payment_terms'])) {
    $order->data['payment_terms'] = $result['payment_terms'];
  }

// Set transaction date
  if (isset($result['date_paid'])) {
    $order->data['date_paid'] = $result['date_paid'];
  }

  $order->save();

// Clean up cart
  $shopping_cart->delete();
  session::$data['checkout']['shopping_cart'] = null;

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

  $order_modules->after_process($order);

  header('Location: '. document::ilink('checkout/success', ['order_id' => $order->data['id'], 'public_key' => $order->data['public_key']]));
  exit;
