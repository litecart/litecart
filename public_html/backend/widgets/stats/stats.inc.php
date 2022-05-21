<?php

  $widget_stats_cache_token = cache::token('widget_stats', ['site'], 'file', 300);
  if (cache::capture($widget_stats_cache_token)) {

    $order_statuses = database::fetch_all(database::query(
      "select id from ". DB_TABLE_PREFIX ."order_statuses where is_sale;"
    ), 'id');

    $stats = [];

  // Total Sales

    $orders = database::fetch(database::query(
      "select count(id) as num_orders, max(total) as max_order_amount, sum(total - total_tax) as total_sales from ". DB_TABLE_PREFIX ."orders
      where order_status_id in ('". implode("', '", $order_statuses) ."');"
    ));

    $stats['total_sales'] = $orders['total_sales'];
    $stats['num_orders'] = $orders['num_orders'];
    $stats['max_order_amount'] = $orders['max_order_amount'];

  // Total Sales Year
    $stats['total_sales_year'] = database::fetch(database::query(
      "select sum(total - total_tax) as total_sales_year from ". DB_TABLE_PREFIX ."orders
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date('Y'))) ."';"
    ), 'total_sales_year');

  // Total Sales Month
    $stats['total_sales_month'] = database::fetch(database::query(
      "select sum(total - total_tax) as total_sales_month from ". DB_TABLE_PREFIX ."orders
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y'))) ."';"
    ), 'total_sales_month');

  // Average order amount
    $orders = database::fetch(database::query(
      "select count(id) as num_orders, sum(total - total_tax) as total_sales from ". DB_TABLE_PREFIX ."orders
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d', strtotime('-6 months')) ."';"
    ));

    $stats['average_order_amount'] = (!empty($orders['total_sales']) && !empty($orders['num_orders'])) ? ($orders['total_sales'] / $orders['num_orders']) : 0;

  // Average order count
    $orders_query = database::query(
      "select count(id) as num_orders from ". DB_TABLE_PREFIX ."orders
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
    $stats['num_customers'] = database::fetch(database::query(
      "select count(id) as num_customers from ". DB_TABLE_PREFIX ."customers;"
    ), 'num_customers');

  // Num products
    $stats['num_products'] = database::fetch(database::query(
      "select count(id) as num_products from ". DB_TABLE_PREFIX ."products;"
    ), 'num_products');
?>
<div id="widget-stats" class="card card-widget">
  <div class="card-header">
    <div class="card-title">
      <?php echo language::translate('title_statistics', 'Statistics'); ?>
    </div>
  </div>

  <div class="card-body table-responsive">

    <div class="row">
      <div class="col-md-6">

        <table class="table table-striped table-hover data-table">
          <tbody>
            <tr>
              <td><?php echo language::translate('title_total_sales', 'Total Sales') .' '. language::strftime('%B'); ?>:</td>
              <td class="text-end"><?php echo currency::format($stats['total_sales_month'], false, settings::get('site_currency_code')); ?></td>
            </tr>
            <tr>
              <td><?php echo language::translate('title_total_sales', 'Total Sales') .' '. date('Y'); ?>:</td>
              <td class="text-end"><?php echo currency::format($stats['total_sales_year'], false, settings::get('site_currency_code')); ?></td>
            </tr>
            <tr>
              <td><?php echo language::translate('title_total_sales', 'Total Sales'); ?>:</td>
              <td class="text-end"><?php echo currency::format($stats['total_sales'], false, settings::get('site_currency_code')); ?></td>
            </tr>
            <tr>
              <td><?php echo language::translate('title_total_number_of_customers', 'Total Number of Customers'); ?>:</td>
              <td class="text-end"><?php echo (int)$stats['num_customers']; ?></td>
            </tr>
          </tbody>
        </table>

      </div>

      <div class="col-md-6">

        <table class="table table-striped table-hover data-table">
          <tbody>
            <tr>
              <td><?php echo language::translate('title_total_number_of_orders', 'Total Number of Orders'); ?>:</td>
              <td class="text-end"><?php echo (int)$stats['num_orders']; ?></td>
            </tr>
            <tr>
              <td><?php echo language::translate('title_monthly_average_number_of_orders', 'Monthly Average Number of Orders'); ?>:</td>
              <td class="text-end"><?php echo $stats['average_order_count']; ?></td>
            </tr>
            <tr>
              <td><?php echo language::translate('title_average_order_amount', 'Average Order Amount'); ?>:</td>
              <td class="text-end"><?php echo currency::format($stats['average_order_amount'], false, settings::get('site_currency_code')); ?></td>
            </tr>
            <tr>
              <td><?php echo language::translate('title_highest_order_amount', 'Highest Order Amount'); ?>:</td>
              <td class="text-end"><?php echo currency::format($stats['max_order_amount'], false, settings::get('site_currency_code')); ?></td>
            </tr>
          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>
<?php
    cache::end_capture($widget_stats_cache_token);
  }
?>