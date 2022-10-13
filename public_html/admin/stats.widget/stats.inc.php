<?php

  $widget_stats_cache_token = cache::token('widget_stats', ['site', 'language'], 'file', 300);
  if (cache::capture($widget_stats_cache_token)) {

    $order_statuses = [];
    $orders_status_query = database::query(
      "select id from ". DB_TABLE_PREFIX ."order_statuses where is_sale;"
    );
    while ($order_status = database::fetch($orders_status_query)) {
      $order_statuses[] = (int)$order_status['id'];
    }

    $stats = [];

  // Total Sales
    $orders_query = database::query(
      "select count(id) as num_orders, max(payment_due) as max_order_amount, sum(payment_due - tax_total) as total_sales from ". DB_TABLE_PREFIX ."orders
      where order_status_id in ('". implode("', '", $order_statuses) ."');"
    );
    $orders = database::fetch($orders_query);
    $stats['total_sales'] = $orders['total_sales'];
    $stats['num_orders'] = $orders['num_orders'];
    $stats['max_order_amount'] = $orders['max_order_amount'];

  // Total Sales Year
    $orders_query = database::query(
      "select sum(payment_due - tax_total) as total_sales_year from ". DB_TABLE_PREFIX ."orders
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date('Y'))) ."';"
    );
    $orders = database::fetch($orders_query);
    $stats['total_sales_year'] = $orders['total_sales_year'];

  // Total Sales Month
    $orders_query = database::query(
      "select sum(payment_due - tax_total) as total_sales_month from ". DB_TABLE_PREFIX ."orders
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y'))) ."';"
    );
    $orders = database::fetch($orders_query);
    $stats['total_sales_month'] = $orders['total_sales_month'];

  // Average order amount
    $orders_query = database::query(
      "select count(id) as num_orders, sum(payment_due - tax_total) as total_sales from ". DB_TABLE_PREFIX ."orders
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d', strtotime('-6 months')) ."';"
    );
    $orders = database::fetch($orders_query);
    $stats['average_order_amount'] = (!empty($orders['total_sales']) && !empty($orders['num_orders'])) ? ($orders['total_sales'] / $orders['num_orders']) : 0;

  // Average order count
    $orders_query = database::query(
      "select count(id) as num_orders, date_format(date_created, '%Y-%m') as month from ". DB_TABLE_PREFIX ."orders
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d', strtotime('-6 months')) ."'
      group by date_format(date_created, '%Y-%m');"
    );
    $total_orders = 0;
    while ($orders = database::fetch($orders_query)) {
      $total_orders += $orders['num_orders'];
    }
    $stats['average_order_count'] = !empty($total_orders) ? round($total_orders / database::num_rows($orders_query)) : 0;

  // Num customers
    $customers_query = database::query(
      "select count(id) as num_customers from ". DB_TABLE_PREFIX ."customers;"
    );
    $customers = database::fetch($customers_query);
    $stats['num_customers'] = $customers['num_customers'];

  // Num products
    $products_query = database::query(
      "select count(id) as num_products from ". DB_TABLE_PREFIX ."products;"
    );
    $products = database::fetch($products_query);
    $stats['num_products'] = $products['num_products'];

  // Total Stock Value
    $stock_query = database::query(
      "select sum(p.quantity * p.purchase_price * c.value) as total_value
      from ". DB_TABLE_PREFIX ."products p
      left join ". DB_TABLE_PREFIX ."currencies c on (c.code = p.purchase_price_currency_code);"
    );

    $stock = database::fetch($stock_query);
    $stats['total_stock_value'] = $stock['total_value'];
?>
<div class="widget">
  <div class="card card-default">
    <div class="card-header">
      <div class="card-title">
        <div class="card-title"><?php echo language::translate('title_statistics', 'Statistics'); ?></div>
      </div>
    </div>

    <div class="card-body table-responsive">
      <div class="row" style="margin-bottom: 0;">
        <div class="col-md-6">
          <table class="table table-striped table-hover data-table" style="margin-bottom: 0;">
            <tbody>
              <tr>
                <td><?php echo language::translate('title_total_sales', 'Total Sales') .' '. language::strftime('%B'); ?>:</td>
                <td class="text-end"><?php echo currency::format($stats['total_sales_month'], false, settings::get('store_currency_code')); ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_total_sales', 'Total Sales') .' '. date('Y'); ?>:</td>
                <td class="text-end"><?php echo currency::format($stats['total_sales_year'], false, settings::get('store_currency_code')); ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_total_sales', 'Total Sales'); ?>:</td>
                <td class="text-end"><?php echo currency::format($stats['total_sales'], false, settings::get('store_currency_code')); ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_total_stock_value', 'Total Stock Value'); ?>:</td>
                <td class="text-end"><?php echo currency::format($stats['total_stock_value'], false, settings::get('store_currency_code')); ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_total_number_of_customers', 'Total Number of Customers'); ?>:</td>
                <td class="text-end"><?php echo language::number_format($stats['num_customers'], 0); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="col-md-6">
          <table class="table table-striped table-hover data-table" style="margin-bottom: 0;">
            <tbody>
              <tr>
                <td><?php echo language::translate('title_total_number_of_orders', 'Total Number of Orders'); ?>:</td>
                <td class="text-end"><?php echo language::number_format($stats['num_orders'], 0); ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_monthly_average_number_of_orders', 'Monthly Average Number of Orders'); ?>:</td>
                <td class="text-end"><?php echo language::number_format($stats['average_order_count'], 0); ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_highest_order_amount', 'Highest Order Amount'); ?>:</td>
                <td class="text-end"><?php echo currency::format($stats['max_order_amount'], false, settings::get('store_currency_code')); ?></td>
              </tr>
              <tr>
                <td><?php echo language::translate('title_average_order_amount', 'Average Order Amount'); ?>:</td>
                <td class="text-end"><?php echo currency::format($stats['average_order_amount'], false, settings::get('store_currency_code')); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
    cache::end_capture($widget_stats_cache_token);
  }
?>