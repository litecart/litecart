<?php

  $deleted_files = array(
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.BezierCurveRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.blockRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.bubbleRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.canvasOverlay.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.canvasTextRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.ciParser.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.cursor.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.dateAxisRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.donutRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.dragable.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.enhancedLegendRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.funnelRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.json2.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.logAxisRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.mekkoAxisRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.mekkoRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.meterGaugeRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.mobile.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.ohlcRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.pieRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.pointLabels.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.pyramidAxisRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.pyramidGridRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.pyramidRenderer.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/plugins/jqplot.trendline.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/changes.txt',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/gpl-2.0.txt',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/jqPlotCssStyling.txt',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/jqPlotOptions.txt',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jquery/jquery.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/jquery.min.js',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/optionsTutorial.txt',
    FS_DIR_HTTP_ROOT . WS_DIR_EXT .'jqplot/usage.txt',
  );

  foreach ($deleted_files as $pattern) {
    if (!file_delete($pattern)) {
      die('<span class="error">[Error]</span></p>');
    }
  }

?>