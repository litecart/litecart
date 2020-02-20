<?php

  return $app_config = [
    'name' => language::translate('title_vMods', 'vMods'),
    'default' => 'vmods',
    'priority' => 0,
    'theme' => [
      'color' => '#77d2cd',
      'icon' => 'fa-plug',
    ],
    'menu' => [],
    'docs' => [
      'edit_vmod' => 'edit_vmod.inc.php',
      'download' => 'download.inc.php',
      'test' => 'test.inc.php',
      'view' => 'view.inc.php',
      'vmods' => 'vmods.inc.php',
    ],
  ];
