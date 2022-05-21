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

    $item = database::fetch(database::query(
      "select oi.id, oi.stck_item_id, si.file, si.filename, si.mime_type from ". DB_TABLE_PREFIX ."orders_items oi
      left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = oi.stock_item_id)
      where oi.order_id = ". (int)$order['id'] ."
      and id = ". (int)$_GET['order_item_id'] ."
      and oi.stock_item_id
      and si.file
      limit 1;"
    ));

    if (!$item) {
      throw new Exception('Could not find a download for the given order_item_id', 400);
    }

    $file = FS_DIR_STORAGE . 'files/' . $item['file'];

    if (!is_file($file)) {
      trigger_error('Missing download for stock item ' . $item['id'], E_USER_WARNING);
      throw new Exception('Found a reference for but the file does not exist on the disk', 404);
    }

    database::query(
      "update ". DB_TABLE_PREFIX ."orders_items
      set downloads = downloads + 1
      where id = ". (int)$item['id'] ."
      limit 1;"
    );

    database::query(
      "update ". DB_TABLE_PREFIX ."stock_items
      set downloads = downloads + 1
      where id = ". (int)$item['stock_item_id'] ."
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
    include vmod::check(FS_DIR_APP . 'frontend/pages/error_document.inc.php');
    return;
  }
