<?php

  document::$layout = 'blank';

  document::$head_tags[] = '<meta name="viewport" content="width=device-width, initial-scale=1">';

  if (!session_name()) {
    notices::add('notices', language::translate('error_missing_session_cookie', 'We failed to identify your browser session. Make sure your browser has cookies enabled or try another browser.'));
  }

  if (isset($_POST['login'])) {

    try {

      if (!empty($_COOKIE['remember_me'])) {
        header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
      }

      if (empty($_POST['username'])) {
        throw new Exception(language::translate('error_must_enter_your_username_or_email', 'You must enter your username or email address'));
      }

      if (empty($_POST['password'])) {
        throw new Exception(language::translate('error_must_enter_your_password', 'You must enter your password'));
      }

      $administrator = database::query(
        "select * from ". DB_TABLE_PREFIX ."administrators
        where lower(username) = '". database::input(strtolower($_POST['username'])) ."'
        or lower(email) = '". database::input(strtolower($_POST['username'])) ."'
        limit 1;"
      )->fetch();

      if (!$administrator) {
        throw new Exception(language::translate('error_administrator_not_found', 'The administrator could not be found in our database'));
      }

      if (empty($administrator['status'])) {
        throw new Exception(language::translate('error_administrator_account_disabled', 'The administrator account is disabled'));
      }

      if (!empty($administrator['date_valid_from']) && date('Y-m-d H:i:s') < $administrator['date_valid_from']) {
        throw new Exception(sprintf(language::translate('error_account_is_blocked', 'The account is blocked until %s'), language::strftime(language::$selected['format_datetime'], strtotime($administrator['date_valid_from']))));
      }

      if (!empty($administrator['date_valid_to']) && date('Y-m-d H:i:s') > $administrator['date_valid_to']) {
        throw new Exception(sprintf(language::translate('error_account_expired', 'The account expired %s'), language::strftime(language::$selected['format_datetime'], strtotime($administrator['date_valid_to']))));
      }

      if (!password_verify($_POST['password'], $administrator['password_hash'])) {

        if (++$administrator['login_attempts'] < 3) {

          database::query(
            "update ". DB_TABLE_PREFIX ."administrators
            set login_attempts = login_attempts + 1
            where id = ". (int)$administrator['id'] ."
            limit 1;"
          );

          throw new Exception(language::translate('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'));

        } else {

          database::query(
            "update ". DB_TABLE_PREFIX ."administrators
            set login_attempts = 0,
            date_valid_from = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
            where id = ". (int)$administrator['id'] ."
            limit 1;"
          );

          if (!empty($administrator['email'])) {

            $aliases = [
              '%store_name' => settings::get('store_name'),
              '%store_link' => document::ilink(''),
              '%username' => $administrator['username'],
              '%expires' => date('Y-m-d H:i:00', strtotime('+15 minutes')),
              '%ip_address' => $_SERVER['REMOTE_ADDR'],
              '%hostname' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
              '%user_agent' => $_SERVER['HTTP_USER_AGENT'],
            ];

            $subject = language::translate('administrator_account_blocked:email_subject', 'Administrator Account Blocked');
            $message = strtr(language::translate('administrator_account_blocked:email_body', implode("\r\n", [
              'Your administrator account %username has been blocked until %expires because of too many invalid attempts.',
              '',
              'Client: %hostname (%ip_address)',
              '%user_agent',
              '',
              '%store_name',
              '%store_link',
            ])), $aliases);

            $email = new ent_email();
            $email->add_recipient($administrator['email'], $administrator['username'])
                  ->set_subject($subject)
                  ->add_body($message)
                  ->send();
          }

          throw new Exception(strtr(language::translate('error_account_has_been_blocked', 'This account has been temporary blocked %n minutes'), ['%n' => 15, '%d' => 15]));
        }

        throw new Exception(language::translate('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'));
      }

      if (password_needs_rehash($administrator['password_hash'], PASSWORD_DEFAULT)) {
        database::query(
          "update ". DB_TABLE_PREFIX ."administrators
          set password_hash = '". database::input(password_hash($_POST['password'], PASSWORD_DEFAULT)) ."'
          where id = ". (int)$administrator['id'] ."
          limit 1;"
        );
      }

      if (!empty($administrator['last_hostname']) && $administrator['last_hostname'] != gethostbyaddr($_SERVER['REMOTE_ADDR'])) {
        notices::add('warnings', strtr(language::translate('warning_account_previously_used_by_another_host', 'Your account was previously used by another location or hostname (%hostname). If this was not you then your login credentials might be compromised.'), ['%hostname' => $administrator['last_hostname']]));
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."administrators
        set last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
          last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
          last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
          login_attempts = 0,
          total_logins = total_logins + 1,
          date_login = '". date('Y-m-d H:i:s') ."'
        where id = ". (int)$administrator['id'] ."
        limit 1;"
      );

      administrator::load($administrator['id']);

      session::$data['administrator_security_timestamp'] = time();
      session::regenerate_id();

      if (!empty($_POST['remember_me'])) {
        $checksum = sha1($administrator['username'] . $administrator['password_hash'] . $_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));
        header('Set-Cookie: remember_me='. $administrator['username'] .':'. $checksum .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; HttpOnly; SameSite=Lax', false);
      } else if (!empty($_COOKIE['remember_me'])) {
        header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
      }

      if (!empty($_POST['redirect_url'])) {
        $redirect_url = new ent_link($_POST['redirect_url']);
        $redirect_url->host = '';
      } else {
        $redirect_url = document::ilink('b:');
      }

      notices::add('success', str_replace(['%username'], [administrator::$data['username']], language::translate('success_now_logged_in_as', 'You are now logged in as %username')));
      header('Location: '. $redirect_url);
      exit;

    } catch (Exception $e) {
      http_response_code(401); // Troublesome with HTTP Auth (e.g. .htpasswd)
      notices::add('errors', $e->getMessage());
    }
  }

  $page_login = new ent_view('app://backend/template/pages/login.inc.php');
  echo $page_login;
