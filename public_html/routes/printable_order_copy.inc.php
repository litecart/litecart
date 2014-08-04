<?php
  
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  document::$layout = 'printable';
  
  if (empty($_GET['order_id']) || empty($_GET['checksum'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
  }
  
  document::$snippets['title'][] = language::translate('title_order', 'Order') .' #'. $_GET['order_id'];
  
  $order = new ctrl_order('load', $_GET['order_id']);
  
  if (empty($order->data['id']) || $_GET['checksum'] != functions::general_order_public_checksum($order->data['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit;
  }
  
  echo $order->draw_printable_copy();
?>