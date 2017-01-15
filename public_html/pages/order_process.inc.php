<?php
  header('X-Robots-Tag: noindex');
  document::$layout = 'checkout';

  if (settings::get('catalog_only_mode')) return;

  $shipping = new mod_shipping();

  $payment = new mod_payment();

  if (empty(session::$data['order'])) {
    notices::add('errors', 'Missing order object');
    header('Location: '. document::ilink('checkout'));
    exit;
  }

  $order = &session::$data['order'];

  if ($error_message = $order->validate()) {
    notices::add('errors', $error_message);
    header('Location: '. document::ilink('checkout'));
    exit;
  }

// Verify transaction
  if (!empty($payment->modules) && count($payment->options()) > 0) {
    $result = $payment->verify($order);

  // If payment error
    if (!empty($result['error'])) {
      if (!empty($order->data['id'])) {
        $order->data['comments'][] = array(
          'author' => 'system',
          'text' => 'Payment Error: '. $result['error'],
          'hidden' => true,
        );
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

// Save order
  $order->save();

// Clean up cart
  cart::clear();

// Send e-mails
  $order->email_order_copy($order->data['customer']['email']);
  foreach (explode(';', settings::get('email_order_copy')) as $email) {
    $order->email_order_copy($email);
  }

// Run after process operations
  $shipping = new mod_shipping();
  $shipping->after_process($order);

  $payment = new mod_payment();
  $payment->after_process($order);

  $order_process = new mod_order();
  $order_process->after_process($order);

  header('Location: '. document::ilink('order_success'));
  exit;
?>