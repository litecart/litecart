<?php
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  document::$snippets['title'][] = language::translate('login:head_title', 'Login');

  if (empty($_POST['remember_me'])) $_POST['remember_me'] = false;
  if (empty($_POST['redirect_url'])) $_POST['redirect_url'] = '';

  breadcrumbs::add(language::translate('title_login', 'Log In'));

  if (!empty(customer::$data['id'])) notices::add('notice', language::translate('text_already_logged_in', 'You are already logged in'));

  if (!empty($_POST['login'])) {
    customer::login($_POST['email'], $_POST['password'], $_POST['redirect_url'], $_POST['remember_me']);
  }

  if (!empty($_POST['lost_password'])) {
    customer::password_reset($_POST['email']);
  }

  $_page = new view();
  echo $_page->stitch('pages/login');
?>