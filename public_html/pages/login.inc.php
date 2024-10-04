<?php

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex">';
  document::$snippets['title'][] = language::translate('title_sign_in', 'Sign In');

  breadcrumbs::add(language::translate('title_sign_in', 'Sign In'));

  if (!settings::get('accounts_enabled')) {
    echo language::translate('error_accounts_are_disabled', 'Accounts are disabled');
    return;
  }

  if (!$_POST) {
    $_POST['email'] = customer::$data['email'];
  }

  if (empty($_POST['remember_me'])) $_POST['remember_me'] = false;

  if (!empty(customer::$data['id'])) notices::add('notices', language::translate('text_already_logged_in', 'You are already logged in'));

  if (!empty($_POST['login'])) {

    try {

      if (!empty($_COOKIE['customer_remember_me'])) {
        header('Set-Cookie: customer_remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
      }

      if (empty($_POST['email']) || empty($_POST['password'])) {
        throw new Exception(language::translate('error_missing_login_credentials', 'You must provide both your email address and password'));
      }

      $customer_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."customers
        where lower(email) = '". database::input(strtolower($_POST['email'])) ."'
        limit 1;"
      );

      if (!$customer = database::fetch($customer_query)) {
        throw new Exception(language::translate('error_email_not_found_in_database', 'The email does not exist in our database'));
      }

      if (empty($customer['status'])) {
        throw new Exception(language::translate('error_customer_account_disabled_or_not_activated', 'The customer account is disabled or not activated'));
      }

      if (!empty($customer['date_blocked_until']) && date('Y-m-d H:i:s') < $customer['date_blocked_until']) {
        throw new Exception(sprintf(language::translate('error_account_is_blocked', 'The account is blocked until %s'), language::strftime(language::$selected['format_datetime'], strtotime($customer['date_blocked_until']))));
      }

      if (!password_verify($_POST['password'], $customer['password_hash'])) {

        if (++$customer['login_attempts'] < 3) {

          database::query(
            "update ". DB_TABLE_PREFIX ."customers
            set login_attempts = login_attempts + 1
            where id = ". (int)$customer['id'] ."
            limit 1;"
          );

          throw new Exception(language::translate('error_wrong_password_or_account', 'Wrong password or the account does not exist'));

        } else {

          database::query(
            "update ". DB_TABLE_PREFIX ."customers
            set login_attempts = 0,
            date_blocked_until = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
            where id = ". (int)$customer['id'] ."
            limit 1;"
          );

          throw new Exception(strtr(language::translate('error_this_account_has_been_temporarily_blocked_n_minutes', 'This account has been temporarily blocked %n minutes'), ['%n' => 15, '%d' => 15]));
        }
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
          login_attempts = 0,
          total_logins = total_logins + 1,
          last_ip = '". database::input($_SERVER['REMOTE_ADDR']) ."',
          last_host = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
          last_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
          date_login = '". date('Y-m-d H:i:s') ."'
        where id = ". (int)$customer['id'] ."
        limit 1;"
      );

      customer::load($customer['id']);

      session::$data['customer_security_timestamp'] = time();
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

      if (!empty($_POST['redirect_url'])) {
        $redirect_url = new ent_link($_POST['redirect_url']);
        $redirect_url->host = '';
      } else {
        $redirect_url = document::ilink('');
      }

      header('Location: '. $redirect_url);
      exit;

    } catch (Exception $e) {
      http_response_code(401); // Troublesome with HTTP Auth (e.g. .htpasswd)
      notices::add('errors', $e->getMessage());
    }
  }

  $_page = new ent_view();
  echo $_page->stitch('pages/login');
