<?php

  if (preg_match('#^(off|false|0)$#i', ini_get('safe_mode'))) {
    set_time_limit(60*5);
  }

  ob_clean();

  session::close();

  $jobs = new mod_jobs();

  echo '<pre>';
  if (!empty($_GET['module_id'])) {
    echo $jobs->process($_GET['module_id'], true);
  } else {
    echo $jobs->process(null, true);
  }
  echo '</pre>';
