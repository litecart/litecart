<?php

  user::require_login();

  document::$layout = 'ajax';

  $app_themes = array_column(functions::admin_get_apps(), 'theme', 'code');

  $search_results = [];

  try {

    if (empty($_GET['query'])) throw new Exception('Nothing to search for');

    $search_results = functions::admin_search_apps($_GET['query']);

  } catch(Exception $e) {
    // Do nothing
  }

  echo json_encode($search_results, JSON_UNESCAPED_SLASHES);
  exit;
