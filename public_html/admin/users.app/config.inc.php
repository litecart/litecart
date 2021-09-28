<?php

  return $app_config = [
    'name' => language::translate('title_users', 'Users'),
    'default' => 'users',
    'priority' => 0,
    'theme' => [
      'color' => '#f79a2e',
      'icon' => 'fa-star',
    ],
    'menu' => [],
    'docs' => [
      'users' => 'users.inc.php',
      'edit_user' => 'edit_user.inc.php',
    ],
  ];
