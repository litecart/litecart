<?php
  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.barRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.BezierCurveRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.blockRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.bubbleRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.canvasAxisLabelRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.canvasAxisTickRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.canvasOverlay.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.canvasTextRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.categoryAxisRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.ciParser.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.cursor.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.dateAxisRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.donutRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.dragable.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.enhancedLegendRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.funnelRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.highlighter.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.json2.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.logAxisRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.mekkoAxisRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.mekkoRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.meterGaugeRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.mobile.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.ohlcRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.pieRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.pointLabels.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.pyramidAxisRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.pyramidGridRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.pyramidRenderer.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/plugins/jqplot.trendline.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/excanvas.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/jquery.jqplot.css',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/jquery.jqplot.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/jquery.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'jqplot/jquery.min.js',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

?>