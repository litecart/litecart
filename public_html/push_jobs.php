<?php
  require_once('includes/app_header.inc.php');
  header('X-Robots-Tag: noindex');
  header('Content-type: text/plain; charset='. $system->language->selected['code']);
  //if (strtotime($system->settings->get('jobs_last_run')) > strtotime('-'. $system->settings->get('jobs_interval') .' minutes')) die('Already did my duty!');
  
  $system->database->query(
    "update ". DB_TABLE_SETTINGS ."
    set value = '". date('Y-m-d H:i:s') ."'
    where `key` = 'jobs_last_run'
    limit 1;"
  );
  
  ignore_user_abort(true);
  set_time_limit(0);

  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'jobs.inc.php');
  $jobs = new jobs();
  
  echo $jobs->process();
?>