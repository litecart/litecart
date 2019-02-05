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
  }
