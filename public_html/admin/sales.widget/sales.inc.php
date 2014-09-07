<?php

  document::$snippets['head_tags']['jqplot'] = '<script src="'. WS_DIR_EXT .'jqplot/jquery.jqplot.min.js"></script>' . PHP_EOL
                                             . '<script src="'. WS_DIR_EXT .'jqplot/plugins/jqplot.highlighter.min.js"></script>' . PHP_EOL
                                             . '<script src="'. WS_DIR_EXT .'jqplot/plugins/jqplot.barRenderer.min.js"></script>' . PHP_EOL
                                             . '<script src="'. WS_DIR_EXT .'jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>' . PHP_EOL
                                             . '<link rel="stylesheet" href="'. WS_DIR_EXT .'jqplot/jquery.jqplot.min.css" />';
  
  $order_statuses = array();
  $orders_status_query = database::query(
    "select id from ". DB_TABLE_ORDER_STATUSES ." where is_sale;"
  );
  while ($order_status = database::fetch($orders_status_query)) {
    $order_statuses[] = (int)$order_status['id'];
  }
  
?>
<div class="widget">
<?php
  $monthly_sales = array();
  $monthly_tax = array();
  for ($timestamp = strtotime('-1 years'); date('Y-m', $timestamp) <= date('Y-m'); $timestamp = strtotime('+1 month', $timestamp)) {
    
    $orders_query = database::query(
      "select sum(payment_due - tax_total) as total_sales, tax_total as total_tax from ". DB_TABLE_ORDERS ."
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $timestamp), 1, date('Y', $timestamp))) ."'
      and date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $timestamp), date('t', $timestamp), date('Y', $timestamp))) ."';"
    );
    $orders = database::fetch($orders_query);
    
    $monthly_sales[date('Y-m', $timestamp)] = '[\''. strftime('%b', $timestamp) .'\', '. (int)$orders['total_sales'] .']';
    $monthly_tax[date('Y-m', $timestamp)] = '[\''. strftime('%b', $timestamp) .'\', '. (int)$orders['total_tax'] .']';
  }
?>
  <div id="chart-sales-monthly" style="float: left; width: 50%; height: 150px;"></div>
  <script>
    var bar1 = [<?php echo implode(',', $monthly_sales); ?>];
    var bar2 = [<?php echo implode(',', $monthly_tax); ?>];
    var plot1 = $.jqplot('chart-sales-monthly', [bar1], {
      title: '<?php echo language::translate('title_sales', 'Sales'); ?> (<?php echo sprintf(language::translate('title_s_months', '%s months'), '12'); ?>)',
      grid:{
        borderColor: 'transparent',
        shadow: false,
        drawBorder: false,
        shadowColor: 'transparent'
      },
      seriesDefaults: {
        renderer: $.jqplot.BarRenderer,
        rendererOptions: {barWidth: 30},
        shadow: false
      },
      series:[
        {label: "<?php echo language::translate('title_sales', 'Sales'); ?>"},
        {label: "<?php echo language::translate('title_tax', 'Tax'); ?>"}
      ],
      axesDefaults: {
        tickOptions: {
          fontSize: '8pt',
        }
      },
      axes: {
        xaxis: {
          renderer: $.jqplot.CategoryAxisRenderer
        },
        yaxis:{
          tickOptions: {
            formatString: '<?php echo currency::$currencies[settings::get('store_currency_code')]['prefix']; ?>%.2f<?php echo currency::$currencies[settings::get('store_currency_code')]['suffix']; ?>'
          }
        }
      },
      legend: {
        show: false,
        placement: 'insideGrid'
      },
      highlighter: {
        show: true
      }
    });
  </script>

<?php
  $daily_sales = array();
  $daily_tax = array();
  for ($timestamp = strtotime('-30 days'); date('Y-m-d', $timestamp) <= date('Y-m-d'); $timestamp = strtotime('+1 day', $timestamp)) {
    
    $orders_query = database::query(
      "select sum(payment_due - tax_total) as total_sales, tax_total as total_tax from ". DB_TABLE_ORDERS ."
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $timestamp), date('d', $timestamp), date('Y', $timestamp))) ."'
      and date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $timestamp), date('d', $timestamp), date('Y', $timestamp))) ."';"
    );
    $orders = database::fetch($orders_query);
    
    $daily_sales[date('d', $timestamp)] = '[\''. date('j', $timestamp) .'\', '. (int)$orders['total_sales'] .']';
    $daily_tax[date('d', $timestamp)] = '[\''. date('j', $timestamp) .'\', '. (int)$orders['total_tax'] .']';
  }
  
?>
  <div id="chart-sales-daily" style="float: right; width: 50%; height: 150px;"></div>
  <script>
    var bar1 = [<?php echo implode(',', $daily_sales); ?>];
    var bar2 = [<?php echo implode(',', $daily_tax); ?>];
    var plot1 = $.jqplot('chart-sales-daily', [bar1], {
      title: '<?php echo language::translate('title_sales', 'Sales'); ?> (<?php echo sprintf(language::translate('title_s_days', '%s days'), '30'); ?>)',
      grid:{
        borderColor: 'transparent',
        shadow: false,
        drawBorder: false,
        shadowColor: 'transparent'
      },
      seriesDefaults: {
        renderer: $.jqplot.BarRenderer,
        rendererOptions: {barWidth: 10},
        shadow: false
      },
      series:[
        {label: "<?php echo language::translate('title_sales', 'Sales'); ?>"},
        {label: "<?php echo language::translate('title_tax', 'Tax'); ?>"}
      ],
      axesDefaults: {
        tickOptions: {
          fontSize: '8pt',
        }
      },
      axes: {
        xaxis: {
          renderer: $.jqplot.CategoryAxisRenderer
        },
        yaxis:{
          tickOptions: {
            formatString: '<?php echo currency::$currencies[settings::get('store_currency_code')]['prefix']; ?>%.2f<?php echo currency::$currencies[settings::get('store_currency_code')]['suffix']; ?>'
          }
        }
      },
      legend: {
        show: false,
        placement: 'insideGrid'
      },
      highlighter: {
        show: true
      }
    });
  </script>
  <div style="clear: both;"></div>
</div>