<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo $system->language->translate('title_monthly_sales', 'Monthly Sales'); ?></h1>

<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" class="dataTable">
  <tr class="header">
    <th nowrap="nowrap" align="left" width="100%"><?php echo $system->language->translate('title_month', 'Month'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_total_sales', 'Total Sales'); ?></th>
    <th nowrap="nowrap" align="left"><?php echo $system->language->translate('title_total_tax', 'Total Tax'); ?></th>
  </tr>
<?php
  $order_statuses = array();
  $orders_status_query = $system->database->query(
    "select id from ". DB_TABLE_ORDERS_STATUS ." where is_sale;"
  );
  while ($order_status = $system->database->fetch($orders_status_query)) {
    $order_statuses[] = (int)$order_status['id'];
  }

  for ($timestamp = mktime(); $timestamp > strtotime('-12 months'); $timestamp = strtotime('-1 month', $timestamp)) {
    $orders_query = $system->database->query(
      "select sum(payment_due) as total_sales, sum(tax_total) as total_tax from ". DB_TABLE_ORDERS ."
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $timestamp), 0, date('Y', $timestamp))) ."'
      and date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $timestamp), date('t', $timestamp), date('Y', $timestamp))) ."';"
    );
    $order = $system->database->fetch($orders_query);
    
    if (!isset($rowclass) || $rowclass == 'even') {
      $rowclass = 'odd';
    } else {
      $rowclass = 'even';
    }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td align="left" valign="top"><?php echo strftime('%B', $timestamp); ?></td>
    <td align="right" valign="top" nowrap="nowrap"><?php echo $system->currency->format($order['total_sales'], false, false, $system->settings->get('store_currency_code')); ?></td>
    <td align="right" valign="top" nowrap="nowrap"><?php echo $system->currency->format($order['total_tax'], false, false, $system->settings->get('store_currency_code')); ?></td>
  </tr>
<?php
  }
?>
</table>