<?php

  document::$snippets['head_tags']['chartist'] = '<link rel="stylesheet" href="'. WS_DIR_APP .'ext/chartist/chartist.min.css" />';
  document::$snippets['foot_tags']['chartist'] = '<script src="'. WS_DIR_APP .'ext/chartist/chartist.min.js"></script>';

  $widget_graphs_cache_token = cache::token('widget_graphs', array('site'), 'file', 300);
  if (cache::capture($widget_graphs_cache_token)) {

  // Monthly Sales

    $orders_query = database::query(
      "select sum(payment_due - tax_total) as total_sales, date_format(date_created, '%Y') as year, date_format(date_created, '%m') as month
      from ". DB_TABLE_ORDERS ."
      where order_status_id in (
        select id from ". DB_TABLE_ORDER_STATUSES ."
        where is_sale
      )
      and date_created between '". date('Y-m-01 00:00:00', strtotime('-36 months')) ."' and '". date('Y-m-t 23:59:59') ."'
      group by year, month
      order by year, month asc;"
    );

    $monthly_sales = array();
    while ($orders = database::fetch($orders_query)) {
      settype($orders['total_sales'], 'float');
      $monthly_sales[$orders['year']][$orders['month']] = $orders;
    }

    for ($timestamp = date('Y', strtotime('-36 months')); $timestamp < strtotime('Dec 31'); $timestamp = strtotime('+1 months', $timestamp)) {
      $year = date('Y', $timestamp);
      $month = date('m', $timestamp);
      $monthly_sales[$year][$month]['year'] = $year;
      $monthly_sales[$year][$month]['month'] = $month;
      $monthly_sales[$year][$month]['label'] = language::strftime('%b', $timestamp);
      if (!isset($monthly_sales[$year][$month]['total_sales'])) $monthly_sales[$year][$month]['total_sales'] = 0;
    }

    $monthly_sales[date('Y')][date('m')]['label'] = '\u2605'.$monthly_sales[date('Y')][date('m')]['label'];

    ksort($monthly_sales);
    foreach (array_keys($monthly_sales) as $year) {
      ksort($monthly_sales[$year]);
    }

  // Daily Sales

    switch (true) {

     // Western Week
      case (extension_loaded('intl') && class_exists('IntlCalendar', false) && IntlCalendar::createInstance()->getFirstDayOfWeek() == 1):
        $daily_sales = array(7 => array(), 1 => array(), 2 => array(), 3 => array(), 4 => array(), 5 => array(), 6 => array());
        break;

    // Middle-Eastern Week
      case (extension_loaded('intl') && class_exists('IntlCalendar', false) && IntlCalendar::createInstance()->getFirstDayOfWeek() == 2):
        $daily_sales = array(6 => array(), 7 => array(), 1 => array(), 2 => array(), 3 => array(), 4 => array(), 5 => array());
        break;

    // ISO-8601 Week
      default:
        $daily_sales = array(1 => array(), 2 => array(), 3 => array(), 4 => array(), 5 => array(), 6 => array(), 7 => array());
        break;
    }

    $orders_query = database::query(
      "select round(sum(payment_due - tax_total) / count(distinct(date(date_created))), 2) as total_sales, tax_total as total_tax, weekday(date_created)+1 as weekday
      from ". DB_TABLE_ORDERS ."
      where order_status_id in (
        select id from ". DB_TABLE_ORDER_STATUSES ."
        where is_sale
      )
      and (date_created >= '". date('Y-m-d 00:00:00', strtotime('Monday this week')) ."')
      group by weekday
      order by weekday asc;"
    );

    while ($orders = database::fetch($orders_query)) {
      $daily_sales[$orders['weekday']]['total_sales'] = (int)$orders['total_sales'];
    }

    $orders_query = database::query(
      "select round(sum(payment_due - tax_total) / count(distinct(date(date_created))), 2) as average_sales, tax_total as total_tax, weekday(date_created)+1 as weekday, group_concat(payment_due - tax_total)
      from ". DB_TABLE_ORDERS ."
      where order_status_id in (
        select id from ". DB_TABLE_ORDER_STATUSES ."
        where is_sale
      )
      and (date_created > '". date('Y-m-d H:i:s', strtotime('-3 months', strtotime('Monday this week'))) ."' and date_created < '". date('Y-m-d 00:00:00', strtotime('Monday this week')) ."')
      group by weekday
      order by weekday asc;"
    );

    while ($orders = database::fetch($orders_query)) {
      $daily_sales[$orders['weekday']]['average_sales'] = (int)$orders['average_sales'];
    }

    for ($timestamp=time(); strtotime('-7 days') < $timestamp; $timestamp = strtotime('-1 day', $timestamp)) {
      $daily_sales[date('N', $timestamp)]['label'] = language::strftime('%a', $timestamp);
      if (!isset($daily_sales[date('N', $timestamp)]['total_sales'])) $daily_sales[date('N', $timestamp)]['total_sales'] = 0;
      if (!isset($daily_sales[date('N', $timestamp)]['average_sales'])) $daily_sales[date('N', $timestamp)]['average_sales'] = 0;
    }

    $daily_sales[date('N')]['label'] = '\u2605'.$daily_sales[date('N')]['label'];
?>
<style>
#chart-sales-monthly .ct-label, #chart-sales-daily .ct-label {
  font-size: 12px;
  color: #999;
}
#chart-sales-monthly .ct-series-a .ct-bar, #chart-sales-daily .ct-series-a .ct-bar {
  stroke: #ececec;
}
#chart-sales-monthly .ct-series-b .ct-bar, #chart-sales-daily .ct-series-b .ct-bar {
  stroke: #d2d2d2;
}
#chart-sales-monthly .ct-series-c .ct-bar, #chart-sales-daily .ct-series-c .ct-bar {
  stroke: #3ba5c6;
}
#chart-sales-monthly .ct-bar{
  stroke-width: 20px;
}

#chart-sales-daily .ct-series-a .ct-bar {
  stroke: #e4e4e4;
}
#chart-sales-daily .ct-series-b .ct-bar {
  stroke: #3ba5c6;
}
#chart-sales-daily .ct-bar {
  stroke-width: 10px;
}
</style>

<div id="widget-graphs" class="widget">
  <div class="row" style="margin-bottom: 0;">
    <div class="col-md-8">
      <div class="panel panel-default">
        <div class="panel-heading">
          <div class="panel-title"><?php echo language::translate('title_monthly_sales', 'Monthly Sales'); ?></div>
        </div>

        <div class="panel-body">
          <div id="chart-sales-monthly" style="width: 100%; height: 250px;" title="<?php echo language::translate('title_monthly_sales', 'Monthly Sales'); ?>"></div>
        </div>
      </div>
    </div>

    <div class="widget col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading">
          <div class="panel-title"><?php echo language::translate('title_daily_sales', 'Daily Sales'); ?></div>
        </div>

        <div class="panel-body">
          <div id="chart-sales-daily" style="width: 100%; height: 250px" title="<?php echo language::translate('title_daily_sales', 'Daily Sales'); ?>"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Monthly Sales
  var data = {
    labels: <?php echo json_encode(array_column($monthly_sales[date('Y')], 'label'), JSON_UNESCAPED_SLASHES); ?>,
    series: <?php echo json_encode(array(array_column($monthly_sales[date('Y')-2], 'total_sales'), array_column($monthly_sales[date('Y')-1], 'total_sales'), array_column($monthly_sales[date('Y')], 'total_sales')), JSON_UNESCAPED_SLASHES); ?>
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
    labels: <?php echo json_encode(array_column($daily_sales, 'label'), JSON_UNESCAPED_SLASHES); ?>,
    series: <?php echo json_encode(array(array_column($daily_sales, 'average_sales'), array_column($daily_sales, 'total_sales')), JSON_UNESCAPED_SLASHES); ?>
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
    cache::end_capture($widget_graphs_cache_token);
  }
?>