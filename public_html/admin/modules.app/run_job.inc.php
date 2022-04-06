<?php

  set_time_limit(300);

  document::$snippets['title'][] = language::translate('title_run_job', 'Run Job') .' '. functions::escape_html($_GET['module_id']);

  breadcrumbs::add(language::translate('title_modules', 'Modules'), document::link(WS_DIR_ADMIN, ['doc' => 'modules'], ['app']));
  breadcrumbs::add(language::translate('title_job_modules', 'Job Modules'), document::link(WS_DIR_ADMIN, ['doc' => 'jobs'], ['app']));
  breadcrumbs::add(language::translate('title_run_job', 'Run Job') .' '. functions::escape_html($_GET['module_id']));

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
