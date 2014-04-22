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

<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo language::translate('title_monthly_sales', 'Monthly Sales'); ?></h1>

<table width="100%" align="center" class="dataTable">
  <tr class="header">
    <th nowrap="nowrap" align="left" width="100%"><?php echo language::translate('title_month', 'Month'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo language::translate('title_total_tax', 'Total Tax'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo language::translate('title_total_sales', 'Total Sales'); ?></th>
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
      "select sum(payment_due - tax_total) as total_sales, sum(tax_total) as total_tax from ". DB_TABLE_ORDERS ."
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $timestamp), 0, date('Y', $timestamp))) ."'
      and date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $timestamp), date('t', $timestamp), date('Y', $timestamp))) ."';"
    );
    $order = database::fetch($orders_query);
    
    if (!isset($rowclass) || $rowclass == 'even') {
      $rowclass = 'odd';
    } else {
      $rowclass = 'even';
    }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td align="left"><?php echo strftime('%B, %Y', $timestamp); ?></td>
    <td align="right" nowrap="nowrap"><?php echo currency::format($order['total_tax'], false, false, settings::get('store_currency_code')); ?></td>
    <td align="right" nowrap="nowrap"><?php echo currency::format($order['total_sales'], false, false, settings::get('store_currency_code')); ?></td>
  </tr>
<?php
  }
?>
</table>