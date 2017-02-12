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
    "select count(id) as num_orders, max(payment_due) as max_order_amount, sum(payment_due - tax_total) as total_sales from ". DB_TABLE_ORDERS ."
    where order_status_id in ('". implode("', '", $order_statuses) ."');"
  );
  $orders = database::fetch($orders_query);
  $stats['total_sales'] = $orders['total_sales'];
  $stats['num_orders'] = $orders['num_orders'];
  $stats['max_order_amount'] = $orders['max_order_amount'];

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

// Average order amount
  $orders_query = database::query(
    "select count(id) as num_orders, sum(payment_due - tax_total) as total_sales from ". DB_TABLE_ORDERS ."
    where order_status_id in ('". implode("', '", $order_statuses) ."')
    and date_created >= '". date('Y-m-d', strtotime('-6 months')) ."';"
  );
  $orders = database::fetch($orders_query);
  @$stats['average_order_amount'] = $orders['total_sales'] / $orders['num_orders'];

// Average order count
  $orders_query = database::query(
    "select count(id) as num_orders, count(month(date_created)) as months from ". DB_TABLE_ORDERS ."
    where order_status_id in ('". implode("', '", $order_statuses) ."')
    and date_created >= '". date('Y-m-d', strtotime('-6 months')) ."'
    group by month(date_created);"
  );
  $orders = database::fetch($orders_query);
  @$stats['average_order_count'] = round($orders['num_orders'] / $orders['months'], 1);

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
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo language::translate('title_statistics', 'Statistics'); ?></h3>
    </div>

    <div class="panel-body table-responsive">
      <div class="row">
        <div class="col-md-6">
          <table class="table table-striped data-table">
            <tbody>
              <tr>
                <td><?php echo language::translate('title_total_sales', 'Total Sales'); ?>:</td>
                <td style="text-align: right;"><?php echo currency::format($stats['total_sales'], false, settings::get('store_currency_code')); ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_total_sales', 'Total Sales') .' '. date('Y'); ?>:</td>
                <td style="text-align: right;"><?php echo currency::format($stats['total_sales_year'], false, settings::get('store_currency_code')); ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_total_sales', 'Total Sales') .' '. strftime('%B'); ?>:</td>
                <td style="text-align: right;"><?php echo currency::format($stats['total_sales_month'], false, settings::get('store_currency_code')); ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_total_number_of_customers', 'Total Number of Customers'); ?>:</td>
                <td style="text-align: right;"><?php echo (int)$stats['num_customers']; ?></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="col-md-6">
          <table class="table table-striped data-table">
            <tbody>
              <tr>
                <td><?php echo language::translate('title_total_number_of_orders', 'Total Number of Orders'); ?>:</td>
                <td style="text-align: right;"><?php echo (int)$stats['num_orders']; ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_monthly_average_number_of_orders', 'Monthly Average Number of Orders'); ?>:</td>
                <td style="text-align: right;"><?php echo $stats['average_order_count']; ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_average_order_amount', 'Average Order Amount'); ?>:</td>
                <td style="text-align: right;"><?php echo currency::format($stats['average_order_amount'], false, settings::get('store_currency_code')); ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_highest_order_amount', 'Highest Order Amount'); ?>:</td>
                <td style="text-align: right;"><?php echo currency::format($stats['max_order_amount'], false, settings::get('store_currency_code')); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>