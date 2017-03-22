<?php

// Store the captured output buffer
  $content = ob_get_clean();

// Run after capture processes
  system::run('after_capture');

// Stitch content
  $_page = new view();
  $_page->snippets = array('content' => $content);
  $output = $_page->stitch('layouts/'.document::$layout);

// Prepare output
  system::run('prepare_output');

// Stitch global snippets
  $_page->snippets = document::$snippets;
  $_page->html = $output;
  $output = $_page->stitch();

// Run before output processes
  system::run('before_output');

// Output page
  header('Content-Language: '. language::$selected['code']);
  echo $output;

// Run after processes
  system::run('shutdown');

// Execute background jobs
  if (strtotime(settings::get('jobs_last_push')) < strtotime('-'. (settings::get('jobs_interval')+1) .' minutes')) {
    if (strtotime(settings::get('jobs_last_run')) < strtotime('-'. (settings::get('jobs_interval')+1) .' minutes')) {

      // To avoid this push method, set up a cron job calling www.yoursite.com/index.php/push_jobs

      database::query(
        "update ". DB_TABLE_SETTINGS ."
        set `value` = '". database::input(date('Y-m-d H:i:s')) ."'
        where `key` = 'jobs_last_push'
        limit 1;"
      );

      $url = document::ilink('push_jobs');
      $disabled_functions = explode(',', str_replace(' ', '', ini_get('disable_functions')));

      if (!in_array('exec', $disabled_functions)) {
        exec('wget -q -O - '. $url .' > /dev/null 2>&1 &');
      } else if (!in_array('fsockopen', $disabled_functions)) {
        $parts = parse_url($url);
        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
        $out = "GET ". $parts['path'] ." HTTP/1.1\r\n"
             . "Host: ". $parts['host'] ."\r\n"
             . "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        fclose($fp);
      }
    }
  }
