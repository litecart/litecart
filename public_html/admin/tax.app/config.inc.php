<?php

  return $app_config = [
    'name' => language::translate('title_tax', 'Tax'),
    'default' => 'tax_rates',
    'priority' => 0,
    'theme' => [
      'color' => '#c0cc82',
      'icon' => 'fa-university',
    ],
    'menu' => [
      [
        'title' => language::translate('title_tax_rates', 'Tax Rates'),
        'doc' => 'tax_rates',
        'params' => [],
      ],
      [
        'title' => language::translate('title_tax_classes', 'Tax Classes'),
        'doc' => 'tax_classes',
        'params' => [],
      ],
    ],
    'docs' => [
      'tax_classes' => 'tax_classes.inc.php',
      'edit_tax_class' => 'edit_tax_class.inc.php',
      'tax_rates' => 'tax_rates.inc.php',
      'tax_rates.json' => 'tax_rates.json.inc.php',
      'edit_tax_rate' => 'edit_tax_rate.inc.php',
    ],
  ];
