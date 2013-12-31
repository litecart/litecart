<?php
  functions::draw_fancybox('a.fancybox', array(
    'type'          => 'iframe',
    'padding'       => '40',
    'width'         => 600,
    'height'        => 800,
    'titlePosition' => 'inside',
    'transitionIn'  => 'elastic',
    'transitionOut' => 'elastic',
    'speedIn'       => 600,
    'speedOut'      => 200,
    'overlayShow'   => true
  ));
?>
<div class="widget">
  <table width="100%" class="dataTable">
    <tr class="header">
      <th nowrap="nowrap" align="left"><?php echo language::translate('title_id', 'ID'); ?></th>
      <th nowrap="nowrap" align="left" width="100%"><?php echo language::translate('title_customer_name', 'Customer Name'); ?></th>
      <th nowrap="nowrap" align="center"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
      <th nowrap="nowrap" align="center"><?php echo language::translate('title_amount', 'Amount'); ?></th>
      <th nowrap="nowrap" align="center"><?php echo language::translate('title_date', 'Date'); ?></th>
      <th nowrap="nowrap" align="center">&nbsp;</th>
    </tr>
<?php
  $orders_query = database::query(
    "select o.*, osi.name as order_status_name from ". DB_TABLE_ORDERS ." o
    left join ". DB_TABLE_ORDER_STATUSES_INFO ." osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". language::$selected['code'] ."')
    where o.order_status_id
    order by o.date_created desc
    limit 10;"
  );
  
  if (database::num_rows($orders_query) > 0) {
    
    while ($order = database::fetch($orders_query)) {
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
    <tr class="<?php echo $rowclass; ?>"<?php echo ($order['order_status_id'] == 0) ? ' style="color: #999;"' : false; ?>>
      <td nowrap="nowrap" align="left"><?php echo $order['id']; ?></td>
      <td nowrap="nowrap" align="left"><a href="<?php echo document::href_link('', array('app' => 'orders', 'doc' => 'edit_order', 'order_id' => $order['id']), true); ?>"><?php echo $order['customer_company'] ? $order['customer_company'] : $order['customer_firstname'] .' '. $order['customer_lastname']; ?></a></td>
      <td nowrap="nowrap" align="center"><?php echo ($order['order_status_id'] == 0) ? language::translate('title_uncompleted', 'Uncompleted') : $order['order_status_name']; ?></td>
      <td nowrap="nowrap" align="right"><?php echo currency::format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <td nowrap="nowrap" align="right"><?php echo strftime(language::$selected['format_datetime'], strtotime($order['date_created'])); ?></td>
      <td nowrap="nowrap"><a class="fancybox" href="<?php echo document::href_link(WS_DIR_ADMIN .'orders.app/printable_packing_slip.php', array('order_id' => $order['id'], 'media' => 'print')); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/box.png'; ?>" width="16" height="16" border="0" align="absbottom" /></a> <a class="fancybox" href="<?php echo document::href_link(WS_DIR_ADMIN .'orders.app/printable_order_copy.php', array('order_id' => $order['id'], 'media' => 'print')); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/print.png'; ?>" width="16" height="16" border="0" align="absbottom" /></a> <a href="<?php echo document::href_link('', array('app' => 'orders', 'doc' => 'edit_order', 'order_id' => $order['id']), true); ?>"><img src="<?php echo WS_DIR_IMAGES . 'icons/16x16/edit.png'; ?>" width="16" height="16" align="absbottom" /></a></td>
    </tr>
<?php
    }
  }
?>
  </table>
</div>