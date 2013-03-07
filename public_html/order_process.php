<?php
  define('REQUIRE_POST_TOKEN', false);
  define('SEO_REDIRECT', false);
  require_once('includes/app_header.inc.php');
  
  header('X-Robots-Tag: noindex');
  
  if (isset($_POST['confirm_order']) && $system->settings->get('checkout_captcha_enabled') == 'true') {
    $captcha = $system->functions->captcha_get('checkout');
    if (!isset($_POST['captcha']) || empty($captcha) || $captcha != $_POST['captcha']) {
      $system->notices->add('errors', $system->language->translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
      header('Location: '. $system->document->link(WS_DIR_HTTP_HOME . 'checkout.php'));
      exit;
    }
  }
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'shipping.inc.php');
  $shipping = new shipping();
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'payment.inc.php');
  $payment = new payment();
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'order.inc.php');
  $order = new ctrl_order('resume');
  
  if (empty($shipping->data['selected'])) die('No shipping selected');
  list($shipping_module_id, $shipping_option_id) = explode(':', $shipping->data['selected']['id']);
  
  if (empty($payment->data['selected'])) die('No payment selected');
  list($payment_module_id, $payment_option_id) = explode(':', $payment->data['selected']['id']);
  
  if ($payment_error = $payment->run('pre_check')) {
    $system->notices->add('errors', $payment_error);
    header('Location: '. $system->document->link(WS_DIR_HTTP_HOME . 'checkout.php'));
    exit;
  }
  
  if (!empty($_POST['comments'])) {
    $order->data['comments']['session']['text'] = $_POST['comments'];
  }
  
// Payment transaction
  if (isset($_POST['confirm_order'])) {
    
    if ($gateway = $payment->transfer()) {
    
      if (!empty($gateway['error'])) {
        $system->notices->add('errors', $gateway['error']);
        header('Location: '. $system->document->link(WS_DIR_HTTP_HOME . 'checkout.php'));
        exit;
      }
      
      if (strtolower($gateway['method']) == 'post') {
        echo '<p><img src="'. WS_DIR_IMAGES .'icons/16x16/loading.gif" width="16" height="16" /> '. $system->language->translate('title_redirecting', 'Redirecting') .'...</p>' . PHP_EOL
           . '<form name="gateway_form" method="post" action="'. $gateway['action'].'">' . PHP_EOL;
        if (is_array($gateway['fields'])) {
          foreach ($gateway['fields'] as $key => $value) echo '  ' . $system->functions->form_draw_hidden_field($key, $value) . PHP_EOL;
        } else {
          echo $gateway['fields'];
        }
        echo '</form>' . PHP_EOL
           . '<script language="javascript">' . PHP_EOL;
        if (!empty($gateway['delay'])) {
          echo '  var t=setTimeout(function(){' . PHP_EOL
             . '    document.forms["gateway_form"].submit();' . PHP_EOL
             . '  }, '. ($gateway['delay']*1000) .');' . PHP_EOL;
        } else {
          echo '  document.forms["gateway_form"].submit();' . PHP_EOL;
        }
        echo '</script>';
        exit;
        
      } else {
        header('Location: '. $gateway['action']);
        exit;
      }
    }
  }
  
// Verify transaction
  $result = $payment->run('verify');
  
// If payment error
  if (!empty($result['error'])) {
    $system->notices->add('errors', $result['error']);
    header('Location: '. $system->document->link(WS_DIR_HTTP_HOME . 'checkout.php'));
    exit;
  }
  
// Set transaction id
  if (isset($result['order_status_id'])) $order->data['order_status_id'] = $result['order_status_id'];
  
// Set order status id
  if (isset($result['payment_transaction_id'])) $order->data['payment_transaction_id'] = $result['payment_transaction_id'];
  
// Save order
  $order->save();
  
// Send e-mails
  $order->email_order_copy($order->data['customer']['email']);
  foreach (explode(';', $system->settings->get('email_order_copy')) as $email) {
    $order->email_order_copy($email);
  }
  
// Run after process operations
  $shipping->run('after_process');
  $payment->run('after_process');
  
  $system->cart->reset();
  
  header('Location: '. $system->document->link(WS_DIR_HTTP_HOME . 'order_success.php'));
  exit;
?>