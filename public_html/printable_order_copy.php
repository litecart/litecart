<?php
  require_once('includes/app_header.inc.php');
  
  header('X-Robots-Tag: noindex');
  $system->document->snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  $system->document->layout = 'default';
  $system->document->viewport = 'printable';
  
  if (empty($_GET['order_id']) || empty($_GET['checksum'])) {
    header('HTTP/1.1 401 Unathorized');
    exit;
  }
  
  $system->document->snippets['title'][] = $system->language->translate('title_order', 'Order') .' #'. $_GET['order_id'];
  //$system->document->snippets['keywords'] = '';
  //$system->document->snippets['description'] = '';
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'order.inc.php');
  $order = new ctrl_order('load', $_GET['order_id']);
  
  if ($_GET['checksum'] != $system->functions->general_order_public_checksum($order->data['id'])) {
    header('HTTP/1.1 401 Unathorized');
    exit;
  }
  
  echo $order->draw_printable_copy();
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>