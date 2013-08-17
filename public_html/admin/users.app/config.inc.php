<?php

$app_config = array(
  'name' => $GLOBALS['system']->language->translate('title_users', 'Users'),
  'default' => 'users',
  'icon' => 'icon.png',
  'menu' => array(),
  'docs' => array(
    'users' => 'users.inc.php',
    'edit_user' => 'edit_user.inc.php',
  ),
);

?>