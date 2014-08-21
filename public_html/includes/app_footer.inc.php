<?php
  
// Store the captured output buffer
  $content = ob_get_clean();
  
// Run after capture processes
  system::run('after_capture');
  
// Stitch content
  $page = new view();
  $page->snippets = array('content' => $content);
  $output = $page->stitch(document::$layout);
  
// Prepare output
  system::run('prepare_output');
  $page->snippets = document::$snippets;
  $page->html = $output;
  $output = $page->stitch();
  
// Run before output processes
  system::run('before_output');
  
// Output page
  header('Content-Language: '. language::$selected['code']);
  echo $output;
  
// Run after processes
  system::run('shutdown');
  exit;
  
// Execute background jobs
  if (strtotime(settings::get('jobs_last_run')) < strtotime('-'. (settings::get('jobs_interval')+1) .' minutes')) {
    
    //error_log('Jobs executed manually because last run was '. settings::get('jobs_last_run').'. Is the cron job set up?');
    
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
  
?>