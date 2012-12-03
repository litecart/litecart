<?php
  
  $order_statuses = array();
  $orders_status_query = $system->database->query(
    "select id from ". DB_TABLE_ORDERS_STATUS ." where is_sale;"
  );
  while ($order_status = $system->database->fetch($orders_status_query)) {
    $order_statuses[] = (int)$order_status['id'];
  }
  
  $sales = array();
  for ($timestamp = strtotime('-30 days'); date('Y-m-d', $timestamp) <= date('Y-m-d'); $timestamp = strtotime('+1 day', $timestamp)) {
    
    $orders_query = $system->database->query(
      "select sum(payment_due) as total_sales from ". DB_TABLE_ORDERS ."
      where order_status_id in ('". implode("', '", $order_statuses) ."')
      and date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $timestamp), date('d', $timestamp), date('Y', $timestamp))) ."'
      and date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $timestamp), date('d', $timestamp), date('Y', $timestamp))) ."';"
    );
    $orders = $system->database->fetch($orders_query);
    $sales[date('d', $timestamp)] = '[\''. date('d-M-Y', $timestamp) .'\', '. (int)$orders['total_sales'] .']';
  }
  
  $system->document->snippets['head_tags']['jqplot'] = '<script type="text/javascript" src="'. WS_DIR_EXT .'/jqplot/jquery.jqplot.min.js"></script>' . PHP_EOL
                                                     . '<script type="text/javascript" src="'. WS_DIR_EXT .'/jqplot/plugins/jqplot.highlighter.min.js"></script>' . PHP_EOL
                                                     . '<script type="text/javascript" src="'. WS_DIR_EXT .'/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>' . PHP_EOL
                                                     . '<link rel="stylesheet" type="text/css" href="'. WS_DIR_EXT .'/jqplot/jquery.jqplot.min.css" />';
?>
<div class="widget">
  <div id="chart1" style="height: 150px;"></div>
  <script class="code" type="text/javascript">
    var line1 = [<?php echo implode(',', $sales); ?>];
    var plot1 = $.jqplot('chart1', [line1], {
        title: '<?php echo $system->language->translate('title_sales', 'Sales'); ?> (<?php echo sprintf($system->language->translate('title_s_days', '%s days'), '30'); ?>)',
        seriesDefaults: {
          lineWidth: 1
        },
        axes: {
          xaxis: {
            renderer: $.jqplot.DateAxisRenderer,
            tickOptions: {
              formatString: '%b&nbsp;%#d'
            },
            pad: 0
          },
          yaxis:{
            tickOptions: {
              formatString: '<?php echo $system->currency->currencies[$system->settings->get('store_currency_code')]['prefix']; ?>%.2f<?php echo $system->currency->currencies[$system->settings->get('store_currency_code')]['suffix']; ?>'
            },
            pad: 0
          }
        },
        highlighter: {
          show: true
        },
        cursor: {
          show: false
        }
    });
  </script>
</div>