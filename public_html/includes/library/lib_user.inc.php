<?php

  class user {
    public static $data;

    //public static function construct() {
    //}

    public static function load_dependencies() {
      self::$data = &session::$data['user'];
    }

    //public static function initiate() {
    //}

    public static function startup() {

      if (empty(session::$data['user']) || !is_array(session::$data['user'])) {
        self::reset();
      }

      if (!empty(self::$data['id'])) {
        ini_set('display_errors', 'On');

        database::query(
          "update ". DB_TABLE_USERS ."
          set date_active = '". date('Y-m-d H:i:s') ."'
          where id = '". self::$data['id'] ."'
          limit 1;"
        );
        self::load(self::$data['id']);
      }

      if (empty(self::$data['id']) && !empty($_COOKIE['remember_me']) && empty($_POST)) {
        list($username, $key) = explode(':', $_COOKIE['remember_me']);

        $user_query = database::query(
          "select * from ". DB_TABLE_USERS ."
          where username = '". database::input($username) ."'
          limit 1;"
        );
        $user = database::fetch($user_query);

        $do_login = false;
        if (!empty($user)) {
          $checksum = sha1($user['username'] . $user['password'] . PASSWORD_SALT . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));
          if ($checksum == $key) $do_login = true;
        }

        if ($do_login) {
          self::load($user['id']);
        } else {
          setcookie('remember_me', null, -1, WS_DIR_HTTP_HOME);
        }
      }
    }

    //public static function before_capture() {
    //}

    //public static function after_capture() {
    //}

    //public static function prepare_output() {
    //}

    //public static function before_output() {
    //}

    //public static function shutdown() {
    //}

    ######################################################################

    public static function reset() {

      session::$data['user'] = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_USERS .";"
      );
      while ($field = database::fetch($fields_query)) {
        session::$data['user'][$field['Field']] = null;
      }

      session::$data['user']['permissions'] = array();
    }

    public static function require_login() {
      if (!self::check_login()) {
        //notices::add('warnings', language::translate('warning_must_login_page', 'You must be logged in to view the page.'));
        header('Location: ' . document::link(WS_DIR_ADMIN . 'login.php', array('redirect_url' => $_SERVER['REQUEST_URI'])));
        exit;
      }
    }

    public static function check_login() {
      if (!empty(self::$data['id'])) return true;
    }

    public static function load($user_id) {

      $user_query = database::query(
        "select * from ". DB_TABLE_USERS ."
        where id = ". (int)$user_id ."
        limit 1;"
      );
      $user = database::fetch($user_query);

      $user['permissions'] = @json_decode($user['permissions'], true);

      session::$data['user'] = $user;
    }

    public static function login($username, $password, $redirect_url='', $remember_me=false) {

      try {

        setcookie('remember_me', null, -1, WS_DIR_HTTP_HOME);

        if (empty($username)) throw new Exception(language::translate('error_missing_username', 'You must provide a username'));

        $user_query = database::query(
          "select * from ". DB_TABLE_USERS ."
          where username = '". database::input($username) ."'
          limit 1;"
        );
        $user = database::fetch($user_query);

        if (empty($user)) throw new Exception(language::translate('error_user_not_found', 'The user could not be found in our database'));

        if (empty($user['status'])) throw new Exception(language::translate('error_account_suspended', 'The account is suspended'));

        if (date('Y', strtotime($user['date_valid_to'])) > '1970' && date('Y-m-d H:i:s') > $user['date_valid_to']) {
          throw new Exception(sprintf(language::translate('error_account_expired', 'The account expired %s'), language::strftime(language::$selected['format_datetime'], strtotime($user['date_valid_to']))));
        }

        if (date('Y-m-d H:i:s') < $user['date_valid_from']) {
          throw new Exception(sprintf(language::translate('error_account_is_blocked', 'The account is blocked until %s'), language::strftime(language::$selected['format_datetime'], strtotime($user['date_valid_from']))));
        }

        $user_query = database::query(
          "select * from ". DB_TABLE_USERS ."
          where username = '". database::input($username) ."'
          and password = '". functions::password_checksum($user['id'], $password) ."'
          limit 1;"
        );

        if (!database::num_rows($user_query)) {
          $user['login_attempts']++;


          if ($user['login_attempts'] < 3) {
            $user_query = database::query(
              "update ". DB_TABLE_USERS ."
              set login_attempts = login_attempts + 1
              where id = ". (int)$user['id'] ."
              limit 1;"
            );
            notices::add('errors', sprintf(language::translate('error_d_login_attempts_left', 'You have %d login attempts left until your account is blocked'), $config_login_attempts - $user['login_attempts']));
          } else {
            $user_query = database::query(
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

        if (!empty($user['last_host']) && $user['last_host'] != gethostbyaddr($_SERVER['REMOTE_ADDR'])) {
          notices::add('warnings', strtr(language::translate('warning_account_previously_used_by_another_host', 'Your account was previously used by another location or hostname (%hostname). If this was not you then your login credentials might be compromised.'), array('%hostname' => $user['last_host'])));
        }

        $user_query = database::query(
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

        self::load($user['id']);

        if ($remember_me) {
          $checksum = sha1($user['username'] . $user['password'] . PASSWORD_SALT . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));
          setcookie('remember_me', $user['username'] .':'. $checksum, strtotime('+3 months'), WS_DIR_HTTP_HOME);
        } else {
          setcookie('remember_me', null, -1, WS_DIR_HTTP_HOME);
        }

        if (empty($redirect_url)) $redirect_url = document::link(WS_DIR_ADMIN);

        notices::add('success', str_replace(array('%username'), array(self::$data['username']), language::translate('success_now_logged_in_as', 'You are now logged in as %username')));
        header('Location: '. $redirect_url);
        exit;

      } catch (Exception $e) {
        http_response_code(401);
        notices::add('errors', $e->getMessage());
      }
    }

    public static function logout($redirect_url='') {
      self::reset();

      setcookie('remember_me', null, strtotime('-3 months'), WS_DIR_HTTP_HOME);

      if (empty($redirect_url)) $redirect_url = document::link(WS_DIR_ADMIN . 'login.php');

      header('Location: ' . $redirect_url);
      exit;
    }
  }
