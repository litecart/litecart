<?php
  
  class user {
    public static $data;
    
    public static function construct() {
    }
    
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
        database::query(
          "update ". DB_TABLE_USERS ."
          set date_active = '". date('Y-m-d H:i:s') ."'
          where id = '". self::$data['id'] ."'
          limit 1;"
        );
        self::load(self::$data['id']);
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
      session::$data['user'] = array(
        'id' => '',
        'status' => '',
        'username' => '',
        'password' => '',
        'last_ip' => '',
        'last_host' => '',
        'login_attempts' => '',
        'total_logins' => '',
        'date_blocked' => '',
        'date_expires' => '',
        'date_active' => '',
        'date_login' => '',
        'date_updated' => '',
        'date_created' => '',
      );
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
        where id = '". (int)$user_id ."'
        limit 1;"
      );
      $user = database::fetch($user_query);
      
      session::$data['user'] = $user;
    }
    
    public static function login($username, $password, $redirect_url='') {
      $config_login_attempts = 3;
    
      if (empty($username)) {
        notices::add('errors', language::translate('error_missing_username', 'You must provide a username'));
        return;
      }
      
      $user_query = database::query(
        "select * from ". DB_TABLE_USERS ."
        where username = '". database::input($username) ."'
        limit 1;"
      );
      $user = database::fetch($user_query);
      
      if (empty($user)) {
        sleep(10);
        notices::add('errors', language::translate('error_user_not_found', 'The user could not be found in our database'));
        return;
      }
      
      if (empty($user['status'])) {
        notices::add('errors', language::translate('error_account_suspended', 'The account is suspended'));
        return;
      }
      
      if (date('Y', strtotime($user['date_expires'])) > '1970' && date('Y-m-d H:i:s') > $user['date_expires']) {
        notices::add('errors', sprintf(language::translate('error_account_expired', 'The account expired %s'), strftime(language::$selected['format_datetime'], strtotime($user['date_expires']))));
        return;
      }
      
      if (date('Y-m-d H:i:s') < $user['date_blocked']) {
        notices::add('errors', sprintf(language::translate('error_account_is_blocked', 'The account is blocked until %s'), strftime(language::$selected['format_datetime'], strtotime($user['date_blocked']))));
        return;
      }
      
      $user_query = database::query(
        "select * from ". DB_TABLE_USERS ."
        where username = '". database::input($username) ."'
        and password = '". functions::password_checksum($user['id'], $password) ."'
        limit 1;"
      );
      
      if (!database::num_rows($user_query)) {
        $user['login_attempts']++;
        
        notices::add('errors', language::translate('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'));
        
        if ($user['login_attempts'] < $config_login_attempts) {
          $user_query = database::query(
            "update ". DB_TABLE_USERS ."
            set login_attempts = login_attempts + 1
            where id = '". (int)$user['id'] ."'
            limit 1;"
          );
          notices::add('errors', sprintf(language::translate('error_d_login_attempts_left', 'You have %d login attempts left until your account is blocked'), $config_login_attempts - $user['login_attempts']));
        } else {
          $user_query = database::query(
            "update ". DB_TABLE_USERS ."
            set login_attempts = 0,
            date_blocked = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
            where id = '". (int)$user['id'] ."'
            limit 1;"
          );
          notices::add('errors', sprintf(language::translate('error_account_has_been_blocked', 'The account has been temporary blocked %d minutes'), 15));
        }
        
        sleep(10);
        return;
      }
      
      $user_query = database::query(
        "update ". DB_TABLE_USERS ."
        set
          last_ip = '". database::input($_SERVER['REMOTE_ADDR']) ."',
          last_host = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
          login_attempts = 0,
          total_logins = total_logins + 1,
          date_login = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$user['id'] ."'
        limit 1;"
      );
      
      self::load($user['id']);
      
      if (empty($redirect_url)) $redirect_url = document::link(WS_DIR_ADMIN);
      
      notices::add('success', str_replace(array('%username'), array(self::$data['username']), language::translate('success_now_logged_in_as', 'You are now logged in as %username')));
      header('Location: '. $redirect_url);
      exit;
    }
    
    public static function logout($redirect_url='') {
      self::reset();
      
      if (empty($redirect_url)) $redirect_url = document::link(WS_DIR_ADMIN . 'login.php');
      
      header('Location: ' . $redirect_url);
      exit;
    }

  }
  
?>