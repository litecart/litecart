<?php
  header('X-Robots-Tag: noindex');
  document::$layout = 'checkout';

  if (settings::get('catalog_only_mode')) return;

  document::$snippets['title'][] = language::translate('checkout:head_title', 'Checkout');

  breadcrumbs::add(language::translate('title_checkout', 'Checkout'));

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

  $_page = new ent_view();
  echo $_page->stitch('pages/checkout.inc.php');

  functions::draw_lightbox();
