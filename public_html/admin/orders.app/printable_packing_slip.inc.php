<?php
  document::$layout = 'printable';

  if (empty($_GET['order_id'])) die('Missing order ID');

  $order = new ctrl_order($_GET['order_id']);

  $session_language = language::$selected['code'];
  language::set($order->data['language_code']);

  $printable_packing_slip = new view();
  $printable_packing_slip->snippets['order'] = $order->data;
  $output = $printable_packing_slip->stitch('pages/printable_packing_slip');

  language::set($session_language);

  require_once('../includes/app_footer.inc.php');
  exit;
