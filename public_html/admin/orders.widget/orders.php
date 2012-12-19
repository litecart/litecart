<div class="widget">
  <table width="100%" class="dataTable">
    <tr class="header">
      <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
      <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_customer_name', 'Customer Name'); ?></th>
      <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_amount', 'Amount'); ?></th>
      <th nowrap="nowrap" align="center"><?php echo $system->language->translate('title_date', 'Date'); ?></th>
    </tr>
<?php
  $orders_query = $system->database->query(
    "select o.*, osi.name as order_status_name from ". DB_TABLE_ORDERS ." o
    left join ". DB_TABLE_ORDERS_STATUS_INFO ." osi on (osi.order_status_id = o.order_status_id and osi.language_code = '". $system->language->selected['code'] ."')
    where o.order_status_id
    order by o.date_created desc
    limit 5;"
  );
  
  if ($system->database->num_rows($orders_query) > 0) {
    
    while ($order = $system->database->fetch($orders_query)) {
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
?>
    <tr class="<?php echo $rowclass; ?>"<?php echo ($order['order_status_id'] == 0) ? ' style="color: #999;"' : false; ?>>
      <td nowrap="nowrap" align="left"><?php echo $order['id']; ?></td>
      <td nowrap="nowrap" align="left"><?php echo $order['customer_firstname'] .' '. $order['customer_lastname']; ?></td>
      <td nowrap="nowrap" align="right"><?php echo $system->currency->format($order['payment_due'], false, false, $order['currency_code'], $order['currency_value']); ?></td>
      <td nowrap="nowrap" align="right"><?php echo strftime($system->language->selected['format_datetime'], strtotime($order['date_created'])); ?></td>
    </tr>
<?php
    }
  }
?>
  </table>
</div>