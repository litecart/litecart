<?php
  
// Store the captured output buffer
  $system->document->snippets['content'] = ob_get_clean();
  
// Initiate library objects
  foreach ($system->get_loaded_modules() as $module) {
    if (method_exists($system->$module, 'after_capture')) $system->$module->after_capture();
  }
  
// Prepare output
  foreach ($system->get_loaded_modules() as $module) {
    if (method_exists($system->$module, 'prepare_output')) $system->$module->prepare_output();
  }
  
// Capture template
  ob_start();
  require(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATES . $system->document->template .'/'. $system->document->layout .'.'. $system->document->viewport .'.inc.php');
  $output = ob_get_clean();

// Stitch content
  foreach ($system->document->snippets as $key => $content) {
    if (is_array($content)) $content = implode(PHP_EOL, $content);
    $output = str_replace(array('{snippet:'. $key .'}', '<!--snippet:'. $key .'-->'), $content, $output);
  }
  $output = preg_replace('/{snippet:.*?}/', '', $output);
  $output = preg_replace('/<!--snippet:.*?-->/', '', $output);
  
// Run after processes for library objects
  foreach ($system->get_loaded_modules() as $module) {
    if (method_exists($system->$module, 'before_output')) $system->$module->before_output();
  }
  
// Output page
  header('Content-Language: '. $system->language->selected['code']);
  echo $output;
  
// Run after processes for library objects
  foreach ($system->get_loaded_modules() as $module) {
    if (method_exists($system->$module, 'shutdown')) $system->$module->shutdown();
  }
  
// Execute background jobs
  if (strtotime($system->settings->get('jobs_last_run')) < strtotime('-'. $system->settings->get('jobs_interval') .' minutes')) {
  
    //error_log('Jobs executed manually because last run was '. $system->settings->get('jobs_last_run').'.');
  
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