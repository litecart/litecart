<?php
  document::$layout = 'blank';

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  if (!isset($_GET['public_key']) && isset($_GET['checksum'])) $_GET['public_key'] = $_GET['checksum']; // Backwards compatible

  if (empty($_GET['order_id']) || empty($_GET['public_key'])) {
    http_response_code(400);
    die('Missing order or key');
  }

  $order = new ent_order($_GET['order_id']);

  if (empty($order->data['id']) || $_GET['public_key'] != $order->data['public_key']) {
    http_response_code(401);
    die('Invalid key');
  }

  document::$snippets['title'][] = language::translate('title_order', 'Order') .' #'. (int)$order->data['id'];

  $session_language = language::$selected['code'];
  language::set($order->data['language_code']);

  $_page = new ent_view();
  $_page->snippets = array(
    'order' => $order->data,
    'comments' => array(),
  );

  foreach ($order->data['comments'] as $comment) {
    if (!empty($comment['hidden'])) continue;

    switch($comment['author']) {
      case 'customer':
        $comment['type'] = 'local';
        break;
      case 'staff':
        $comment['type'] = 'remote';
        break;
      default:
        $comment['type'] = 'event';
        break;
    }

    $_page->snippets['comments'][] = $comment;
  }

  echo $_page->stitch('pages/order');

  language::set($session_language);
