<?php
  require_once('../includes/app_header.inc.php');

  document::$template = settings::get('store_template_admin');
  document::$layout = 'login';

  if (empty($_POST['username']) && !empty($_SERVER['PHP_AUTH_USER'])) {
    $_POST['username'] = !empty($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
  }

  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';

  if (!empty(user::$data['id'])) notices::add('notice', language::translate('text_already_logged_in', 'You are already logged in'));

  if (isset($_POST['login'])) {

    try {

      header('Set-Cookie: remember_me=; path='. WS_DIR_APP .'; expires=-1; HttpOnly; SameSite=Strict');

      if (empty($_POST['username'])) throw new Exception(language::translate('error_missing_username', 'You must provide a username'));

      $user_query = database::query(
        "select * from ". DB_TABLE_USERS ."
        where lower(username) = lower('". database::input($_POST['username']) ."')
        limit 1;"
      );

      if (!$user = database::fetch($user_query)) {
        throw new Exception(language::translate('error_user_not_found', 'The user could not be found in our database'));
      }

      if (empty($user['status'])) throw new Exception(language::translate('error_account_suspended', 'The account is suspended'));

      if (date('Y', strtotime($user['date_valid_to'])) > '1970' && date('Y-m-d H:i:s') > $user['date_valid_to']) {
        throw new Exception(sprintf(language::translate('error_account_expired', 'The account expired %s'), language::strftime(language::$selected['format_datetime'], strtotime($user['date_valid_to']))));
      }

      if (date('Y-m-d H:i:s') < $user['date_valid_from']) {
        throw new Exception(sprintf(language::translate('error_account_is_blocked', 'The account is blocked until %s'), language::strftime(language::$selected['format_datetime'], strtotime($user['date_valid_from']))));
      }

    // Compatibility with older passwords (prior to LiteCart 2.2.0)
      if (substr($user['password_hash'], 0, 1) != '$') {

        if (functions::password_checksum($user['id'], $_POST['password']) != $user['password_hash']) {
          throw new Exception(language::translate('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'));
        }

      // Migrate password
        database::query(
          "update ". DB_TABLE_USERS ."
          set password_hash = '". database::input($user['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT)) ."'
          where id = ". (int)$user['id'] ."
          limit 1;"
        );
      }

      if (!password_verify($_POST['password'], $user['password_hash'])) {
        if (++$user['login_attempts'] < 3) {

          database::query(
            "update ". DB_TABLE_USERS ."
            set login_attempts = login_attempts + 1
            where id = ". (int)$user['id'] ."
            limit 1;"
          );

          notices::add('errors', sprintf(language::translate('error_d_login_attempts_left', 'You have %d login attempts left until your account is temporary blocked'), 3 - $user['login_attempts']));

        } else {

          database::query(
            "update ". DB_TABLE_USERS ."
            set login_attempts = 0,
            date_valid_from = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
            where id = ". (int)$user['id'] ."
            limit 1;"
          );

          notices::add('errors', sprintf(language::translate('error_account_has_been_blocked', 'The account has been temporary blocked %d minutes'), 15));
        }

        throw new Exception(language::translate('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'));
      }

      if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        database::query(
          "update ". DB_TABLE_USERS ."
          set password_hash = '". database::input(password_hash($_POST['password'], PASSWORD_DEFAULT)) ."'
          where id = ". (int)$user['id'] ."
          limit 1;"
        );
      }

      if (!empty($user['last_host']) && $user['last_host'] != gethostbyaddr($_SERVER['REMOTE_ADDR'])) {
        notices::add('warnings', strtr(language::translate('warning_account_previously_used_by_another_host', 'Your account was previously used by another location or hostname (%hostname). If this was not you then your login credentials might be compromised.'), array('%hostname' => $user['last_host'])));
      }

      database::query(
        "update ". DB_TABLE_USERS ."
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

      if (!empty($_POST['remember_me'])) {
        $checksum = sha1($user['username'] . $user['password_hash'] . $_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));
        header('Set-Cookie: remember_me='. $user['username'] .':'. $checksum .'; path='. WS_DIR_APP .'; expires='. gmdate('r', strtotime('+3 months')) .'; HttpOnly; SameSite=Strict');
      } else {
        header('Set-Cookie: remember_me=; path='. WS_DIR_APP .'; expires=-1; HttpOnly; SameSite=Strict');
      }

      if (empty($_POST['redirect_url']) || preg_match('#^' . preg_quote(WS_DIR_ADMIN, '#') . 'index\.php#', $_POST['redirect_url'])) {
        $_POST['redirect_url'] = document::link(WS_DIR_ADMIN);
      }

      notices::add('success', str_replace(array('%username'), array(user::$data['username']), language::translate('success_now_logged_in_as', 'You are now logged in as %username')));
      header('Location: '. $_POST['redirect_url']);
      exit;

    } catch (Exception $e) {
      //http_response_code(401); // Troublesome with HTTP Auth
      notices::add('errors', $e->getMessage());
    }
  }

  $page_login = new ent_view();
  echo $page_login->stitch('pages/login');

  require_once vmod::check(FS_DIR_APP . 'includes/app_footer.inc.php');
