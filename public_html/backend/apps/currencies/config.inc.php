<?php

  return $app_config = [
    'name' => language::translate('title_currencies', 'Currencies'),
    'default' => 'currencies',
    'priority' => 0,
    'theme' => [
      'color' => '#ecae06',
      'icon' => 'fa-money',
    ],

    'menu' => [],
    'docs' => [
      'currencies' => 'currencies.inc.php',
      'edit_currency' => 'edit_currency.inc.php',
    ],
  ];
