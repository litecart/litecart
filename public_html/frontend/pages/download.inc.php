<?php
  document::$layout = 'blank';

  header('X-Robots-Tag: noindex');

  try {

    if (empty($_GET['order_id']) || empty($_GET['public_key'])) {
      throw new Exception('Missing order_id or public_key');
    }

    $order = new ent_order($_GET['order_id']);

    if (empty($order->data['id']) || $_GET['public_key'] != $order->data['public_key']) {
      throw new Exception('Not found or invalid public_key');
    }

    if (empty($_GET['order_item_id'])) {
      throw new Exception('Missing order_item_id');
    }

    $item = database::query(
      "select oi.id, oi.product_id, p.file, p.filename, p.mime_type from ". DB_TABLE_PREFIX ."order_items
      left join ". DB_TABLE_PREFIX ."products p on (p.id = oi.product_id)
      where oi.order_id = ". (int)$order['id'] ."
      and id = ". (int)$_GET['order_item_id'] ."
      and p.file
      limit 1;"
    )->fetch();

    if (!$item) {
      throw new Exception('Could not find a download for the given order_item_id', 400);
    }

    $file = 'storage://files/' . $item['file'];

    if (!is_file($file)) {
      trigger_error('Missing download for product ' . $item['id'], E_USER_WARNING);
      throw new Exception('The downloadable file does not exist on the server', 404);
    }

    database::query(
      "update ". DB_TABLE_PREFIX ."orders_items
      set downloads = downloads + 1
      where id = ". (int)$item['id'] ."
      limit 1;"
    );

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Type: ' . $item['mime_type']);
    header('Content-Disposition: attachment; filename="' . $item['filename'] .'"');
    header('Content-Length: ' . filesize($file));

    $fh = fopen($file, 'r');
    while ($buffer = fread($fh, 1024)) echo $buffer;
    fclose($fh);

    exit;

  } catch (Exception $e) {
    http_response_code(404);
    include 'app://frontend/pages/error_document.inc.php';
    return;
  }
