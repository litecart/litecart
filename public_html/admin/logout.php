<?php
  require_once('../includes/app_header.inc.php');

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  user::logout();
