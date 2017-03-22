<?php
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  $redirect_url = document::ilink('');
  customer::logout($redirect_url);
