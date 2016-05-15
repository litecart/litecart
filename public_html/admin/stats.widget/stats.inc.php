<?php
  $order_statuses = array();
  $orders_status_query = database::query(
    "select id from ". DB_TABLE_ORDER_STATUSES ." where is_sale;"
  );
  while ($order_status = database::fetch($orders_status_query)) {
    $order_statuses[] = (int)$order_status['id'];
  }

  $stats = array();

// Total Sales
  $orders_query = database::query(
    "select sum(payment_due - tax_total) as total_sales from ". DB_TABLE_ORDERS ."
    where order_status_id in ('". implode("', '", $order_statuses) ."');"
  );
  $orders = database::fetch($orders_query);
  $stats['total_sales'] = $orders['total_sales'];

// Total Sales Year
  $orders_query = database::query(
    "select sum(payment_due - tax_total) as total_sales_year from ". DB_TABLE_ORDERS ."
    where order_status_id in ('". implode("', '", $order_statuses) ."')
    and date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date('Y'))) ."';"
  );
  $orders = database::fetch($orders_query);
  $stats['total_sales_year'] = $orders['total_sales_year'];

// Total Sales Month
  $orders_query = database::query(
    "select sum(payment_due - tax_total) as total_sales_month from ". DB_TABLE_ORDERS ."
    where order_status_id in ('". implode("', '", $order_statuses) ."')
    and date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y'))) ."';"
  );
  $orders = database::fetch($orders_query);
  $stats['total_sales_month'] = $orders['total_sales_month'];

// Average order
  $orders_query = database::query(
    "select count(id) as num_orders, sum(payment_due - tax_total) as total_sales from ". DB_TABLE_ORDERS ."
    where order_status_id in ('". implode("', '", $order_statuses) ."');"
  );
  $orders = database::fetch($orders_query);
  @$stats['average_order_amount'] = $orders['total_sales'] / $orders['num_orders'];

// Num customers
  $customers_query = database::query(
    "select count(id) as num_customers from ". DB_TABLE_CUSTOMERS .";"
  );
  $customers = database::fetch($customers_query);
  $stats['num_customers'] = $customers['num_customers'];

// Num products
  $products_query = database::query(
    "select count(id) as num_products from ". DB_TABLE_PRODUCTS .";"
  );
  $products = database::fetch($products_query);
  $stats['num_products'] = $products['num_products'];
?>
<div class="widget">
  <table width="100%" class="dataTable">
    <tr class="header">
      <th colspan="2"><?php echo language::translate('title_statistics', 'Statistics'); ?></th>
    </tr>
    <tr class="odd">
      <td><?php echo language::translate('title_total_sales', 'Total Sales'); ?>:</td>
      <td style="text-align: right;"><?php echo currency::format($stats['total_sales'], false, false, settings::get('store_currency_code')); ?></td>
    </tr>
    <tr class="even">
      <td><?php echo language::translate('title_total_sales', 'Total Sales') .' '. date('Y'); ?>:</td>
      <td style="text-align: right;"><?php echo currency::format($stats['total_sales_year'], false, false, settings::get('store_currency_code')); ?></td>
    </tr>
    <tr class="odd">
      <td><?php echo language::translate('title_total_sales', 'Total Sales') .' '. language::strftime('%B'); ?>:</td>
      <td style="text-align: right;"><?php echo currency::format($stats['total_sales_month'], false, false, settings::get('store_currency_code')); ?></td>
    </tr>
    <tr class="even">
      <td><?php echo language::translate('title_average_order_amount', 'Average Order Amount'); ?>:</td>
      <td style="text-align: right;"><?php echo currency::format($stats['average_order_amount'], false, false, settings::get('store_currency_code')); ?></td>
    </tr>
    <tr class="odd">
      <td><?php echo language::translate('title_number_of_customers', 'Number of Customers'); ?>:</td>
      <td style="text-align: right;"><?php echo (int)$stats['num_customers']; ?></td>
    </tr>
    <tr class="even">
      <td><?php echo language::translate('title_number_of_products', 'Number of Products'); ?>:</td>
      <td style="text-align: right;"><?php echo (int)$stats['num_products']; ?></td>
    </tr>
  </table>
</div>