<?php
  header('X-Robots-Tag: noindex');
  document::$snippets['title'][] = language::translate('title_order_success', 'Order Success');

  breadcrumbs::add(language::translate('title_checkout', 'Checkout'), document::ilink('checkout/index'));
  breadcrumbs::add(language::translate('title_order_success', 'Order Success'));

  try {

    if (empty($_GET['order_id']) || empty($_GET['public_key'])) {
      throw new Exception('Missing order_id or public_key');
    }

    $order = new ent_order($_GET['order_id']);

    if (empty($order->data['id']) || $_GET['public_key'] != $order->data['public_key']) {
      throw new Exception('Not found or invalid public_key');
    }

  } catch (Exception $e) {
    http_response_code(404);
    include vmod::check(FS_DIR_APP . 'frontend/pages/error_document.inc.php');
    return;
  }

  $payment = new mod_payment();
  $order_module = new mod_order();

  $_page = new ent_view(FS_DIR_TEMPLATE . 'pages/order_success.inc.php');
  $_page->snippets = [
    'order' => $order->data,
    'printable_link' => document::ilink('printable_order_copy', ['order_id' => $order->data['id'], 'public_key' => $order->data['public_key']]),
    'payment_receipt' => $order->payment->receipt($order),
    'order_success_modules_output' => $order_module->success($order),
  ];

  echo $_page;
