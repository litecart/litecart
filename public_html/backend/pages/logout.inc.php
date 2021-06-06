<?php

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  user::reset();

  session::regenerate_id();

  if (!empty($_COOKIE['remember_me'])) {
    header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
  }

  header('Location: ' . document::ilink('login'));
  exit;
