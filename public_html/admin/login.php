<?php
  require_once('../includes/app_header.inc.php');

  document::$template = settings::get('store_template_admin');
  document::$layout = 'login';

  if (empty($_POST['username']) && !empty($_SERVER['PHP_AUTH_USER'])) {
    $_POST['username'] = !empty($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
  }

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex">';

  if (!empty(user::$data['id'])) notices::add('notices', language::translate('text_already_logged_in', 'You are already logged in'));

  if (empty($_COOKIE[session_name()])) {
    notices::add('notices', language::translate('error_missing_session_cookie', 'We failed to identify your browser session. Make sure your browser has cookies enabled or try another browser.'));
  }

  if (isset($_POST['login'])) {

    try {

      if (!empty($_COOKIE['remember_me'])) {
        header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
      }

      if (empty($_POST['username'])) throw new Exception(language::translate('error_missing_username', 'You must provide a username'));

      $user_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."users
        where lower(username) = '". database::input(strtolower($_POST['username'])) ."'
        or lower(email) = '". database::input(strtolower($_POST['username'])) ."'
        limit 1;"
      );

      if (!$user = database::fetch($user_query)) {
        throw new Exception(language::translate('error_user_not_found', 'The user could not be found in our database'));
      }

      if (empty($user['status'])) throw new Exception(language::translate('error_user_account_disabled', 'The user account is disabled'));

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

          throw new Exception(sprintf(language::translate('error_d_login_attempts_left', 'You have %d login attempts left until your account is temporarily blocked'), 3 - $user['login_attempts']));

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

          throw new Exception(sprintf(language::translate('error_account_has_been_blocked_x_minutes', 'This account has been temporarily blocked for %d minutes.'), 15));
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

      if (!empty($user['last_host']) && $user['last_host'] != gethostbyaddr($_SERVER['REMOTE_ADDR'])) {

        notices::add('warnings', strtr(language::translate('warning_account_previously_used_by_another_host', 'Your account was previously used by another location or hostname (%hostname). If this was not you then your login credentials might be compromised.'), ['%hostname' => $user['last_host']]));

        if (!empty($user['email'])) {

          $aliases = [
            '%store_name' => settings::get('store_name'),
            '%store_link' => document::ilink(''),
            '%username' => $user['username'],
            '%expires' => date('Y-m-d H:i:00', strtotime('+15 minutes')),
            '%ip_address' => $_SERVER['REMOTE_ADDR'],
            '%hostname' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
            '%user_agent' => $_SERVER['HTTP_USER_AGENT'],
            '%previous_ip_address' => $user['last_ip'],
            '%previous_hostname' => $user['last_host'],
          ];

          $subject = language::translate('different_hostname_login:email_subject', 'A different hostname was used to sign in to your account');
          $message = strtr(language::translate('different_hostname_login:email_body',
            "Your user account %username was just signed in to from a different client hostname.\r\n"
          . "\r\n"
          . "Currently:\r\n"
          . "%hostname (%ip_address)\r\n"
          . "%user_agent\r\n"
          . "\r\n"
          . "Previously:\r\n"
          . "%previous_hostname (%previous_ip_address)\r\n"
          . "\r\n"
          . "If this was not you then your account could be compromised."
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
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."users
        set
          last_ip = '". database::input($_SERVER['REMOTE_ADDR']) ."',
          last_host = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
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

      if (!empty($_POST['redirect_url']) && !preg_match('#^' . preg_quote(WS_DIR_ADMIN, '#') . 'index\.php#', $_POST['redirect_url'])) {
        $redirect_url = new ent_link($_POST['redirect_url']);
        $redirect_url->host = '';
      } else {
        $redirect_url = document::link(WS_DIR_ADMIN);
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
  echo $page_login->stitch('pages/login');

  require_once vmod::check(FS_DIR_APP . 'includes/app_footer.inc.php');
