<?php

  document::$snippets['head_tags']['chartist'] = '<link rel="stylesheet" href="'. document::href_rlink('app://assets/chartist/chartist.min.css') .'" />';
  document::$snippets['foot_tags']['chartist'] = '<script src="'. document::href_rlink('app://assets/chartist/chartist.min.js') .'"></script>';

  $widget_graphs_cache_token = cache::token('widget_graphs', ['site'], 'memory', 300);
  if (cache::capture($widget_graphs_cache_token)) {

  // Monthly Sales

    $orders_query = database::query(
      "select sum(total - total_tax) as total_sales, date_format(date_created, '%Y') as year, date_format(date_created, '%m') as month
      from ". DB_TABLE_PREFIX ."orders
      where order_status_id in (
        select id from ". DB_TABLE_PREFIX ."order_statuses
        where is_sale
      )
      and date_created between '". date('Y-m-01 00:00:00', strtotime('-36 months')) ."' and '". date('Y-m-t 23:59:59') ."'
      group by year, month
      order by year, month asc;"
    );

    $monthly_sales = [];
    while ($orders = database::fetch($orders_query)) {
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

    ksort($monthly_sales);
    foreach (array_keys($monthly_sales) as $year) {
      ksort($monthly_sales[$year]);
    }

  // Daily Sales
    switch (true) {

     // Western Week
      case (class_exists('IntlCalendar', false) && IntlCalendar::createInstance(null, language::$selected['locale'])->getFirstDayOfWeek() == IntlCalendar::DOW_SUNDAY):
        $daily_sales = [7 => [], 1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => []];
        break;

    // Middle-Eastern Week
      case (class_exists('IntlCalendar', false) && IntlCalendar::createInstance(null, language::$selected['locale'])->getFirstDayOfWeek() == IntlCalendar::DOW_SATURDAY):
        $daily_sales = [6 => [], 7 => [], 1 => [], 2 => [], 3 => [], 4 => [], 5 => []];
        break;

    // ISO-8601 Week
      default:
        $daily_sales = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []];
        break;
    }

    $orders_query = database::query(
      "select round(sum(total - total_tax) / count(distinct(date(date_created))), 2) as total_sales, total_tax as total_tax, weekday(date_created)+1 as weekday
      from ". DB_TABLE_PREFIX ."orders
      where order_status_id in (
        select id from ". DB_TABLE_PREFIX ."order_statuses
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
      "select round(sum(total - total_tax) / count(distinct(date(date_created))), 2) as average_sales, total_tax as total_tax, weekday(date_created)+1 as weekday, group_concat(total - total_tax)
      from ". DB_TABLE_PREFIX ."orders
      where order_status_id in (
        select id from ". DB_TABLE_PREFIX ."order_statuses
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
?>
<style>
#chart-sales-monthly {
  --chart-label-color: #999;
  --chart-a-color: #ececec;
  --chart-b-color: #d2d2d2;
  --chart-c-color: #3ba5c6;
}
.dark-mode #chart-sales-monthly {
  --chart-label-color: #999;
  --chart-a-color: #343d5a;
  --chart-b-color: #424d6e;
  --chart-c-color: #4e90ad;
}

#chart-sales-daily {
  --chart-label-color: #999;
  --chart-a-color: #e4e4e4;
  --chart-b-color: #3ba5c6;
}
.dark-mode #chart-sales-daily {
  --chart-label-color: #999;
  --chart-a-color: #424d6e;
  --chart-b-color: #4e90ad;
}

#chart-sales-monthly .ct-label, #chart-sales-daily .ct-label {
  font-size: 12px;
  color: var(--chart-label-color);
}
#chart-sales-monthly .ct-series-a .ct-bar, #chart-sales-daily .ct-series-a .ct-bar {
  stroke: var(--chart-a-color);
}
#chart-sales-monthly .ct-series-b .ct-bar, #chart-sales-daily .ct-series-b .ct-bar {
  stroke: var(--chart-b-color);
}
#chart-sales-monthly .ct-series-c .ct-bar, #chart-sales-daily .ct-series-c .ct-bar {
  stroke: var(--chart-c-color);
}
#chart-sales-monthly .ct-bar{
  stroke-width: 20px;
}

#chart-sales-daily .ct-series-a .ct-bar {
  stroke: var(--chart-a-color);
}
#chart-sales-daily .ct-series-b .ct-bar {
  stroke: var(--chart-b-color);
}
#chart-sales-daily .ct-bar {
  stroke-width: 10px;
}
</style>

<div id="widget-graphs" class="widget">
  <div class="row">
    <div class="col-md-8">
      <div class="card card-default">
        <div class="card-header">
          <div class="card-title"><?php echo language::translate('title_monthly_sales', 'Monthly Sales'); ?></div>
      </div>
    </div>

        <div class="card-body">
      <div id="chart-sales-monthly" style="width: 100%; height: 250px;" title="<?php echo language::translate('title_monthly_sales', 'Monthly Sales'); ?>"></div>
    </div>
  </div>

    <div class="widget col-md-4">
      <div class="card card-default">
        <div class="card-header">
          <div class="card-title"><?php echo language::translate('title_daily_sales', 'Daily Sales'); ?></div>
      </div>
    </div>

        <div class="card-body">
      <div id="chart-sales-daily" style="width: 100%; height: 250px" title="<?php echo language::translate('title_daily_sales', 'Daily Sales'); ?>"></div>
    </div>
  </div>
</div>

<script>
// Monthly Sales
  var data = {
    labels: <?php echo json_encode(array_column($monthly_sales[date('Y')], 'label'), JSON_UNESCAPED_SLASHES); ?>,
    series: <?php echo json_encode([array_column($monthly_sales[date('Y')-2], 'total_sales'), array_column($monthly_sales[date('Y')-1], 'total_sales'), array_column($monthly_sales[date('Y')], 'total_sales')], JSON_UNESCAPED_SLASHES); ?>
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

  let chart1 = new Chartist.Bar('#chart-sales-monthly', data, options, responsiveOptions);

  // Offset x1 a tiny amount so that the straight stroke gets a bounding box
  // Straight lines don't get a bounding box
  // Last remark on -> http://www.w3.org/TR/SVG11/coords.html#ObjectBoundingBox
  chart1.on('draw', function(ctx) {
    if(ctx.type === 'bar') {
      ctx.element.attr({
        x1: ctx.x1 + 0.001
      });
    }
  });

  // Create the gradient definition on created event (always after chart re-render)
  chart1.on('created', function(ctx) {
    let defs = ctx.svg.elem('defs');
    defs.elem('linearGradient', { id: 'gradient', x1: 0, y1: 1, x2: 0, y2: 0 })
    .elem('stop', { offset: 0, 'stop-color': 'hsla(278, 100%, 42%, .8)' })
    .parent().elem('stop', { offset: 1, 'stop-color': 'hsla(204, 100%, 50%, .8)' });
  });

// Daily Sales

  var data = {
    labels: <?php echo json_encode(array_column($daily_sales, 'label'), JSON_UNESCAPED_SLASHES); ?>,
    series: <?php echo json_encode([array_column($daily_sales, 'average_sales'), array_column($daily_sales, 'total_sales')], JSON_UNESCAPED_SLASHES); ?>
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

  let chart2 = new Chartist.Bar('#chart-sales-daily', data, options, responsiveOptions);

  // Offset x1 a tiny amount so that the straight stroke gets a bounding box
  // Straight lines don't get a bounding box
  // Last remark on -> http://www.w3.org/TR/SVG11/coords.html#ObjectBoundingBox
  chart2.on('draw', function(ctx) {
    if(ctx.type === 'bar') {
      ctx.element.attr({
        x1: ctx.x1 + 0.001
      });
    }
  });

  // Create the gradient definition on created event (always after chart re-render)
  chart2.on('created', function(ctx) {
    let defs = ctx.svg.elem('defs');
    defs.elem('linearGradient', { id: 'gradient', x1: 0, y1: 1, x2: 0, y2: 0 })
    .elem('stop', { offset: 0, 'stop-color': 'hsla(278, 100%, 42%, .8)' })
    .parent().elem('stop', { offset: 1, 'stop-color': 'hsla(204, 100%, 50%, .8)' });
  });

</script>
<?php
    cache::end_capture($widget_graphs_cache_token);
  }
?>