<?php

  header('X-Robots-Tag: noindex');

  unset(notices::$data['notices']['maintenance_mode']);

  if (empty(cart::$items)) return;

  if (empty($shipping)) $shipping = new mod_shipping();
  if (empty($payment)) $payment = new mod_payment();

  if (!empty(session::$data['order']->data['id'])) {
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

// Build Order
  $order->data['weight_class'] = settings::get('store_weight_class');
  $order->data['currency_code'] = currency::$selected['code'];
  $order->data['currency_value'] = currency::$currencies[currency::$selected['code']]['value'];
  $order->data['language_code'] = language::$selected['code'];
  $order->data['customer'] = customer::$data;
  $order->data['display_prices_including_tax'] = !empty(customer::$data['display_prices_including_tax']) ? true : false;

  foreach (cart::$items as $item) {
    $order->add_item($item);
  }

  if (!empty($shipping->data['selected'])) {
    $order->data['shipping_option'] = $shipping->data['selected'];
  }

  if (!empty($payment->data['selected'])) {
    $order->data['payment_option'] = $payment->data['selected'];
  }

  $order_total = new mod_order_total();
  $rows = $order_total->process($order);
  foreach ($rows as $row) {
    $order->add_ot_row($row);
  }

// Output
  $box_checkout_summary = new ent_view();

  $box_checkout_summary->snippets = [
    'items' => [],
    'order_total' => [],
    'tax_total' => !empty($order->data['tax_total']) ? currency::format($order->data['tax_total'], false) : null,
    'incl_excl_tax' => !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'),
    'payment_due' => $order->data['payment_due'],
    'error' => $order->validate($shipping, $payment),
    'selected_shipping' => null,
    'selected_payment' => null,
    'consent' => null,
    'confirm' => !empty($payment->data['selected']['confirm']) ? $payment->data['selected']['confirm'] : language::translate('title_confirm_order', 'Confirm Order'),
  ];

  foreach ($order->data['items'] as $item) {
    $box_checkout_summary->snippets['items'][] = [
      'product_id' => $item['product_id'],
      'link' => document::ilink('product', ['product_id' => $item['product_id']]),
      'name' => $item['name'],
      'sku' => $item['sku'],
      'options' => $item['options'],
      'price' => $item['price'],
      'tax' => $item['tax'],
      'sum' => !empty(customer::$data['display_prices_including_tax']) ? currency::format(($item['price'] + $item['tax']) * $item['quantity'], false) : currency::format($item['price'] * $item['quantity'], false),
      'quantity' => (float)$item['quantity'],
    ];
  }

  if (!empty($shipping->data['selected'])) {
    $box_checkout_summary->snippets['selected_shipping'] = [
      'icon' => is_file(FS_DIR_APP . $shipping->data['selected']['icon']) ? functions::image_thumbnail(FS_DIR_APP . $shipping->data['selected']['icon'], 160, 60, 'FIT_USE_WHITESPACING') : '',
      'title' => $shipping->data['selected']['title'],
    ];
  }

  if (!empty($payment->data['selected'])) {
    $box_checkout_summary->snippets['selected_payment'] = [
      'icon' => is_file(FS_DIR_APP . $payment->data['selected']['icon']) ? functions::image_thumbnail(FS_DIR_APP . $payment->data['selected']['icon'], 160, 60, 'FIT_USE_WHITESPACING') : '',
      'title' => $payment->data['selected']['title'],
    ];
  }

  foreach ($order->data['order_total'] as $row) {
    $box_checkout_summary->snippets['order_total'][] = [
      'title' => $row['title'],
      'value' => $row['value'],
      'tax' => $row['tax'],
    ];
  }

  $privacy_policy_id = settings::get('privacy_policy');
  $terms_of_purchase_id = settings::get('terms_of_purchase');

  switch(true) {
    case ($terms_of_purchase_id && $privacy_policy_id):
      $box_checkout_summary->snippets['consent'] = language::translate('consent:privacy_policy_and_terms_of_purchase', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and <a href="%terms_of_purchase_link" target="_blank">Terms of Purchase</a> and I consent.');
      break;
    case ($privacy_policy_id):
      $box_checkout_summary->snippets['consent'] = language::translate('consent:privacy_policy', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and I consent.');
      break;
    case ($terms_of_purchase_id):
      $box_checkout_summary->snippets['consent'] = language::translate('consent:terms_of_purchase', 'I have read the <a href="%terms_of_purchase_link" target="_blank">Terms of Purchase</a> and I consent.');
      break;
  }

  if (!empty($box_checkout_summary->snippets['consent'])) {

    $aliases = [
      '%privacy_policy_link' => document::href_ilink('information', ['page_id' => $privacy_policy_id]),
      '%terms_of_purchase_link' => document::href_ilink('information', ['page_id' => $terms_of_purchase_id]),
    ];

    $box_checkout_summary->snippets['consent'] = strtr($box_checkout_summary->snippets['consent'], $aliases);
  }

  echo $box_checkout_summary->stitch('views/box_checkout_summary');
