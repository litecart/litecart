<?php
  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    header('Content-type: text/html; charset='. language::$selected['charset']);
    document::$layout = 'ajax';
    header('X-Robots-Tag: noindex');
  }

  if (empty(cart::$items)) return;

  if (!isset($shipping)) $shipping = new mod_shipping();

  if (!isset($payment)) $payment = new mod_payment();

  $order_total = new mod_order_total();

  $order = new ctrl_order('resume');

// Overwrite incompleted order in session
  if (!empty($order->data) && $order->data['customer']['id'] == customer::$data['id'] && empty($order->data['order_status_id'])) {
    $resume_id = $order->data['id'];
    $order = new ctrl_order('import_session');
    $order->data['id'] = $resume_id;
// New order based on session
  } else {
    $order = new ctrl_order('import_session');
  }

  $order->data['order_total'] = array();
  $order_total->process($order);

  $box_checkout_summary = new view();

  $box_checkout_summary->snippets = array(
    'items' => array(),
    'order_total' => array(),
    'tax_total' => !empty($order->data['tax_total']) ? currency::format($order->data['tax_total'], false) : '',
    'incl_excl_tax' => !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'),
    'payment_due' => currency::format($order->data['payment_due'], false),
    'error' => $order->checkout_forbidden(),
    'selected_payment' => null,
    'confirm' => !empty($payment->data['selected']['confirm']) ? $payment->data['selected']['confirm'] : language::translate('title_confirm_order', 'Confirm Order'),
  );

  foreach ($order->data['items'] as $item) {
    $box_checkout_summary->snippets['items'][] = array(
      'link' => document::ilink('product', array('product_id' => $item['product_id'])),
      'name' => $item['name'],
      'sku' => $item['sku'],
      'options' => $item['options'],
      'price' => !empty(customer::$data['display_prices_including_tax']) ? currency::format($item['price'] + $item['tax'], false) : currency::format($item['price'], false),
      'tax' => currency::format($item['tax'], false),
      'sum' => !empty(customer::$data['display_prices_including_tax']) ? currency::format(($item['price'] + $item['tax']) * $item['quantity'], false) : currency::format($item['price'] * $item['quantity'], false),
      'quantity' => (float)$item['quantity'],
    );
  }

  foreach ($order->data['order_total'] as $row) {
    $box_checkout_summary->snippets['order_total'][] = array(
      'title' => $row['title'],
      'value' => !empty(customer::$data['display_prices_including_tax']) ? currency::format($row['value'] + $row['tax'], false) : currency::format($row['value'], false),
      'tax' => currency::format($row['tax'], false)
    );
  }

  if (!empty($payment->data['selected'])) {
    $box_checkout_summary->snippets['selected_payment'] = array(
      'icon' => is_file(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $payment->data['selected']['icon']) ? functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $payment->data['selected']['icon'], 160, 60, 'FIT_USE_WHITESPACING') : '',
      'title' => $payment->data['selected']['title'],
    );
  }

  echo $box_checkout_summary->stitch('views/box_checkout_summary');

?>
