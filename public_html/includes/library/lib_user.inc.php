<?php

  class user {

    public static $data;

    public static function init() {

      if (empty(session::$data['user']) || !is_array(session::$data['user'])) {
        self::reset();
      }

    // Bind user to session
      self::$data = &session::$data['user'];

      if (!empty(self::$data['id'])) {

        ini_set('display_errors', 'On');

        database::query(
          "update ". DB_TABLE_USERS ."
          set date_active = '". date('Y-m-d H:i:s') ."'
          where id = ". (int)self::$data['id'] ."
          limit 1;"
        );

        self::load(self::$data['id']);
      }

      if (empty(self::$data['id']) && !empty($_COOKIE['remember_me']) && empty($_POST)) {
        list($username, $key) = explode(':', $_COOKIE['remember_me']);

        $user_query = database::query(
          "select * from ". DB_TABLE_USERS ."
          where lower(username) = lower('". database::input($username) ."')
          and status
          and (date_valid_from is null or date_valid_from < '". date('Y-m-d H:i:s') ."')
          and (date_valid_to is null or year(date_valid_to) < '1971' or date_valid_to > '". date('Y-m-d H:i:s') ."')
          limit 1;"
        );

        if ($user = database::fetch($user_query)) {
          $checksum = sha1($user['username'] . $user['password_hash'] . $_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));

          if ($checksum == $key) {

            self::load($user['id']);

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

          } else {

            if (!empty($_COOKIE['remember_me'])) {
              header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
            }

            if (++$user['login_attempts'] < 3) {
              database::query(
                "update ". DB_TABLE_USERS ."
                set login_attempts = login_attempts + 1
                where id = ". (int)$user['id'] ."
                limit 1;"
              );
            } else {
              database::query(
                "update ". DB_TABLE_USERS ."
                set login_attempts = 0,
                date_valid_from = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
                where id = ". (int)$user['id'] ."
                limit 1;"
              );
            }
          }

        } else {
          header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
        }
      }
    }

    ######################################################################

    public static function reset() {

      session::$data['user'] = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_USERS .";"
      );
      while ($field = database::fetch($fields_query)) {
        session::$data['user'][$field['Field']] = null;
      }

      session::$data['user']['apps'] = [];
      session::$data['user']['widgets'] = [];
    }

    public static function load($user_id) {

      self::reset();

      $user_query = database::query(
        "select * from ". DB_TABLE_USERS ."
        where id = ". (int)$user_id ."
        limit 1;"
      );

      if (!$user = database::fetch($user_query)) {
        throw new Exception('No user found');
      }

      $user['apps'] = $user['apps'] ? json_decode($user['apps'], true) : [];
      $user['widgets'] = $user['widgets'] ? json_decode($user['widgets'], true) : [];

      session::$data['user'] = $user;
    }

    public static function require_login() {
      if (!self::check_login()) {
        //notices::add('warnings', language::translate('warning_must_login_page', 'You must be logged in to view the page.'));
        header('Location: ' . document::link(WS_DIR_ADMIN . 'login.php', ['redirect_url' => $_SERVER['REQUEST_URI']]));
        exit;
      }
    }

    public static function check_login() {
      if (!empty(self::$data['id'])) return true;
    }
  }
