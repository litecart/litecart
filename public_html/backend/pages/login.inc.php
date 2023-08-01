<?php

  document::$layout = 'login';

  header('X-Robots-Tag: noindex');

  if (!empty(user::$data['id'])) notices::add('notices', language::translate('text_already_logged_in', 'You are already logged in'));

  if (empty($_COOKIE[session_name()])) {
    notices::add('notices', language::translate('error_missing_session_cookie', 'We failed to identify your browser session. Make sure your browser has cookies enabled or try another browser.'));
  }

  if (isset($_POST['login'])) {

    try {

      if (!empty($_COOKIE['remember_me'])) {
        header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
      }

      if (empty($_POST['username'])) throw new Exception(language::translate('error_must_enter_your_username_or_email', 'You must enter your username or email address'));
      if (empty($_POST['password'])) throw new Exception(language::translate('error_must_enter_your_password', 'You must enter your password'));

      $user = database::query(
        "select * from ". DB_TABLE_PREFIX ."users
        where lower(username) = '". database::input(strtolower($_POST['username'])) ."'
        or lower(email) = '". database::input(strtolower($_POST['username'])) ."'
        limit 1;"
      )->fetch();

      if (!$user) {
        throw new Exception(language::translate('error_user_not_found', 'The user could not be found in our database'));
      }

      if (empty($user['status'])) throw new Exception(language::translate('error_user_account_disabled', 'The user account is disabled'));

      if (!empty($user['date_valid_from']) && date('Y-m-d H:i:s') < $user['date_valid_from']) {
        throw new Exception(sprintf(language::translate('error_account_is_blocked', 'The account is blocked until %s'), language::strftime(language::$selected['format_datetime'], strtotime($user['date_valid_from']))));
      }

      if (!empty($user['date_valid_to']) && date('Y-m-d H:i:s') > $user['date_valid_to']) {
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

          if (!empty($user['email'])) {

            $aliases = [
              '%store_name' => settings::get('store_name'),
              '%store_link' => document::ilink(''),
              '%username' => $user['username'],
              '%expires' => date('Y-m-d H:i:00', strtotime('+15 minutes')),
              '%ip_address' => $_SERVER['REMOTE_ADDR'],
              '%hostname' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
              '%user_agent' => $_SERVER['HTTP_USER_AGENT'],
            ];

            $subject = language::translate('user_account_blocked:email_subject', 'User Account Blocked');
            $message = strtr(language::translate('user_account_blocked:email_body',
              "Your user account %username has been blocked until %expires because of too many invalid attempts.\r\n"
            . "\r\n"
            . "Client: %hostname (%ip_address)\r\n"
            . "%user_agent\r\n"
            . "\r\n"
            . "%store_name\r\n"
            . "%store_link"
            ), $aliases);

            $email = new ent_email();
            $email->add_recipient($user['email'], $user['username'])
                  ->set_subject($subject)
                  ->add_body($message)
                  ->send();
          }

          throw new Exception(strtr(language::translate('error_account_has_been_blocked', 'This account has been temporary blocked %n minutes'), ['%n' => 15, '%d' => 15]));
        }

        throw new Exception(language::translate('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'));
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
        set last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
          last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
          last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
          login_attempts = 0,
          total_logins = total_logins + 1,
          date_login = '". date('Y-m-d H:i:s') ."'
        where id = ". (int)$user['id'] ."
        limit 1;"
      );

      user::load($user['id']);

      session::$data['user_security_timestamp'] = time();
      session::regenerate_id();

      if (!empty($_POST['remember_me'])) {
        $checksum = sha1($user['username'] . $user['password_hash'] . $_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));
        header('Set-Cookie: remember_me='. $user['username'] .':'. $checksum .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; HttpOnly; SameSite=Lax', false);
      } else if (!empty($_COOKIE['remember_me'])) {
        header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
      }

      if (!empty($_POST['redirect_url'])) {
        $redirect_url = new ent_link($_POST['redirect_url']);
        $redirect_url->host = '';
      } else {
        $redirect_url = document::ilink('b:');
      }

      notices::add('success', str_replace(['%username'], [user::$data['username']], language::translate('success_now_logged_in_as', 'You are now logged in as %username')));
      header('Location: '. $redirect_url);
      exit;

    } catch (Exception $e) {
      http_response_code(401); // Troublesome with HTTP Auth (e.g. .htpasswd)
      notices::add('errors', $e->getMessage());
    }
  }

  $page_login = new ent_view();
  echo $page_login->render(FS_DIR_TEMPLATE . 'pages/login.inc.php');
