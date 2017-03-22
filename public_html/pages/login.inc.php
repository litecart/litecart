<?php
  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    document::$layout = 'ajax';
  }

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  document::$snippets['title'][] = language::translate('title_sign_in', 'Sign In');

  breadcrumbs::add(language::translate('title_sign_in', 'Sign In'));

  if (empty($_POST['remember_me'])) $_POST['remember_me'] = false;
  if (empty($_REQUEST['redirect_url'])) $_REQUEST['redirect_url'] = '';

  if (!empty(customer::$data['id'])) notices::add('notice', language::translate('text_already_logged_in', 'You are already logged in'));

  if (!empty($_POST['login'])) {
    customer::login($_POST['email'], $_POST['password'], $_REQUEST['redirect_url'], $_POST['remember_me']);
  }

  $_page = new view();

  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    echo $_page->stitch('pages/login.ajax');
  } else {
    echo $_page->stitch('pages/login');
  }
