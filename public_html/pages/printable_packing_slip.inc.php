<?php
  document::$layout = 'printable';

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex">';

  if (!isset($_GET['public_key']) && isset($_GET['checksum'])) $_GET['public_key'] = $_GET['checksum']; // Backwards compatible

  try {

    if (empty($_GET['order_id']) || empty($_GET['public_key'])) {
      throw new Exception('Missing order_id or public_key', 404);
    }

    $order = new ent_order($_GET['order_id']);

    if (empty($order->data['id']) || $_GET['public_key'] != $order->data['public_key']) {
      throw new Exception('Not found or invalid public_key', 400);
    }

  } catch (Exception $e) {
    http_response_code($e->getCode() ?: 404);
    include vmod::check(FS_DIR_APP . 'pages/error_document.inc.php');
    return;
  }

  document::$snippets['title'][] = language::translate('title_order', 'Order') .' #'. (int)$order->data['id'];

  $session_language = language::$selected['code'];
  language::set($order->data['language_code']);

  $_page = new ent_view();
  $_page->snippets = [
    'paper_size' => settings::get('default_print_paper_size'),
    'text_direction' => !empty(language::$languages[$order->data['language_code']]['direction']) ? language::$languages[$order->data['language_code']]['direction'] : 'ltr',
    'order' => $order->data,
    'comments' => [],
  ];

  foreach ($order->data['comments'] as $comment) {
    if (!empty($comment['hidden'])) continue;
    $_page->snippets['comments'][] = $comment;
  }

  echo $_page->stitch('pages/printable_packing_slip');

  language::set($session_language);
