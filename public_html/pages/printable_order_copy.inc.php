<?php
  document::$layout = 'printable';
  
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  if (empty($_GET['order_id']) || empty($_GET['checksum'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
  }
  
  $order = new ctrl_order('load', $_GET['order_id']);
  
  document::$snippets['title'][] = language::translate('title_order', 'Order') .' #'. (int)$order->data['id'];

  if (empty($order->data['id']) || $_GET['checksum'] != functions::general_order_public_checksum($order->data['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit;
  }
  
  echo $order->draw_printable_copy();
?>