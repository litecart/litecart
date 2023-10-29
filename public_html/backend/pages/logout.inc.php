<?php

  header('X-Robots-Tag: noindex');

  administrator::reset();

  session::regenerate_id();

  if (!empty($_COOKIE['remember_me'])) {
    header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
  }

  header('Location: ' . document::ilink('login'));
  exit;
