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
      
      self::identify();
    }
    
    public static function before_capture() {
    
    // Set regional data
      if (settings::get('regional_settings_screen_enabled')) {
        if (empty(customer::$data['id']) && empty(session::$data['skip_set_region_data']) && empty($_COOKIE['skip_set_region_data'])) {
          
          functions::draw_fancybox('', array(
            'centerOnScroll' => true,
            'hideOnContentClick' => true,
            'href' => document::link(WS_DIR_HTTP_HOME . 'select_region.php', array('redirect' => $_SERVER['REQUEST_URI'])),
            //'modal' => true,
            'speedIn' => 600,
            'transitionIn' => 'fade',
            'transitionOut' => 'fade',
            'type' => 'ajax',
            'scrolling' => 'false',
          ));
        }
      }
    }
    
    //public static function after_capture() {
    //}
    
    //public static function prepare_output() {
    //}
    
    public static function before_output() {
      
      if (settings::get('regional_settings_screen_enabled')) {
        if (empty(session::$data['skip_set_region_data']) && empty($_COOKIE['skip_set_region_data'])) {
          session::$data['skip_set_region_data'] = true;
          setcookie('skip_set_region_data', true, time() + (60*60*24*10), WS_DIR_HTTP_HOME);
        }
      }
    }
    
    //public static function shutdown() {
    //}
    
    ######################################################################
    
    public static function identify() {
      
    // Set country from cookie
      if (empty(self::$data['country_code'])) {
        if (!empty($_COOKIE['country_code'])) {
          self::$data['country_code'] = $_COOKIE['country_code'];
        }
      }
      
    // Set country from browser
      if (empty(self::$data['country_code'])) {
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
          if (preg_match('/-([A-Z]{2})/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {
            if (!empty($matches[1])) self::$data['country_code'] = $matches[1];
          }
        }
      }

    // Build list of supported countries
      $countries_query = database::query(
        "select * from ". DB_TABLE_COUNTRIES ."
        where status;"
      );
      
      $countries = array();
      while ($country = database::fetch($countries_query)) {
        $countries[] = $country['iso_code_2'];
      }
      
    // Unset non supported country
      if (!in_array(self::$data['country_code'], $countries)) self::$data['country_code'] = '';
      
    // Set default country
      if (empty(self::$data['country_code'])) {
        self::$data['country_code'] = settings::get('default_country_code');
      }
      
    // Set zone from cookie
      if (empty(self::$data['zone_code'])) {
        if (!empty($_COOKIE['zone_code'])) {
          self::$data['zone_code'] = $_COOKIE['zone_code'];
        }
      }
      
      if (empty(self::$data['zone_code'])) {
        self::$data['zone_code'] = settings::get('default_zone_code');
      }
      
    // Unset zone if not in country
      if (!functions::reference_verify_zone_code(self::$data['country_code'], self::$data['zone_code'])) {
        self::$data['zone_code'] = '';
      }
      
      self::$data['shipping_address']['country_code'] = self::$data['country_code'];
      self::$data['shipping_address']['zone_code'] = self::$data['zone_code'];
      
    // Set tax from cookie
      if (!isset(self::$data['display_prices_including_tax']) || self::$data['display_prices_including_tax'] === null) {
        if (isset($_COOKIE['display_prices_including_tax'])) self::$data['display_prices_including_tax'] = !empty($_COOKIE['display_prices_including_tax']) ? 1 : 0;
      }
    
    // Set default tax
      if (!isset(self::$data['display_prices_including_tax']) || self::$data['display_prices_including_tax'] === null) {
        self::$data['display_prices_including_tax'] = settings::get('default_display_prices_including_tax') ? 1 : 0;
      }
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
        'country_code' => '',
        'zone_code' => '',
        'different_shipping_address' => false,
        'shipping_address' => array(
          'company' => '',
          'firstname' => '',
          'lastname' => '',
          'address1' => '',
          'address2' => '',
          'city' => '',
          'postcode' => '',
          'country_code' => '',
          'zone_code' => '',
        ),
        'display_prices_including_tax' => null,
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
        sleep(5);
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
      
      if (!empty(self::$data['different_shipping_address'])) {
        foreach ($key_map as $skey => $tkey){
        self::$data['shipping_address'][$tkey] = self::$data[$skey];
        unset(self::$data[$skey]);
        }
      } else {
        foreach ($key_map as $skey => $tkey){
          self::$data['shipping_address'][$tkey] = self::$data[$tkey];
          unset(self::$data[$skey]);
        }
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