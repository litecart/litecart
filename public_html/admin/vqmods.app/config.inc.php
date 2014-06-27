<?php

$app_config = array(
  'name' => language::translate('title_vqmods', 'VQMods'),
  'default' => 'vqmods',
  'icon' => 'icon.png',
  'menu' => array(
    array(
      'title' => language::translate('title_vqmods', 'VQMods'),
      'doc' => 'vqmods',
      'params' => array(),
    ),
    array(
      'title' => language::translate('title_log', 'Log'),
      'doc' => 'log',
      'params' => array(),
    ),
  ),
  'docs' => array(
    'vqmods' => 'vqmods.inc.php',
    'log' => 'log.inc.php',
  ),
);

?>