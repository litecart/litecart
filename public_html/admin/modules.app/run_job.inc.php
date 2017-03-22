<?php
  set_time_limit(60*5);
  ob_end_clean();

  $jobs = new mod_jobs();

  echo '<pre>';
  if (!empty($_GET['module_id'])) {
    echo $jobs->process($_GET['module_id'], true);
  } else {
    echo $jobs->process(null, true);
  }
  echo '</pre>';
