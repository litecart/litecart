<?php
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  cart::reset();
  customer::reset();

  session::regenerate_id();
  session::$data['cart']['uid'] = null;

  header('Set-Cookie: cart[uid]=; path='. WS_DIR_APP .'; expires=-1; HttpOnly; SameSite=Strict');
  header('Set-Cookie: customer_remember_me=; path='. WS_DIR_APP .'; expires=-1; HttpOnly; SameSite=Strict');

  notices::add('success', language::translate('description_logged_out', 'You are now logged out.'));

  header('Location: ' . document::ilink(''));
  exit;
