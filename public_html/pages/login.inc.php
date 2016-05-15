<?php
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  document::$snippets['title'][] = language::translate('login:head_title', 'Login');

  breadcrumbs::add(language::translate('title_login', 'Log In'));

  if (!empty(customer::$data['id'])) notices::add('notice', language::translate('text_already_logged_in', 'You are already logged in'));

  if (!empty($_POST['login'])) customer::login($_POST['email'], $_POST['password'], $_POST['redirect_url'], !empty($_POST['remember_me']) ? true : false);

  if (!empty($_POST['lost_password'])) customer::password_reset($_POST['email']);

  $page = new view();
  echo $page->stitch('views/box_login');
?>