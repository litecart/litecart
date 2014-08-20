<?php
  
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  setcookie('customer_remember_me', '', strtotime('-1 year'), WS_DIR_HTTP_HOME);

  customer::logout(document::ilink(''));
?>