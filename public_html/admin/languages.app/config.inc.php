<?php

$app_config = array(
  'name' => language::translate('title_languages', 'Languages'),
  'default' => 'languages',
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'title' => language::translate('title_languages', 'Languages'),
      'doc' => 'languages',
      'params' => array(),
    ),
    array(
      'title' => language::translate('title_storage_encoding', 'Storage Encoding'),
      'doc' => 'storage_encoding',
      'params' => array(),
    ),
  ),
  'docs' => array(
    'languages' => 'languages.inc.php',
    'edit_language' => 'edit_language.inc.php',
    'storage_encoding' => 'storage_encoding.inc.php',
  ),
);

?>