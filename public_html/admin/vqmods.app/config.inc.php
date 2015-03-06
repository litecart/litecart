<?php

  $app_config = array(
    'name' => language::translate('title_vqmods', 'vQmods'),
    'default' => 'vqmods',
    'theme' => array(
      'color' => '#a6dad7',
      'icon' => 'plug',
    ),
    'menu' => array(
      array(
        'title' => language::translate('title_vqmods', 'vQmods'),
        'doc' => 'vqmods',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_log', 'Log'),
        'doc' => 'log',
        'params' => array(),
      ),
    ),
    'docs' => array(
      'download' => 'download.inc.php',
      'log' => 'log.inc.php',
      'vqmods' => 'vqmods.inc.php',
    ),
  );

?>