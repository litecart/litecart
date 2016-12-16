<?php
  header('X-Robots-Tag: noindex');
  document::$layout = 'checkout';

  if (settings::get('catalog_only_mode')) return;

// If Confirm Order button was pressed
  if (isset($_POST['confirm_order'])) {

    ob_start();
    include_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_PAGES . 'ajax/checkout_customer.html.inc.php');
    include_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_PAGES . 'ajax/checkout_shipping.html.inc.php');
    include_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_PAGES . 'ajax/checkout_payment.html.inc.php');
    include_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_PAGES . 'ajax/checkout_summary.html.inc.php');
    ob_end_clean();

    if ($error_message = $order->validate()) {
      notices::add('errors', $error_message);
      header('Location: '. document::ilink('checkout'));
      exit;
    }

    if (!empty(notices::$data['errors'])) {
      header('Location: '. document::ilink('checkout'));
      exit;
    }

    if (!empty($payment->modules) && count($payment->options()) > 0) {
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

        switch (@strtoupper($gateway['method'])) {

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
            require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
            exit;

          case 'GET':
          default:
            header('Location: '. (!empty($gateway['action']) ? $gateway['action'] : document::ilink('order_process')));
            exit;
        }
      }
    }
  }

  document::$snippets['title'][] = language::translate('checkout:head_title', 'Checkout');

  breadcrumbs::add(language::translate('title_checkout', 'Checkout'));

  $_page = new view();
  echo $_page->stitch('pages/checkout');
?>