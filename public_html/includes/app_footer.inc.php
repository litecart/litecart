<?php

// Store the captured output buffer
  $content = ob_get_contents();
  ob_clean();

// Run after capture processes
  event::fire('after_capture');

// Stitch content with layout
  $_page = new ent_view(FS_DIR_TEMPLATE . 'layouts/'.document::$layout.'.inc.php');
  $_page->snippets = [
    'important_notice' => settings::get('important_notice'),
    'content' => $content,
  ];
  $output = (string)$_page;

// Run prepare output processes
  event::fire('prepare_output');

// Output page
  $_page = new ent_view();
  $_page->html = $output;
  $_page->snippets = &document::$snippets;
  $_page->cleanup = true;
  $GLOBALS['output'] = (string)$_page;

// Run before output processes
  event::fire('before_output');

// Output Compression
  if (filter_var(settings::get('gzip_enabled'), FILTER_VALIDATE_BOOLEAN)) {
    ini_set('zlib.output_compression', 1);
  } else {
    ini_set('zlib.output_compression', 0);
  }

// Output page
  echo $GLOBALS['output'];

// Run after processes
  event::fire('shutdown');

// Execute background jobs
  if (date('Ymdh', strtotime(settings::get('jobs_last_run'))) != date('Ymdh')) {
    if (strtotime(settings::get('jobs_last_push')) < strtotime('-5 minutes')) {

      // To avoid this push method, set up a cron job calling https://www.yoursite.com/index.php/push_jobs

      database::query(
        "update ". DB_TABLE_PREFIX ."settings
        set `value` = '". date('Y-m-d H:i:s') ."'
        where `key` = 'jobs_last_push'
        limit 1;"
      );

      $url = document::ilink('f:push_jobs');
      $disabled_functions = preg_split('#\s*,\s*#', ini_get('disable_functions'), -1, PREG_SPLIT_NO_EMPTY);

      if (!in_array('exec', $disabled_functions)) {
        exec('wget -q -O - '. $url .' > /dev/null 2>&1 &');
      } else if (!in_array('fsockopen', $disabled_functions)) {
        $parts = parse_url($url);
        $fp = fsockopen($parts['host'], fallback($parts['port'], 80), $errno, $errstr, 30);
        $out = "GET ". $parts['path'] ." HTTP/1.1\r\n"
             . "Host: ". $parts['host'] ."\r\n"
             . "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        fclose($fp);
      }
    }
  }
