<?php
  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-type: text/html; charset='. language::$selected['charset']);
    document::$layout = 'ajax';
    header('X-Robots-Tag: noindex');
  }

  if (empty(cart::$items)) return;

  if (!isset($shipping)) $shipping = new mod_shipping();

  if (!isset($payment)) $payment = new mod_payment();

  session::$data['order'] = new ctrl_order();
  $order = &session::$data['order'];

// Resume incomplete order in session
  if (!empty($order->data['id']) && empty($order->data['order_status_id']) && strtotime($order->data['date_created']) > strtotime('-15 minutes')) {
    $resume_id = $order->data['id'];
  }

  $order->reset();

  if (!empty($resume_id)) {
    $order->data['id'] = $resume_id;
  }

// Build Order
  $order->data['weight_class'] = settings::get('store_weight_class');
  $order->data['currency_code'] = currency::$selected['code'];
  $order->data['currency_value'] = currency::$currencies[currency::$selected['code']]['value'];
  $order->data['language_code'] = language::$selected['code'];
  $order->data['customer'] = customer::$data;

  foreach (cart::$items as $item) {
    $order->add_item($item);
  }

  if (!empty($shipping->data['selected'])) {
    $order->data['shipping_option'] = array(
      'id' => $shipping->data['selected']['id'],
      'name' => $shipping->data['selected']['title'] .' ('. $shipping->data['selected']['name'] .')',
    );
  }

  if (!empty($payment->data['selected'])) {
    $order->data['payment_option'] = array(
      'id' => $payment->data['selected']['id'],
      'name' => $payment->data['selected']['title'] .' ('. $payment->data['selected']['name'] .')',
    );
  }

  $order_total = new mod_order_total();
  $rows = $order_total->process($order);
  foreach ($rows as $row) {
    $order->add_ot_row($row);
  }

// Output
  $box_checkout_summary = new view();

  $box_checkout_summary->snippets = array(
    'items' => array(),
    'order_total' => array(),
    'tax_total' => !empty($order->data['tax_total']) ? currency::format($order->data['tax_total'], false) : null,
    'incl_excl_tax' => !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'),
    'payment_due' => $order->data['payment_due'],
    'error' => $order->validate(),
    'selected_shipping' => null,
    'selected_payment' => null,
    'confirm' => !empty($payment->data['selected']['confirm']) ? $payment->data['selected']['confirm'] : language::translate('title_confirm_order', 'Confirm Order'),
  );

  foreach ($order->data['items'] as $item) {
    $box_checkout_summary->snippets['items'][] = array(
      'link' => document::ilink('product', array('product_id' => $item['product_id'])),
      'name' => $item['name'],
      'sku' => $item['sku'],
      'options' => $item['options'],
      'price' => $item['price'],
      'tax' => $item['tax'],
      'sum' => !empty(customer::$data['display_prices_including_tax']) ? currency::format(($item['price'] + $item['tax']) * $item['quantity'], false) : currency::format($item['price'] * $item['quantity'], false),
      'quantity' => (float)$item['quantity'],
    );
  }

  if (!empty($shipping->data['selected'])) {
    $box_checkout_summary->snippets['selected_shipping'] = array(
      'icon' => is_file(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $shipping->data['selected']['icon']) ? functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $shipping->data['selected']['icon'], 160, 60, 'FIT_USE_WHITESPACING') : '',
      'title' => $shipping->data['selected']['title'],
    );
  }

  if (!empty($payment->data['selected'])) {
    $box_checkout_summary->snippets['selected_payment'] = array(
      'icon' => is_file(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $payment->data['selected']['icon']) ? functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $payment->data['selected']['icon'], 160, 60, 'FIT_USE_WHITESPACING') : '',
      'title' => $payment->data['selected']['title'],
    );
  }

  foreach ($order->data['order_total'] as $row) {
    $box_checkout_summary->snippets['order_total'][] = array(
      'title' => $row['title'],
      'value' => $row['value'],
      'tax' => $row['tax'],
    );
  }

  echo $box_checkout_summary->stitch('views/box_checkout_summary');
