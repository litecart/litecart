<?php
  document::$layout = 'printable';

  if (empty($_GET['order_id'])) die('Missing order ID');

  $order = new ctrl_order($_GET['order_id']);

  echo $order->draw_printable_packing_slip();

  require_once('../includes/app_footer.inc.php');
  exit;
