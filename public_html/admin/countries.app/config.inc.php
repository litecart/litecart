<?php

  return $app_config = [
    'name' => language::translate('title_countries', 'Countries'),
    'default' => 'countries',
    'priority' => 0,
    'theme' => [
      'color' => '#43bbe7',
      'icon' => 'fa-flag',
    ],
    'menu' => [],
    'docs' => [
      'countries' => 'countries.inc.php',
      'edit_country' => 'edit_country.inc.php',
    ],
  ];
