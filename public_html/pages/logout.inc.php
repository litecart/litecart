<?php
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  cart::reset();
  customer::reset();

  session::regenerate_id();
  session::$data['cart']['uid'] = null;

  setcookie('cart[uid]', null, -1, WS_DIR_HTTP_HOME);
  setcookie('customer_remember_me', null, -1, WS_DIR_HTTP_HOME);

  notices::add('success', language::translate('description_logged_out', 'You are now logged out.'));

  header('Location: ' . document::ilink(''));
  exit;
