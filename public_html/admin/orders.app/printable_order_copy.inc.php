<?php
  document::$layout = 'printable';

  if (empty($_GET['order_id'])) die('Missing order ID');

  $order = new ent_order($_GET['order_id']);

  $session_language = language::$selected['code'];
  language::set($order->data['language_code']);

  $_page = new ent_view();
  $_page->snippets['order'] = $order->data;
  echo $_page->stitch('pages/printable_order_copy');

  language::set($session_language);

  require_once vmod::check(FS_DIR_APP . 'includes/app_footer.inc.php');
  exit;
