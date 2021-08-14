<?php

  return $app_config = [
    'name' => language::translate('title_countries', 'Countries'),
    'default' => 'countries',
    'priority' => 0,

    'theme' => [
      'color' => '#21a9d2',
      'icon' => 'fa-flag',
    ],

    'menu' => [],

    'docs' => [
      'countries' => 'countries.inc.php',
      'edit_country' => 'edit_country.inc.php',
      'zones.json' => 'zones.json.inc.php',
    ],
  ];
