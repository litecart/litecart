<?php
  require_once('../includes/app_header.inc.php');
  
  header('X-Robots-Tag: noindex');
  $system->document->snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  $system->user->logout();
  
?>