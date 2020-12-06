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

  if ($error_message = $order->validate($shipping, $payment)) {
    notices::add('errors', $error_message);
    header('Location: '. document::ilink('checkout'));
    exit;
  }

// If Confirm Order button was pressed
  if (isset($_POST['confirm_order'])) {

    ob_start();
    include_once vmod::check(FS_DIR_APP . 'pages/ajax/checkout_customer.inc.php');
    include_once vmod::check(FS_DIR_APP . 'pages/ajax/checkout_shipping.inc.php');
    include_once vmod::check(FS_DIR_APP . 'pages/ajax/checkout_payment.inc.php');
    include_once vmod::check(FS_DIR_APP . 'pages/ajax/checkout_summary.inc.php');
    ob_end_clean();

    if (!empty(notices::$data['errors'])) {
      header('Location: '. document::ilink('checkout'));
      exit;
    }

    if (!empty($payment->modules) && count($payment->options($order->data['items'], $order->data['currency_code'], $order->data['customer'])) > 0) {
      if (empty($payment->data['selected'])) {
        notices::add('errors', language::translate('error_no_payment_method_selected', 'No payment method selected'));
        header('Location: '. document::ilink('checkout'));
        exit;
      }

      if ($payment_error = $payment->pre_check($order)) {
        notices::add('errors', $payment_error);
        header('Location: '. document::ilink('checkout'));
        exit;
      }

      if (!empty($_POST['comments'])) {
        $order->data['comments']['session'] = array(
          'author' => 'customer',
          'text' => $_POST['comments'],
        );
      }

      if ($gateway = $payment->transfer($order)) {

        if (!empty($gateway['error'])) {
          notices::add('errors', $gateway['error']);
          header('Location: '. document::ilink('checkout'));
          exit;
        }

        if (!empty($gateway['method'])) {
          switch (strtoupper($gateway['method'])) {

            case 'POST':

              echo '<p>'. language::translate('title_redirecting', 'Redirecting') .'...</p>' . PHP_EOL
                 . '<form name="gateway_form" method="post" action="'. (!empty($gateway['action']) ? $gateway['action'] : document::ilink('order_process')) .'">' . PHP_EOL;

              if (is_array($gateway['fields'])) {
                foreach ($gateway['fields'] as $key => $value) echo '  ' . functions::form_draw_hidden_field($key, $value) . PHP_EOL;
              } else {
                echo $gateway['fields'];
              }

              echo '</form>' . PHP_EOL
                 . '<script>' . PHP_EOL;

              if (!empty($gateway['delay'])) {
                echo '  var t=setTimeout(function(){' . PHP_EOL
                   . '    document.forms["gateway_form"].submit();' . PHP_EOL
                   . '  }, '. ($gateway['delay']*1000) .');' . PHP_EOL;
              } else {
                echo '  document.forms["gateway_form"].submit();' . PHP_EOL;
              }

              echo '</script>';
              exit;

            case 'HTML':
              echo $gateway['content'];
              require_once vmod::check(FS_DIR_APP . 'includes/app_footer.inc.php');
              exit;

            case 'GET':
            default:
              header('Location: '. (!empty($gateway['action']) ? $gateway['action'] : document::ilink('order_process')));
              exit;
          }
        }
      }
    }
  }

// Refresh the order if it's in the database in case a callback might have tampered with it
  if (!empty($order->data['id'])) {
    $order->load($order->data['id']);
  }

// Verify transaction
  if (!empty($payment->modules) && count($payment->options($order->data['items'], $order->data['currency_code'], $order->data['customer'])) > 0) {
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
  $order->data['unread'] = true;
  $order->save();

// Clean up cart
  cart::clear();

// Send order confirmation email
  if (settings::get('send_order_confirmation')) {
    $bccs = array();

    if (settings::get('email_order_copy')) {
      foreach (preg_split('#[\s;,]+#', settings::get('email_order_copy'), -1, PREG_SPLIT_NO_EMPTY) as $email) {
        $bccs[] = $email;
      }
    }

    $order->email_order_copy($order->data['customer']['email'], $bccs, $order->data['language_code']);
  }

// Run after process operations
  $shipping->after_process($order);

  $payment->after_process($order);

  $order_process = new mod_order();
  $order_process->after_process($order);

  header('Location: '. document::ilink('order_success'));
  exit;
