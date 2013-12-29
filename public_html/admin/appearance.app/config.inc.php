<?php

$app_config = array(
  'name' => language::translate('title_appearence', 'Appearence'),
  'default' => 'template',
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
  'icon' => 'icon.png',
  'docs' => array(
    'logotype' => 'logotype.inc.php',
    'template' => 'template.inc.php',
    'template_settings' => 'template_settings.inc.php',
  ),
);

?>