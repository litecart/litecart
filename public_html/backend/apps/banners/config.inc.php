<?php

  return $app_config = [
    'name' => language::translate('title_banners', 'Banners'),
    'default' => 'banners',
    'priority' => 0,
    'theme' => [
      'color' => '#c71799',
      'icon' => 'fa-th-large',
    ],
    'menu' => [],
    'docs' => [
      'banners' => 'banners.inc.php',
      'edit_banner' => 'edit_banner.inc.php',
    ],
  ];
