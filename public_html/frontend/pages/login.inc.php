<?php

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  document::$snippets['title'][] = language::translate('title_sign_in', 'Sign In');

  breadcrumbs::add(language::translate('title_sign_in', 'Sign In'));

  if (!settings::get('accounts_enabled')) {
    echo language::translate('error_accounts_are_disabled', 'Accounts are disabled');
    return;
  }

  if (empty($_POST['remember_me'])) $_POST['remember_me'] = false;
  if (empty($_REQUEST['redirect_url'])) $_REQUEST['redirect_url'] = document::ilink('');

  if (!empty(customer::$data['id'])) notices::add('notice', language::translate('text_already_logged_in', 'You are already logged in'));

  if (!empty($_POST['login'])) {

    try {

      if (!empty($_COOKIE['customer_remember_me'])) {
        header('Set-Cookie: customer_remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
      }

      if (empty($_POST['email']) || empty($_POST['password'])) {
        throw new Exception(language::translate('error_missing_login_credentials', 'You must provide both email address and password.'));
      }

      $customer_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."customers
        where lower(email) = lower('". database::input($_POST['email']) ."')
        limit 1;"
      );

      if (!$customer = database::fetch($customer_query)) {
        throw new Exception(language::translate('error_email_not_found_in_database', 'The email does not exist in our database'));
      }

      if (empty($customer['status'])) {
        throw new Exception(language::translate('error_account_inactive', 'Your account is inactive, contact customer support'));
      }

      if (!password_verify($_POST['password'], $customer['password_hash'])) {
        throw new Exception(language::translate('error_wrong_password_or_account', 'Wrong password or the account does not exist'));
      }

      if (password_needs_rehash($customer['password_hash'], PASSWORD_DEFAULT)) {
        database::query(
          "update ". DB_TABLE_PREFIX ."customers
          set password_hash = '". database::input(password_hash($_POST['password'], PASSWORD_DEFAULT)) ."'
          where id = ". (int)$customer['id'] ."
          limit 1;"
        );
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."customers set
          num_logins = num_logins + 1,
          last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
          last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
          last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
          date_login = '". date('Y-m-d H:i:s') ."'
        where id = ". (int)$customer['id'] ."
        limit 1;"
      );

      customer::load($customer['id']);

      session::regenerate_id();

      if (!empty($_POST['remember_me'])) {
        $checksum = sha1($customer['email'] . $customer['password_hash'] . $_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));
        header('Set-Cookie: customer_remember_me='. $customer['email'] .':'. $checksum .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; HttpOnly; SameSite=Lax', false);
      }

      notices::add('success', strtr(language::translate('success_logged_in_as_user', 'You are now logged in as %firstname %lastname.'), [
        '%email' => customer::$data['email'],
        '%firstname' => customer::$data['firstname'],
        '%lastname' => customer::$data['lastname'],
      ]));

      header('Location: '. $_REQUEST['redirect_url']);
      exit;

    } catch (Exception $e) {
      //http_response_code(401); // Troublesome with HTTP Auth
      notices::add('errors', $e->getMessage());
    }
  }


  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $_page = new ent_view('pages/login.ajax.inc.php');
  } else {
    $_page = new ent_view('pages/login.inc.php');
  }

  echo $_page;
