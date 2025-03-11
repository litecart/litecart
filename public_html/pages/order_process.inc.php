<?php

  header('X-Robots-Tag: noindex');
  document::$layout = 'checkout';

  if (settings::get('catalog_only_mode')) return;

  try {

    $shipping = new mod_shipping();
    $payment = new mod_payment();

    if (empty(session::$data['order'])) {
      throw new Exception('Missing order object');
    }

    $order = &session::$data['order'];

    if ($error_message = $order->validate($shipping, $payment)) {
      throw new Exception($error_message);
    }

  // If Confirm Order button was pressed
    if (isset($_POST['confirm_order'])) {

      ob_start();
      include_once vmod::check(FS_DIR_APP . 'pages/ajax/checkout_customer.inc.php');
      include_once vmod::check(FS_DIR_APP . 'pages/ajax/checkout_shipping.inc.php');
      include_once vmod::check(FS_DIR_APP . 'pages/ajax/checkout_payment.inc.php');
      include_once vmod::check(FS_DIR_APP . 'pages/ajax/checkout_summary.inc.php');
      ob_end_clean();

      if (!empty($_POST['comments'])) {
        $order->data['comments']['session'] = [
          'author' => 'customer',
          'text' => $_POST['comments'],
        ];
      }

      if ($payment->options($order->data['items'], $order->data['currency_code'], $order->data['customer'])) {
        if (empty($payment->data['selected'])) {
          throw new Exception(language::translate('error_no_payment_method_selected', 'No payment method selected'));
        }

        if ($payment_error = $payment->pre_check($order)) {
          $order->data['comments'][] = [
            'author' => 'system',
            'text' => 'Payment Precheck Error: '. $payment_error,
            'hidden' => true,
          ];
          throw new Exception($payment_error);
        }

      // Update the order draft if it's already saved to database
        if (!empty($order->data['id'])) {
          $order->save();
        }

        if ($gateway = $payment->transfer($order)) {

          if (!empty($gateway['error'])) {
            $order->data['comments'][] = [
              'author' => 'system',
              'text' => 'Payment Transfer Error: '. $gateway['error'],
              'hidden' => true,
            ];

            throw new Exception($gateway['error']);
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
                if (!empty($gateway['action'])) {
                  $redirect_url = document::link($gateway['action'], !empty($gateway['fields']) ? $gateway['fields'] : []);
                } else {
                  $redirect_url = document::ilink('order_process', !empty($gateway['fields']) ? $gateway['fields'] : []);
                }

                header('Location: '. $redirect_url);
                exit;

              default:
                throw new Exception('Undefined method ('. $gateway['method'] .')');
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
    if ($payment->options($order->data['items'], $order->data['currency_code'], $order->data['customer'])) {
      $result = $payment->verify($order);

    // If payment error
      if (!empty($result['error'])) {
        $order->data['comments'][] = [
          'author' => 'system',
          'text' => 'Payment Validation Error: '. $result['error'],
          'hidden' => true,
        ];

        throw new Exception($result['error']);
      }

    // Mark as paid
      if (!empty($result['is_paid'])) {
        $order->data['date_paid'] = date('Y-m-d H:i:s');
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
        foreach (preg_split('#[\s;,]+#', settings::get('email_order_copy'), -1, PREG_SPLIT_NO_EMPTY) as $email) {
          $bccs[] = $email;
        }
      }

      $order->send_order_copy($order->data['customer']['email'], [], $bccs, $order->data['language_code']);
    }

  // Run after process operations
    $shipping->after_process($order);

    $payment->after_process($order);

    $order_process = new mod_order();
    $order_process->after_process($order);

    header('Location: '. document::ilink('order_success', ['order_id' => $order->data['id'], 'public_key' => $order->data['public_key']]));
    unset(session::$data['order']);
    exit;

  } catch (Exception $e) {

  // Update the order draft if it's already saved to database
    if (!empty($order->data['id'])) {
      $order->save();
    }

    notices::add('errors', $e->getMessage());
    header('Location: '. document::ilink('checkout'));
    exit;
  }
