<?php
  define('SEO_REDIRECT', false);
  require_once('includes/app_header.inc.php');
  header('Content-type: text/plain; charset='. $system->language->selected['code']);
  
  if (strtotime($system->settings->get('jobs_last_run')) > strtotime('-'. $system->settings->get('jobs_interval') .' minutes')) die('Already did my duty!');
  
  ignore_user_abort(true);
  set_time_limit(60*5);
  header('X-Robots-Tag: noindex');
  
  $system->database->query(
    "update ". DB_TABLE_SETTINGS ."
    set value = '". date('Y-m-d H:i:s') ."'
    where `key` = 'jobs_last_run'
    limit 1;"
  );
  
  $jobs = new jobs();
  
  if (!empty($_GET['module_id'])) {
    echo $jobs->process($_GET['module_id']);
  } else {
    echo $jobs->process();
  }
?>