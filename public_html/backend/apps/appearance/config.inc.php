<?php

  return $app_config = [
    'name' => language::translate('title_appearance', 'Appearance'),
    'default' => 'template',
    'priority' => 0,

    'theme' => [
      'color' => '#ff2a72',
      'icon' => 'fa-adjust',
    ],

    'menu' => [
      [
        'title' => language::translate('title_template', 'Template'),
        'doc' => 'template',
        'params' => [],
      ],
      [
        'title' => language::translate('title_logotype', 'Logotype'),
        'doc' => 'logotype',
        'params' => [],
      ],
    ],

    'docs' => [
      'logotype' => 'logotype.inc.php',
      'template' => 'template.inc.php',
      'template_settings' => 'template_settings.inc.php',
    ],
  ];
