<?php

$app_config = array(
  'name' => $GLOBALS['system']->language->translate('title_languages', 'Languages'),
  'default' => 'languages',
  'icon' => 'icon.png',
  'menu' => array(),
  'docs' => array(
    'languages' => 'languages.inc.php',
    'edit_language' => 'edit_language.inc.php',
  ),
);

?>