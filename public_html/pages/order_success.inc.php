<?php
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  document::$snippets['title'][] = language::translate('title_order_success', 'Order Success');

  breadcrumbs::add(language::translate('title_checkout', 'Checkout'), document::ilink('checkout'));
  breadcrumbs::add(language::translate('title_order_success', 'Order Success'));

  $order = &session::$data['order'];

  if (empty($order->data['id'])) die('Error: Missing session order object');

  $payment = new mod_payment();

  $order_module = new mod_order();

  $_page = new view();

  $_page->snippets = array(
    'order' => $order->data,
    'printable_link' => document::ilink('printable_order_copy', array('order_id' => $order->data['id'], 'checksum' => functions::general_order_public_checksum($order->data['id']), 'media' => 'print')),
    'payment_receipt' => $payment->receipt($order),
    'order_success_modules_output' => $order_module->success($order),
  );

  echo $_page->stitch('pages/order_success');
