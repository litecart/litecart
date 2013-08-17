<?php
  
  class lib_user {
    public $data;
    
    public function __construct() {
    }
    
    public function load_dependencies() {
      $this->data = &$GLOBALS['system']->session->data['user'];
    }
    
    //public function initiate() {
    //}
    
    public function startup() {
    
      if (empty($GLOBALS['system']->session->data['user']) || !is_array($GLOBALS['system']->session->data['user'])) {
        $this->reset();
      }
      
      if (!empty($this->data['id'])) {
        $GLOBALS['system']->database->query(
          "update ". DB_TABLE_USERS ."
          set date_active = '". date('Y-m-d H:i:s') ."'
          where id = '". $this->data['id'] ."'
          limit 1;"
        );
        $this->load($this->data['id']);
      }
    }
    
    //public function before_capture() {
    //}
    
    //public function after_capture() {
    //}
    
    //public function prepare_output() {
    //}
    
    //public function before_output() {
    //}
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function reset() {
      $GLOBALS['system']->session->data['user'] = array(
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
    
    public function require_login() {
      if (!$this->check_login()) {
        //$GLOBALS['system']->notices->add('warnings', $GLOBALS['system']->language->translate('warning_must_login_page', 'You must be logged in to view the page.'));
        header('Location: ' . $GLOBALS['system']->document->link(WS_DIR_ADMIN . 'login.php', array('redirect_url' => $_SERVER['REQUEST_URI'])));
        exit;
      }
    }
    
    public function check_login() {
      if (!empty($this->data['id'])) return true;
    }
    
    public function load($user_id) {
      
      $user_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_USERS ."
        where id = '". (int)$user_id ."'
        limit 1;"
      );
      $user = $GLOBALS['system']->database->fetch($user_query);
      
      $GLOBALS['system']->session->data['user'] = $user;
    }
    
    public function login($username, $password, $redirect_url='') {
      $config_login_attempts = 3;
    
      if (empty($username)) {
        $GLOBALS['system']->notices->add('errors', $GLOBALS['system']->language->translate('error_missing_username', 'You must provide a username'));
        return;
      }
      
      $user_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_USERS ."
        where username = '". $GLOBALS['system']->database->input($username) ."'
        limit 1;"
      );
      $user = $GLOBALS['system']->database->fetch($user_query);
      
      if (empty($user)) {
        sleep(10);
        $GLOBALS['system']->notices->add('errors', $GLOBALS['system']->language->translate('error_user_not_found', 'The user could not be found in our database'));
        return;
      }
      
      if (empty($user['status'])) {
        $GLOBALS['system']->notices->add('errors', $GLOBALS['system']->language->translate('error_account_suspended', 'The account is suspended'));
        return;
      }
      
      if (date('Y-m-d', strtotime($user['date_expires'])) > '1970' && date('Y-m-d H:i:s') > $user['date_expires']) {
        $GLOBALS['system']->notices->add('errors', sprintf($GLOBALS['system']->language->translate('error_account_expired', 'The account expired %s'), strftime($GLOBALS['system']->language->selected['format_datetime'], strtotime($user['date_expires']))));
        return;
      }
      
      if (date('Y-m-d H:i:s') < $user['date_blocked']) {
        $GLOBALS['system']->notices->add('errors', sprintf($GLOBALS['system']->language->translate('error_account_is_blocked', 'The account is blocked until %s'), strftime($GLOBALS['system']->language->selected['format_datetime'], strtotime($user['date_blocked']))));
        return;
      }
      
      $user_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_USERS ."
        where username = '". $GLOBALS['system']->database->input($username) ."'
        and password = '". $GLOBALS['system']->functions->password_hash($user['id'], $password) ."'
        limit 1;"
      );
      
      if (!$GLOBALS['system']->database->num_rows($user_query)) {
        $user['login_attempts']++;
        
        $GLOBALS['system']->notices->add('errors', $GLOBALS['system']->language->translate('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'));
        
        if ($user['login_attempts'] < $config_login_attempts) {
          $user_query = $GLOBALS['system']->database->query(
            "update ". DB_TABLE_USERS ."
            set login_attempts = login_attempts + 1
            where id = '". (int)$user['id'] ."'
            limit 1;"
          );
          $GLOBALS['system']->notices->add('errors', sprintf($GLOBALS['system']->language->translate('error_d_login_attempts_left', 'You have %d login attempts left until your account is blocked'), $config_login_attempts - $user['login_attempts']));
        } else {
          $user_query = $GLOBALS['system']->database->query(
            "update ". DB_TABLE_USERS ."
            set login_attempts = 0,
            date_blocked = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
            where id = '". (int)$user['id'] ."'
            limit 1;"
          );
          $GLOBALS['system']->notices->add('errors', sprintf($GLOBALS['system']->language->translate('error_account_has_been_blocked', 'The account has been temporary blocked %d minutes'), 15));
        }
        
        sleep(10);
        return;
      }
      
      $user_query = $GLOBALS['system']->database->query(
        "update ". DB_TABLE_USERS ."
        set
          last_ip = '". $GLOBALS['system']->database->input($_SERVER['REMOTE_ADDR']) ."',
          last_host = '". $GLOBALS['system']->database->input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
          login_attempts = 0,
          total_logins = total_logins + 1,
          date_login = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$user['id'] ."'
        limit 1;"
      );
      
      $this->load($user['id']);
      
      if (empty($redirect_url)) $redirect_url = $GLOBALS['system']->document->link(WS_DIR_ADMIN);
      
      $GLOBALS['system']->notices->add('success', str_replace(array('%username'), array($this->data['username']), $GLOBALS['system']->language->translate('success_now_logged_in_as', 'You are now logged in as %username')));
      header('Location: '. $redirect_url);
      exit;
    }
    
    public function logout($redirect_url='') {
      $this->reset();
      
      if (empty($redirect_url)) $redirect_url = $GLOBALS['system']->document->link(WS_DIR_ADMIN . 'login.php');
      
      header('Location: ' . $redirect_url);
      exit;
    }

  }
  
?>