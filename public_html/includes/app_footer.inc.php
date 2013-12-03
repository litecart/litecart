<?php
  
// Store the captured output buffer
  $system->document->snippets['content'] = ob_get_clean();
  
// Run after capture processes
  $system->run('after_capture');
  
// Prepare output
  $system->run('prepare_output');
  
// Capture template
  ob_start();
  require(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . $system->document->template .'/'. $system->document->layout .'.inc.php');
  $output = ob_get_clean();
  
// Stitch content
  $system->document->stitch($output);
  
// Run before output processes
  $system->run('before_output');
  
// Output page
  header('Content-Language: '. $system->language->selected['code']);
  echo $output;
  
// Run after processes
  $system->run('shutdown');
  
// Execute background jobs
  if (strtotime($system->settings->get('jobs_last_run')) < strtotime('-'. ($system->settings->get('jobs_interval')+1) .' minutes')) {
    
    //error_log('Jobs executed manually because last run was '. $system->settings->get('jobs_last_run').'. Is the cron job set up?');
    
    $url = $system->document->link(WS_DIR_HTTP_HOME . 'push_jobs.php');
    
    if (!in_array('exec', explode(',', str_replace(' ', '', ini_get('disable_functions'))))) {
      exec('wget -q -O - '. $url .' > /dev/null 2>&1 &');
    } else {
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