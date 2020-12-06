<?php
  header('X-Robots-Tag: noindex');
  header('Content-type: text/plain; charset='. language::$selected['code']);

  if (strtotime(settings::get('jobs_last_run')) > strtotime('-'. settings::get('jobs_interval') .' minutes')) die('Already did my duty!');

  session::close();

  if (preg_match('#^(off|false|0)$#i', ini_get('safe_mode'))) {
    ignore_user_abort(true);
    set_time_limit(60*5);
  }

  database::query(
    "update ". DB_TABLE_SETTINGS ."
    set value = '". date('Y-m-d H:i:s') ."'
    where `key` = 'jobs_last_run'
    limit 1;"
  );

  $jobs = new mod_jobs();

  if (!empty($_GET['module_id'])) {
    $jobs->process($_GET['module_id']);
  } else {
    $jobs->process();
  }

  echo 'OK';
  exit;
