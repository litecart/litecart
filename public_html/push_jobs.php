<?php
  define('SEO_REDIRECT', false);
  require_once('includes/app_header.inc.php');
  header('Content-type: text/plain; charset='. $system->language->selected['code']);
  if (strtotime($system->settings->get('jobs_last_run')) > strtotime('-'. $system->settings->get('jobs_interval') .' minutes')) die('Already did my duty!');
  
  file_put_contents('test.txt', '123');
  
  ignore_user_abort(true);
  set_time_limit(60*15);
  header('X-Robots-Tag: noindex');
  
  $system->database->query(
    "update ". DB_TABLE_SETTINGS ."
    set value = '". date('Y-m-d H:i:s') ."'
    where `key` = 'jobs_last_run'
    limit 1;"
  );
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'jobs.inc.php');
  $jobs = new jobs();
  
  echo $jobs->process();
?>