<?php

  document::$layout = 'login';

  if (empty($_POST['username']) && !empty($_SERVER['PHP_AUTH_USER'])) {
    $_POST['username'] = !empty($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
  }

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  if (!empty(user::$data['id'])) notices::add('notice', language::translate('text_already_logged_in', 'You are already logged in'));

  if (empty($_COOKIE[session_name()])) {
    notices::add('notice', language::translate('error_missing_session_cookie', 'We failed to identify your browser session. Make sure your browser have cookies enabled or try another browser.'));
  }

  if (isset($_POST['login'])) {

    try {

      if (!empty($_COOKIE['remember_me'])) {
        header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
      }

      if (empty($_POST['username'])) throw new Exception(language::translate('error_missing_username', 'You must provide a username'));

      $user_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."users
        where lower(username) = lower('". database::input($_POST['username']) ."')
        limit 1;"
      );

      if (!$user = database::fetch($user_query)) {
        throw new Exception(language::translate('error_user_not_found', 'The user could not be found in our database'));
      }

      if (empty($user['status'])) throw new Exception(language::translate('error_account_suspended', 'The account is suspended'));

      if (!empty($user['date_valid_from']) && date('Y-m-d H:i:s') < $user['date_valid_from']) {
        throw new Exception(sprintf(language::translate('error_account_is_blocked', 'The account is blocked until %s'), language::strftime(language::$selected['format_datetime'], strtotime($user['date_valid_from']))));
      }

      if (!empty($user['date_valid_to']) && date('Y', strtotime($user['date_valid_to'])) > '1970' && date('Y-m-d H:i:s') > $user['date_valid_to']) {
        throw new Exception(sprintf(language::translate('error_account_expired', 'The account expired %s'), language::strftime(language::$selected['format_datetime'], strtotime($user['date_valid_to']))));
      }

      if (!password_verify($_POST['password'], $user['password_hash'])) {

        if (++$user['login_attempts'] < 3) {

          database::query(
            "update ". DB_TABLE_PREFIX ."users
            set login_attempts = login_attempts + 1
            where id = ". (int)$user['id'] ."
            limit 1;"
          );

          throw new Exception(language::translate('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'));

        } else {

          database::query(
            "update ". DB_TABLE_PREFIX ."users
            set login_attempts = 0,
            date_valid_from = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
            where id = ". (int)$user['id'] ."
            limit 1;"
          );

          throw new Exception(strtr(language::translate('error_account_has_been_blocked', 'This account has been temporary blocked %n minutes'), ['%n' => 15, '%d' => 15]));
        }
      }

      if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        database::query(
          "update ". DB_TABLE_PREFIX ."users
          set password_hash = '". database::input(password_hash($_POST['password'], PASSWORD_DEFAULT)) ."'
          where id = ". (int)$user['id'] ."
          limit 1;"
        );
      }

      if (!empty($user['last_hostname']) && $user['last_hostname'] != gethostbyaddr($_SERVER['REMOTE_ADDR'])) {
        notices::add('warnings', strtr(language::translate('warning_account_previously_used_by_another_host', 'Your account was previously used by another location or hostname (%hostname). If this was not you then your login credentials might be compromised.'), ['%hostname' => $user['last_hostname']]));
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."users
        set
          last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
          last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
          last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
          login_attempts = 0,
          total_logins = total_logins + 1,
          date_login = '". date('Y-m-d H:i:s') ."'
        where id = ". (int)$user['id'] ."
        limit 1;"
      );

      user::load($user['id']);

      session::regenerate_id();

      if (!empty($_POST['remember_me'])) {
        $checksum = sha1($user['username'] . $user['password_hash'] . $_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));
        header('Set-Cookie: remember_me='. $user['username'] .':'. $checksum .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; HttpOnly; SameSite=Lax', false);
      } else if (!empty($_COOKIE['remember_me'])) {
        header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
      }

      if (empty($_POST['redirect_url']) || preg_match('#^' . preg_quote(WS_DIR_ADMIN, '#') . 'index\.php#', $_POST['redirect_url'])) {
        $_POST['redirect_url'] = document::ilink('');
      }

      notices::add('success', str_replace(['%username'], [user::$data['username']], language::translate('success_now_logged_in_as', 'You are now logged in as %username')));
      header('Location: '. $_POST['redirect_url']);
      exit;

    } catch (Exception $e) {
      http_response_code(401);
      notices::add('errors', $e->getMessage());
    }
  }

  $page_login = new ent_view();
  echo $page_login->stitch('pages/login.inc.php');
