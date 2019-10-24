<?php
  require_once('../includes/app_header.inc.php');

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  user::reset();

  setcookie('remember_me', null, -1, WS_DIR_APP);

  header('Location: ' . document::link(WS_DIR_ADMIN . 'login.php'));
  exit;
