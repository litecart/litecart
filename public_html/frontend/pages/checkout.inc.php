<?php
  header('X-Robots-Tag: noindex');
  document::$layout = 'checkout';

  if (settings::get('catalog_only_mode')) return;

  document::$snippets['title'][] = language::translate('checkout:head_title', 'Checkout');

  breadcrumbs::add(language::translate('title_checkout', 'Checkout'));

  if (!empty(session::$data['order']->data['id'])) {
    session::$data['order'] = $previous_order = new ent_order(session::$data['order']->data['id']);
    $resume_id = session::$data['order']->data['id'];
  }

  session::$data['order'] = new ent_order();

// Resume incomplete order in session
  if (!empty($resume_id)) {
    $order_query = database::query(
      "select * from ". DB_TABLE_PREFIX ."orders
      where id = ". (int)$resume_id ."
      and order_status_id = 0
      and date_created > '". date('Y-m-d H:i:s', strtotime('-15 minutes')) ."'
      limit 1;"
    );

    if (database::num_rows($order_query)) {
      session::$data['order'] = new ent_order($resume_id);
      session::$data['order']->reset();
      session::$data['order']->data['id'] = $resume_id;
    }
  }

  $order = &session::$data['order'];

// If Confirm Order button was pressed
  if (isset($_POST['confirm_order'])) {

    try {

      ob_start();
      include_once vmod::check(FS_DIR_APP . 'frontend/pages/checkout/customer.inc.php');
      include_once vmod::check(FS_DIR_APP . 'frontend/pages/checkout/shipping.inc.php');
      include_once vmod::check(FS_DIR_APP . 'frontend/pages/checkout/payment.inc.php');
      include_once vmod::check(FS_DIR_APP . 'frontend/pages/checkout/summary.inc.php');
      ob_end_clean();

      if (!empty(notices::$data['errors'])) {
        header('Location: '. document::ilink('checkout'));
        exit;
      }

      if ($order->payment->options($order->data['items'], $order->data['currency_code'], $order->data['customer'])) {

        if (empty($order->payment->selected)) {
          notices::add('errors', language::translate('error_no_payment_method_selected', 'No payment method selected'));
          header('Location: '. document::ilink('checkout'));
          exit;
        }

        if ($payment_error = $order->payment->pre_check($order)) {
          notices::add('errors', $payment_error);
          header('Location: '. document::ilink('checkout'));
          exit;
        }

        if (!empty($_POST['comments'])) {
          $order->data['comments']['session'] = [
            'author' => 'customer',
            'text' => $_POST['comments'],
          ];
        }

        if ($gateway = $order->payment->transfer($order)) {

          if (!empty($gateway['error'])) {
            notices::add('errors', $gateway['error']);
            header('Location: '. document::ilink('checkout'));
            exit;
          }

          if (!empty($gateway['method'])) {
            switch (strtoupper($gateway['method'])) {

              case 'POST':

                document::$template = 'blank';

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

                document::$template = 'blank';

                echo $gateway['content'];
                return;

              case 'GET':
              default:

                header('Location: '. (!empty($gateway['action']) ? $gateway['action'] : document::ilink('order_process')));
                exit;
            }
          }
        }
      }

      $order->data['processable'] = true;
      header('Location: '. document::ilink('order_process'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Build Order
  $order->data['processable'] = false; // Whether or not it is allowed to be processed in order_process
  $order->data['weight_unit'] = settings::get('site_weight_unit');
  $order->data['currency_code'] = currency::$selected['code'];
  $order->data['currency_value'] = currency::$currencies[currency::$selected['code']]['value'];
  $order->data['language_code'] = language::$selected['code'];
  $order->data['customer'] = customer::$data;
  $order->data['display_prices_including_tax'] = !empty(customer::$data['display_prices_including_tax']) ? true : false;

  if (!empty($previous_order)) {
    $order->data['customer'] = $previous_order->data['customer'];
    $order->shipping = $previous_order->shipping;
    $order->payment = $previous_order->payment;
  }

  foreach (cart::$items as $item) {
    $order->add_item($item);
  }

  $order->calculate_total();

  $_page = new ent_view();
  echo $_page->stitch(FS_DIR_TEMPLATE . 'pages/checkout.inc.php');

  functions::draw_lightbox();
