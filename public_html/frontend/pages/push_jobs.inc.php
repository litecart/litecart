<?php
  ignore_user_abort(true);
  set_time_limit(60*5);

  header('X-Robots-Tag: noindex');
  header('Content-type: text/plain; charset='. mb_http_output());

  $last_push = strtotime(settings::get('jobs_last_push'));
  if ($last_push > strtotime('-5 minutes')) die('I just recently did my duty at '. date('H:i:s', ) .'!');

  session::close();

  database::query(
    "update ". DB_TABLE_PREFIX ."settings
    set value = '". date('Y-m-d H:i:s') ."'
    where `key` = 'jobs_last_push'
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
