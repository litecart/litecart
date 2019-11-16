<?php

  return $app_config = array(
    'name' => language::translate('title_vMods', 'vMods'),
    'default' => 'vmods',
    'priority' => 0,
    'theme' => array(
      'color' => '#77d2cd',
      'icon' => 'fa-plug',
    ),
    'menu' => array(),
    'docs' => array(
      'download' => 'download.inc.php',
      'test' => 'test.inc.php',
      'view' => 'view.inc.php',
      'vmods' => 'vmods.inc.php',
    ),
  );
