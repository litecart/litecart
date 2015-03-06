<div style="float: right; display: inline;">
  <?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>
    <?php echo functions::form_draw_hidden_field('app'); ?>
    <?php echo functions::form_draw_hidden_field('doc'); ?>
    <table>
      <tr>
        <td><?php echo language::translate('title_date_period', 'Date Period'); ?>:</td>
        <td><?php echo functions::form_draw_month_field('date_from'); ?> - <?php echo functions::form_draw_month_field('date_to'); ?></td>
        <td><?php echo functions::form_draw_button('filter', language::translate('title_filter_now', 'Filter')); ?></td>
      </tr>
    </table>
  <?php echo functions::form_draw_form_end(); ?>
</div>

<h1 style="margin-top: 0px;"><?php echo $app_icon; ?><?php echo language::translate('title_monthly_sales', 'Monthly Sales'); ?></h1>

<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th width="100%"><?php echo language::translate('title_month', 'Month'); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_subtotal', 'Subtotal'); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_shipping_fees', 'Shipping Fees'); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_payment_fees', 'Payment Fees'); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_tax', 'Tax'); ?></th>
    <th style="text-align: center;"><?php echo language::translate('title_total', 'Total'); ?></th>
  </tr>
<?php
  $order_statuses = array();
  $orders_status_query = database::query(
    "select id from ". DB_TABLE_ORDER_STATUSES ." where is_sale;"
  );
  while ($order_status = database::fetch($orders_status_query)) {
    $order_statuses[] = (int)$order_status['id'];
  }
  
  $timestamp_from = !empty($_GET['date_from']) ? mktime(23, 59, 59, date('m', strtotime($_GET['date_from'])), date('d', strtotime($_GET['date_from'])), date('Y', strtotime($_GET['date_from']))) : null;
  $timestamp_to = !empty($_GET['date_to']) ? mktime(23, 59, 59, date('m', strtotime($_GET['date_to'])), date('d', strtotime($_GET['date_to'])), date('Y', strtotime($_GET['date_to']))) : time();
  
  $row = database::fetch(database::query("select min(date_created) from ". DB_TABLE_ORDERS ." limit 1;"));
  if (empty($row['min(date_created)'])) $row['min(date_created)'] = date('Y-m-01 00:00:00');
  
  $timestamp_from = ($timestamp_from < strtotime($row['min(date_created)'])) ? strtotime($row['min(date_created)']) : $timestamp_from;
  
  for ($timestamp = $timestamp_to; $timestamp >= $timestamp_from; $timestamp = strtotime('-1 month', $timestamp)) {
    $orders_query = database::query(
      "select
        sum(payment_due) as total_sales,
        sum(tax_total) as total_tax
      from ". DB_TABLE_ORDERS ."
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $timestamp), 0, date('Y', $timestamp))) ."'
      and date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $timestamp), date('t', $timestamp), date('Y', $timestamp))) ."';"
    );
    $orders = database::fetch($orders_query);
    
    $orders_total_query = database::query(
      "select sum(ot.value) as total_value
      from ". DB_TABLE_ORDERS ." o
      left join ". DB_TABLE_ORDERS_TOTALS ." ot on (ot.order_id = o.id)
      where o.order_status_id in ('". implode("', '", $order_statuses) ."')
      and o.date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $timestamp), 0, date('Y', $timestamp))) ."'
      and o.date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $timestamp), date('t', $timestamp), date('Y', $timestamp))) ."'
      and ot.module_id = 'ot_subtotal';"
    );
    $orders_total = database::fetch($orders_total_query);
    $orders['total_subtotal'] = $orders_total['total_value'];
    
    $orders_total_query = database::query(
      "select sum(ot.value) as total_value
      from ". DB_TABLE_ORDERS ." o
      left join ". DB_TABLE_ORDERS_TOTALS ." ot on (ot.order_id = o.id)
      where o.order_status_id in ('". implode("', '", $order_statuses) ."')
      and o.date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $timestamp), 0, date('Y', $timestamp))) ."'
      and o.date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $timestamp), date('t', $timestamp), date('Y', $timestamp))) ."'
      and ot.module_id = 'ot_shipping_fee';"
    );
    $orders_total = database::fetch($orders_total_query);
    $orders['total_shipping_fees'] = $orders_total['total_value'];
    
    $orders_total_query = database::query(
      "select sum(ot.value) as total_value
      from ". DB_TABLE_ORDERS ." o
      left join ". DB_TABLE_ORDERS_TOTALS ." ot on (ot.order_id = o.id)
      where o.order_status_id in ('". implode("', '", $order_statuses) ."')
      and o.date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $timestamp), 0, date('Y', $timestamp))) ."'
      and o.date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $timestamp), date('t', $timestamp), date('Y', $timestamp))) ."'
      and ot.module_id = 'ot_payment_fee';"
    );
    $orders_total = database::fetch($orders_total_query);
    $orders['total_payment_fees'] = $orders_total['total_value'];
    
    if (!isset($rowclass) || $rowclass == 'even') {
      $rowclass = 'odd';
    } else {
      $rowclass = 'even';
    }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td><?php echo strftime('%B, %Y', $timestamp); ?></td>
    <td style="text-align: right;"><?php echo currency::format($orders['total_subtotal'], false, false, settings::get('store_currency_code')); ?></td>
    <td style="text-align: right;"><?php echo currency::format($orders['total_shipping_fees'], false, false, settings::get('store_currency_code')); ?></td>
    <td style="text-align: right;"><?php echo currency::format($orders['total_payment_fees'], false, false, settings::get('store_currency_code')); ?></td>
    <td style="text-align: right;"><?php echo currency::format($orders['total_tax'], false, false, settings::get('store_currency_code')); ?></td>
    <td style="text-align: right;"><?php echo currency::format($orders['total_sales'], false, false, settings::get('store_currency_code')); ?></td>
  </tr>
<?php
  }
?>
</table>