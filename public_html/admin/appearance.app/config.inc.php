<?php

  $app_config = array(
    'name' => language::translate('title_appearance', 'Appearance'),
    'default' => 'template',
    'theme' => array(
      'color' => '#ff387c',
      'icon' => 'fa-adjust',
    ),
    'menu' => array(
      array(
        'title' => language::translate('title_template', 'Template'),
        'doc' => 'template',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_logotype', 'Logotype'),
        'doc' => 'logotype',
        'params' => array(),
      ),
    ),
    'docs' => array(
      'logotype' => 'logotype.inc.php',
      'template' => 'template.inc.php',
      'template_settings' => 'template_settings.inc.php',
    ),
  );
