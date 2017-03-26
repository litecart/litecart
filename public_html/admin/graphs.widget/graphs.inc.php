<?php

  document::$snippets['head_tags']['chartist'] = '<link rel="stylesheet" href="'. WS_DIR_EXT .'chartist/chartist.min.css" />';
  document::$snippets['foot_tags']['chartist'] = '<script src="'. WS_DIR_EXT .'chartist/chartist.min.js"></script>';

  $widget_graphs_cache_id = cache::cache_id('widget_graphs');
  if (cache::capture($widget_graphs_cache_id, 'file', 300)) {

  // Order Statuses flagged as Sale
    $order_statuses = array();
    $orders_status_query = database::query(
      "select id from ". DB_TABLE_ORDER_STATUSES ." where is_sale;"
    );
    while ($order_status = database::fetch($orders_status_query)) {
      $order_statuses[] = (int)$order_status['id'];
    }

  // Monthly Sales

    $orders_query = database::query(
      "select sum(payment_due - tax_total) as total_sales, tax_total as total_tax, date_format(date_created, '%m') as month from ". DB_TABLE_ORDERS ."
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created > '". date('Y-m-01 00:00:00', strtotime('-11 months')) ."'
      group by month
      order by month asc;"
    );

    $monthly_sales = array();
    while($orders = database::fetch($orders_query)) {
      $monthly_sales[$orders['month']]['total_sales'] = (int)$orders['total_sales'];
    }

    $orders_query = database::query(
      "select sum(payment_due - tax_total) as total_sales, tax_total as total_tax, date_format(date_created, '%m') as month from ". DB_TABLE_ORDERS ."
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created > '". date('Y-m-01 00:00:00', strtotime('-23 months')) ."' and date_created < '". date('Y-m-t 23:59:59', strtotime('-12 months')) ."'
      group by month
      order by month asc;"
    );

    while($orders = database::fetch($orders_query)) {
      $monthly_sales[$orders['month']]['total_sales_last_year'] = (int)$orders['total_sales'];
    }

    for ($timestamp=time(); strtotime('-12 months') < $timestamp; $timestamp = strtotime('-1 month', $timestamp)) {
      $monthly_sales[date('m', $timestamp)]['month'] = date('Y-m', $timestamp);
      $monthly_sales[date('m', $timestamp)]['label'] = language::strftime('%b', $timestamp);
      if (!isset($monthly_sales[date('m', $timestamp)]['total_sales'])) $monthly_sales[date('m', $timestamp)]['total_sales'] = 0;
      if (!isset($monthly_sales[date('m', $timestamp)]['total_sales_last_year'])) $monthly_sales[date('m', $timestamp)]['total_sales_last_year'] = 0;
    }

    $monthly_sales[date('m')]['label'] = '★'.$monthly_sales[date('m')]['label'];

    uasort($monthly_sales, function($a, $b) { return $a['month'] > $b['month'] ? 1 : -1; });

  // Daily Sales

    $daily_sales = array();

    $orders_query = database::query(
      "select round(sum(payment_due - tax_total) / count(distinct(date(date_created))), 2) as total_sales, tax_total as total_tax, weekday(date_created)+1 as weekday from ". DB_TABLE_ORDERS ."
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and (date_created >= '". date('Y-m-d 00:00:00', strtotime('Monday this week')) ."')
      group by weekday
      order by weekday asc;"
    );

    while($orders = database::fetch($orders_query)) {
      $daily_sales[$orders['weekday']]['total_sales'] = (int)$orders['total_sales'];
    }

    $orders_query = database::query(
      "select round(sum(payment_due - tax_total) / count(distinct(date(date_created))), 2) as average_sales, tax_total as total_tax, weekday(date_created)+1 as weekday, group_concat(payment_due - tax_total) from ". DB_TABLE_ORDERS ."
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and (date_created > '". date('Y-m-d H:i:s', strtotime('-30 days', strtotime('Monday this week'))) ."' and date_created < '". date('Y-m-d 00:00:00', strtotime('Monday this week')) ."')
      group by weekday
      order by weekday asc;"
    );

    while($orders = database::fetch($orders_query)) {
      $daily_sales[$orders['weekday']]['average_sales'] = (int)$orders['average_sales'];
    }

    for ($timestamp=time(); strtotime('-7 days') < $timestamp; $timestamp = strtotime('-1 day', $timestamp)) {
      $daily_sales[date('N', $timestamp)]['label'] = language::strftime('%a', $timestamp);
      if (!isset($daily_sales[date('N', $timestamp)]['total_sales'])) $daily_sales[date('N', $timestamp)]['total_sales'] = 0;
      if (!isset($daily_sales[date('N', $timestamp)]['average_sales'])) $daily_sales[date('N', $timestamp)]['average_sales'] = 0;
    }

    $daily_sales[date('N')]['label'] = '★'.$daily_sales[date('N')]['label'];

    ksort($daily_sales);
?>
<style>
#chart-sales-monthly .ct-label, #chart-sales-daily .ct-label {
  font-size: 12px;
  color: #999;
}

#chart-sales-monthly .ct-series-a .ct-bar, #chart-sales-daily .ct-series-a .ct-bar {
  stroke: rgba(0,0,0,0.15);
}

#chart-sales-monthly .ct-bar{
  stroke-width: 20px;
}
#chart-sales-daily .ct-bar {
  stroke-width: 10px;
}
</style>

<div class="row">
  <div class="widget col-md-8">
    <div id="chart-sales-monthly" style="width: 100%; height: 250px;" title="<?php echo language::translate('title_monthly_sales', 'Monthly Sales'); ?>"></div>
  </div>

  <div class="widget col-md-4">
    <div id="chart-sales-daily" style="width: 100%; height: 250px" title="<?php echo language::translate('title_daily_sales', 'Daily Sales'); ?>"></div>
  </div>
</div>

<script>
// Monthly Sales

  var data = {
    labels: <?php echo json_encode(array_column($monthly_sales, 'label')); ?>,
    series: <?php echo json_encode(array(array_column($monthly_sales, 'total_sales_last_year'), array_column($monthly_sales, 'total_sales'))); ?>
  };

  var options = {
    seriesBarDistance: 10,
    showArea: true,
    lineSmooth: true
  };

  var responsiveOptions = [
    ['screen and (max-width: 640px)', {
      seriesBarDistance: 5,
      axisX: {
        labelInterpolationFnc: function (value) {
          return value[0];
        }
      }
    }]
  ];

  new Chartist.Bar('#chart-sales-monthly', data, options, responsiveOptions);

// Daily Sales

  var data = {
    labels: <?php echo json_encode(array_column($daily_sales, 'label')); ?>,
    series: <?php echo json_encode(array(array_column($daily_sales, 'average_sales'), array_column($daily_sales, 'total_sales'))); ?>
  };

  var options = {
    seriesBarDistance: 10
  };

  var responsiveOptions = [
    ['screen and (max-width: 640px)', {
      seriesBarDistance: 5,
      axisX: {
        labelInterpolationFnc: function (value) {
          return value[0];
        }
      }
    }]
  ];

  new Chartist.Bar('#chart-sales-daily', data, options, responsiveOptions);
</script>
<?php
    cache::end_capture($widget_graphs_cache_id);
  }
?>