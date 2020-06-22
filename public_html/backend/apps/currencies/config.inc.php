<?php

  return $app_config = [
    'name' => language::translate('title_currencies', 'Currencies'),
    'default' => 'currencies',
    'priority' => 0,
    'theme' => [
      'color' => '#f3b91c',
      'icon' => 'fa-money',
    ],

    'menu' => [],
    'docs' => [
      'currencies' => 'currencies.inc.php',
      'edit_currency' => 'edit_currency.inc.php',
    ],
  ];
