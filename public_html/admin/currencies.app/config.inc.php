<?php

  $app_config = array(
    'name' => language::translate('title_currencies', 'Currencies'),
    'default' => 'currencies',
    'theme' => array(
      'color' => '#f3be30',
      'icon' => 'fa-money',
    ),

    'menu' => array(),
    'docs' => array(
      'currencies' => 'currencies.inc.php',
      'edit_currency' => 'edit_currency.inc.php',
    ),
  );
