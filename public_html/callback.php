<?php
  
  /*
   * This script requires that the order has been made and is identified with the unique order id.
   */
  
  define('REQUIRE_POST_TOKEN', false);
  require_once('includes/app_header.inc.php');
  
  header('X-Robots-Tag: noindex');
  header('Content-type: text/plain; charset='. $system->language->selected['code']);
  
  $system->document->layout = 'default';
  $system->document->viewport = 'printable';
  
  if (empty($_GET['order_uid'])) {
    header('HTTP/1.0 400 Bad Request');
    echo 'Error: Bad Request';
    exit;
  }
  
  $orders_query = $system->database->query(
    "select id from ". DB_TABLE_ORDERS ."
    where uid = '". $system->database->input($_GET['order_uid']) ."'
    limit 1;"
  );
  $order = $system->database->fetch($orders_query);
  
  if (empty($order)) {
    header('HTTP/1.0 400 Bad Request');
    echo 'Error: Bad Request';
    exit;
  }
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'order.inc.php');
  $order = new ctrl_order('load', $order['id']);
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'payment.inc.php');
  $payment = new payment();
  list($payment_module_id, $payment_option_id) = explode(':', $order->data['payment_option']['id']);
  
  $result = $payment->run('callback', $payment_module_id);
  
  if (!empty($result['error'])) {
    echo $result['error'];
    exit;
  }
  
  if (!empty($result['order_status_id'])) $order->data['order_status_id'] = $result['order_status_id'];
  if (!empty($result['payment_transaction_id'])) $order->data['payment_transaction_id'] = $result['payment_transaction_id'];
  
  $order->data['comments'][] = array(
    'user' => 'system',
    'text' => 'Callback recceived from '. $_SERVER['REMOTE_ADDR'] .' ('. gethostbyaddr($_SERVER['REMOTE_ADDR']) .')'
            . (!empty($result['comments']) ? PHP_EOL . $result['comments'] : '')
            . (!empty($result['error']) ? PHP_EOL . 'Error: ' . $result['error'] : ''),
    'hidden' => true,
  );
  
  $order->save();
  
// If payment error
  if (!empty($result['error'])) {
    echo 'Error: '. $result['error'];
    exit;
  }
  
  echo 'OK';
?>