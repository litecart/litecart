<?php
  document::$layout = 'printable';

  if (empty($_GET['order_id'])) die('Missing order ID');

  $order = new ctrl_order($_GET['order_id']);

  $session_language = language::$selected['code'];
  language::set($order->data['language_code']);

  $_page = new view();
  $_page->snippets['order'] = $order->data;
  echo $_page->stitch('pages/printable_order_copy');

  language::set($session_language);

  require_once vmod::check('../includes/app_footer.inc.php');
  exit;
