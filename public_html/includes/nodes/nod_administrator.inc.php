<?php

  class administrator {

    public static $data;

    public static function init() {

      if (empty(session::$data['administrator']) || !is_array(session::$data['administrator'])) {
        self::reset();
      }

    // Bind administrator to session
      self::$data = &session::$data['administrator'];

    // Login remembered administrator automatically
      if (empty(self::$data['id']) && !empty($_COOKIE['remember_me']) && empty($_POST)) {

        try {

          list($username, $key) = explode(':', $_COOKIE['remember_me']);

          $administrator = database::query(
            "select * from ". DB_TABLE_PREFIX ."administrators
            where lower(username) = lower('". database::input($username) ."')
            and status
            and (date_valid_from is null or date_valid_from < '". date('Y-m-d H:i:s') ."')
            and (date_valid_to is null or date_valid_to > '". date('Y-m-d H:i:s') ."')
            limit 1;"
          )->fetch();

          if (!$administrator) {
            throw new Exception('Invalid email or the account has been removed');
          }

          $checksum = sha1($administrator['username'] . $administrator['password_hash'] . $_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));

          if ($checksum != $key) {

            if (++$administrator['login_attempts'] < 3) {
              database::query(
                "update ". DB_TABLE_PREFIX ."administrators
                set login_attempts = login_attempts + 1
                where id = ". (int)$administrator['id'] ."
                limit 1;"
              );
            } else {
              database::query(
                "update ". DB_TABLE_PREFIX ."administrators
                set login_attempts = 0,
                date_valid_from = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
                where id = ". (int)$administrator['id'] ."
                limit 1;"
              );
            }

            throw new Exception('Invalid checksum for cookie');
          }

          self::load($administrator['id']);

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

        } catch (Exception $e) {
          header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
        }
      }

      if (!empty(self::$data['id'])) {

        $administrator = database::query(
          "select * from ". DB_TABLE_PREFIX ."administrators
          where id = ". (int)self::$data['id'] ."
          limit 1;"
        )->fetch();

        if (!$administrator) {
          self::reset();
          die('The account has been removed');
        }

        if (!$administrator['status']) {
          if (!empty($_COOKIE['remember_me'])) {
            header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax');
          }
          self::reset();
          die('Your account is disabled');
        }

        ini_set('display_errors', 'On');

        database::query(
          "update ". DB_TABLE_PREFIX ."administrators
          set date_active = '". date('Y-m-d H:i:s') ."'
          where id = ". (int)self::$data['id'] ."
          limit 1;"
        );

        if (!empty($administrator['date_expire_sessions'])) {
          if (!isset(session::$data['administrator_security_timestamp']) || session::$data['administrator_security_timestamp'] < strtotime($administrator['date_expire_sessions'])) {
            self::reset();
            notices::add('errors', language::translate('error_session_expired_due_to_account_changes', 'Session expired due to changes in the account'));
            header('Location: '. document::ilink('login'));
            exit;
          }
        }
      }
    }

    ######################################################################

    public static function reset() {

      $administrator = database::query(
        "show fields from ". DB_TABLE_PREFIX ."administrators;"
      )->each(function($field) use ($administrator) {
        $administrator[$field['Field']] = database::create_variable($field);
      });

      $administrator['apps'] = [];
      $administrator['widgets'] = [];

      session::$data['administrator'] = $administrator;
    }

    public static function load($administrator_id) {

      self::reset();

      $administrator = database::query(
        "select * from ". DB_TABLE_PREFIX ."administrators
        where id = ". (int)$administrator_id ."
        limit 1;"
      )->fetch();

      if (!$administrator) {
        throw new Exception('No administrator found');
      }

      $administrator['apps'] = $administrator['apps'] ? json_decode($administrator['apps'], true) : [];
      $administrator['widgets'] = $administrator['widgets'] ? json_decode($administrator['widgets'], true) : [];

      session::$data['administrator'] = $administrator;
    }

    public static function require_login() {
      if (!self::check_login()) {
        //notices::add('warnings', language::translate('warning_must_login_page', 'You must be logged in to view the page.'));
        $redirect_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        header('Location: ' . document::ilink('b:login', ['redirect_url' => $redirect_url]));
        exit;
      }
    }

    public static function check_login() {
      if (!empty(self::$data['id'])) return true;
    }
  }
