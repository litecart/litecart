<?php

  try {

    if (empty($_GET['pattern'])) {
      throw new Exception('Missing file');
    }

    $results = [];

    $skip_list = [
      '#.*(?<!\.inc\.php)$#',
      '#^assets/#',
      '#^includes/app_header.inc.php$#',
      '#^includes/library/nod_vmod.inc.php$#',
      '#^(cache|data|ext|images|install|logs|vmods|vqmods)/#',
    ];

    $_GET['pattern'] = preg_replace(array_keys(vmod::$aliases), array_values(vmod::$aliases), $_GET['pattern']);

    $files = functions::file_search(FS_DIR_APP . $_GET['pattern'], GLOB_BRACE);

    foreach ($files as $file) {
      $relative_path = functions::file_relative_path($file);

      foreach ($skip_list as $pattern) {
        if (preg_match($pattern, $relative_path)) continue 2;
      }

      $results[$relative_path] = file_get_contents($file);
    }

  } catch (Exception $e) {
    $results = [];
  }

  header('Content-Type: application/json');
  echo json_encode($results, JSON_UNESCAPED_SLASHES);
  exit;
