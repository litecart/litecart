<?php
  
  class customer {
    public static $data;
    
    public static function construct() {
    }
    
    public static function load_dependencies() {
      self::$data = &session::$data['customer'];
    }
    
    //public static function initiate() {
    //}
    
    public static function startup() {
      
      if (empty(session::$data['customer']) || !is_array(session::$data['customer'])) {
        self::reset();
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
    
    public static function identify() {
      
    // Build list of supported countries
      $countries_query = database::query(
        "select * from ". DB_TABLE_COUNTRIES ."
        where iso_code_2 = '". database::input(settings::get('default_country_code')) ."'
        limit 1;"
      );
      $country = database::fetch($countries_query);
      
      $countries = array();
      while ($country = database::fetch($countries_query)) {
        if ($country['status']) {
          $countries[] = $country['iso_code_2'];
        }
      }
      
    // Return country from cookie
      if (isset($_COOKIE['country_code']) && in_array($_COOKIE['country_code'], $countries)) return $_COOKIE['country_code'];
      
    // Return country from browser
      if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browser_locales = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
      } elseif (isset($_SERVER['LC_CTYPE'])) {
        $browser_locales = explode(',', $_SERVER['LC_CTYPE']);
      } else {
        $browser_locales = array();
      }
      foreach ($browser_locales as $browser_locale) {
        if (preg_match('/('. implode('|', $countries) .')-?.*/', $browser_locale, $reg)) {
          if (!empty($reg[1])) return $reg[1];
        }
      }
      
    // Return default country
      return settings::get('default_country_code');
    }
    
    public static function reset() {
      session::$data['customer'] = array(
        'id' => '',
        'email' => '',
        'tax_id' => '',
        'phone' => '',
        'mobile' => '',
        'company' => '',
        'firstname' => '',
        'lastname' => '',
        'address1' => '',
        'address2' => '',
        'city' => '',
        'postcode' => '',
        'country_code' => settings::get('default_country_code'),
        'zone_code' => settings::get('default_zone_code'),
        'different_shipping_address' => false,
        'shipping_address' => array(
          'company' => '',
          'firstname' => '',
          'lastname' => '',
          'address1' => '',
          'address2' => '',
          'city' => '',
          'postcode' => '',
          'country_code' => settings::get('default_country_code'),
          'zone_code' => settings::get('default_zone_code'),
        ),
      );
    }
    
    public static function require_login() {
      if (!self::check_login()) {
        notices::add('warnings', language::translate('warning_must_login_page', 'You must be logged in to view the page.'));
        header('Location: ' . document::link(WS_DIR_HTTP_HOME));
        exit;
      }
    }
    
    public static function check_login() {
      if (!empty(self::$data['id'])) return true;
    }
    
    public static function password_reset($email) {
      
      if (empty($email)) {
        notices::add('errors', language::translate('error_missing_email', 'To reset your password you must provide an e-mail address.'));
        return;
      }

      $customer_query = database::query(
        "select * from ". DB_TABLE_CUSTOMERS ."
        where email = '". database::input($email) ."'
        limit 1;"
      );
      $customer = database::fetch($customer_query);
      
      if (empty($customer)) {
        sleep(10);
        notices::add('errors', language::translate('error_email_not_in_database', 'The e-mail address does not exist in our database.'));
        return;
      }
      
      $new_password = functions::password_generate(6);
      
      $customer_query = database::query(
        "update ". DB_TABLE_CUSTOMERS ."
        set password = '". functions::password_checksum($email, $new_password) ."'
        where email = '". database::input($email) ."'
        limit 1;"
      );
      
      $message = str_replace(array('%email', '%password', '%store_link'), array($email, $new_password, document::link(WS_DIR_HTTP_HOME)), language::translate('email_body_password_reset', "We have set a new password for your account.\n\nLogin: %email\nPassword: %password\n\n%store_link"));
      
      functions::email_send(
        settings::get('store_email'),
        $email,
        language::translate('email_subject_new_password', 'New Password'),
        $message
      );
      
      notices::add('success', language::translate('success_password_reset', 'A new password has been sent to your e-mail address.'));
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;
    }
    
    public static function load($customer_id) {
      
      $customer_query = database::query(
        "select * from ". DB_TABLE_CUSTOMERS ."
        where id = '". (int)$customer_id ."'
        limit 1;"
      );
      $customer = database::fetch($customer_query);
      
      session::$data['customer'] = $customer;
      
      $key_map = array(
        'shipping_company' => 'company',
        'shipping_firstname' => 'firstname',
        'shipping_lastname' => 'lastname',
        'shipping_address1' => 'address1',
        'shipping_address2' => 'address2',
        'shipping_postcode' => 'postcode',
        'shipping_city' => 'city',
        'shipping_country_code' => 'country_code',
        'shipping_zone_code' => 'zone_code',
      );
      foreach ($key_map as $skey => $tkey){
        self::$data['shipping_address'][$tkey] = self::$data[$skey];
        unset(self::$data[$skey]);
      }
    }
    
    public static function login($email, $password, $redirect_url='') {
    
      if (empty($email) || empty($password)) {
        notices::add('errors', language::translate('error_missing_login_credentials', 'You must provide both e-mail address and password.'));
        return;
      }
      
      $customer_query = database::query(
        "select * from ". DB_TABLE_CUSTOMERS ."
        where email = '". database::input($email) ."'
        and password = '". functions::password_checksum($email, $password) ."'
        limit 1;"
      );
      $customer = database::fetch($customer_query);
      
      if (empty($customer)) {
        sleep(10);
        notices::add('errors', language::translate('error_login_incorrect', 'Wrong e-mail and password combination or the account does not exist.'));
        return;
      }
      
      self::load($customer['id']);
      
      session::regenerate_id();
      
      cart::load();
      
      if (empty($redirect_url)) $redirect_url = document::link(WS_DIR_HTTP_HOME);
      
      notices::add('success', str_replace(array('%firstname', '%lastname'), array(self::$data['firstname'], self::$data['lastname']), language::translate('success_welcome_back_user', 'Welcome back %firstname %lastname.')));
      header('Location: '. $redirect_url);
      exit;
    }
    
    public static function logout($redirect_url='') {
      self::reset();
      cart::reset();
      
      session::regenerate_id();
      
      notices::add('success', language::translate('description_logged_out', 'You are now logged out.'));
      
      if ($redirect_url) {
        header('Location: ' . $redirect_url);
        exit;
      }
    }
  }
  
?>