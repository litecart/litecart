<?php
  ob_clean();
  document::$layout = 'printable';

  if (empty($_GET['order_id'])) die('Missing order ID');

  $order = new ctrl_order($_GET['order_id']);

  echo $order->draw_printable_copy();

  exit;
?>