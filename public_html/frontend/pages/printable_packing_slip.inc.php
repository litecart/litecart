<?php
  document::$layout = 'printable';

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

  } catch (Exception $e) {
    http_response_code(404);
    include vmod::check(FS_DIR_APP . 'frontend/pages/error_document.inc.php');
    return;
  }

  document::$snippets['title'][] = language::translate('title_order', 'Order') .' #'. (int)$order->data['id'];

  $session_language = language::$selected['code'];
  language::set($order->data['language_code']);

  $_page = new ent_view();
  $_page->snippets['order'] = $order->data;
  echo $_page->stitch('pages/printable_packing_slip.inc.php');

  language::set($session_language);
