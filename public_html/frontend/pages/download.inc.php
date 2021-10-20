<?php
  document::$layout = 'blank';

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

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

    $file_query = database::query(
      "select si.* from ". DB_TABLE_PREFIX ."orders_items oi
      left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = oi.stock_item_id)
      where oi.order_id = ". (int)$order['id'] ."
      and id = ". (int)$_GET['order_item_id'] ."
      and oi.stock_item_id
      and si.file
      limit 1;"
    );

    if (!$file = database::fetch($file_query)) throw new Exception('Could not find a download for the given order_item_id', 400);

    $src = FS_DIR_STORAGE . 'files/' . $file['file'];

    if (!is_file($src)) {
      trigger_error('Missing download for stock item ' . $file['id'], E_USER_WARNING);
      throw new Exception('Found a reference for but the file does not exist on the disk', 404);
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Type: ' . $file['mime_type']);
    header('Content-Disposition: attachment; filename="' . $file['filename'] .'"');
    header('Content-Length: ' . filesize($src));

    $fh = fopen($src, 'r');
    while ($buffer = fread($fh, 1024)) echo $buffer;
    fclose($fh);

    exit;

  } catch (Exception $e) {
    http_response_code(404);
    include vmod::check(FS_DIR_APP . 'frontend/pages/error_document.inc.php');
    return;
  }
