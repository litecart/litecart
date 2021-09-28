<?php

  return $app_config = [
    'name' => language::translate('title_vqmods', 'vQmods'),
    'default' => 'vqmods',
    'priority' => 0,
    'theme' => [
      'color' => '#77d2cd',
      'icon' => 'fa-plug',
    ],
    'menu' => [],
    'docs' => [
      'view' => 'view.inc.php',
      'download' => 'download.inc.php',
      'vqmods' => 'vqmods.inc.php',
      'test' => 'test.inc.php',
    ],
  ];
