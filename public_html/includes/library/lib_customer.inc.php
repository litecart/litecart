<?php
  
  class lib_customer {
    public $data;
    
    public function __construct(&$system) {
    }
    
    public function load_dependencies() {
      $this->data = &$GLOBALS['system']->session->data['customer'];
    }
    
    //public function initiate() {
    //}
    
    public function startup() {
      
      if (empty($GLOBALS['system']->session->data['customer']) || !is_array($GLOBALS['system']->session->data['customer'])) {
        $this->reset();
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
    
    public function identify() {
      
    // Build list of supported countries
      $countries_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_COUNTRIES ."
        where iso_code_2 = '". $GLOBALS['system']->database->input($GLOBALS['system']->settings->get('default_country_code')) ."'
        limit 1;"
      );
      $country = $GLOBALS['system']->database->fetch($countries_query);
      
      $countries = array();
      while ($country = $GLOBALS['system']->database->fetch($countries_query)) {
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
      return $GLOBALS['system']->settings->get('default_country_code');
    }
    
    public function reset() {
      $GLOBALS['system']->session->data['customer'] = array(
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
        'country_code' => $GLOBALS['system']->settings->get('default_country_code'),
        'zone_code' => $GLOBALS['system']->settings->get('default_zone_code'),
        'different_shipping_address' => false,
        'shipping_address' => array(
          'company' => '',
          'firstname' => '',
          'lastname' => '',
          'address1' => '',
          'address2' => '',
          'city' => '',
          'postcode' => '',
          'country_code' => $GLOBALS['system']->settings->get('default_country_code'),
          'zone_code' => $GLOBALS['system']->settings->get('default_zone_code'),
        ),
      );
    }
    
    public function require_login() {
      if (!$this->check_login()) {
        $GLOBALS['system']->notices->add('warnings', $GLOBALS['system']->language->translate('warning_must_login_page', 'You must be logged in to view the page.'));
        header('Location: ' . $GLOBALS['system']->document->link(WS_DIR_HTTP_HOME));
        exit;
      }
    }
    
    public function check_login() {
      if (!empty($this->data['id'])) return true;
    }
    
    public function password_reset($email) {
      
      if (empty($email)) {
        $GLOBALS['system']->notices->add('errors', $GLOBALS['system']->language->translate('error_missing_email', 'To reset your password you must provide an e-mail address.'));
        return;
      }

      $customer_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_CUSTOMERS ."
        where email = '". $GLOBALS['system']->database->input($email) ."'
        limit 1;"
      );
      $customer = $GLOBALS['system']->database->fetch($customer_query);
      
      if (empty($customer)) {
        sleep(10);
        $GLOBALS['system']->notices->add('errors', $GLOBALS['system']->language->translate('error_email_not_in_database', 'The e-mail address does not exist in our database.'));
        return;
      }
      
      $new_password = $GLOBALS['system']->functions->password_generate(6);
      
      $customer_query = $GLOBALS['system']->database->query(
        "update ". DB_TABLE_CUSTOMERS ."
        set password = '". $GLOBALS['system']->functions->password_checksum($email, $new_password) ."'
        where email = '". $GLOBALS['system']->database->input($email) ."'
        limit 1;"
      );
      
      $message = str_replace(array('%email', '%password', '%store_link'), array($email, $new_password, $GLOBALS['system']->document->link(WS_DIR_HTTP_HOME)), $GLOBALS['system']->language->translate('email_body_password_reset', "We have set a new password for your account.\n\nLogin: %email\nPassword: %password\n\n%store_link"));
      
      $GLOBALS['system']->functions->email_send(
        $GLOBALS['system']->settings->get('store_email'),
        $email,
        $GLOBALS['system']->language->translate('email_subject_new_password', 'New Password'),
        $message
      );
      
      $GLOBALS['system']->notices->add('success', $GLOBALS['system']->language->translate('success_password_reset', 'A new password has been sent to your e-mail address.'));
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;
    }
    
    public function load($customer_id) {
      
      $customer_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_CUSTOMERS ."
        where id = '". (int)$customer_id ."'
        limit 1;"
      );
      $customer = $GLOBALS['system']->database->fetch($customer_query);
      
      $GLOBALS['system']->session->data['customer'] = $customer;
      
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
      
      if (!empty($this->data['different_shipping_address'])) {
        foreach ($key_map as $skey => $tkey){
          $this->data['shipping_address'][$tkey] = $this->data[$skey];
          unset($this->data[$skey]);
        }
      } else {
        foreach ($key_map as $skey => $tkey){
          $this->data['shipping_address'][$tkey] = $this->data[$tkey];
          unset($this->data[$skey]);
        }
      }
    }
    
    public function login($email, $password, $redirect_url='') {
    
      if (empty($email) || empty($password)) {
        $GLOBALS['system']->notices->add('errors', $GLOBALS['system']->language->translate('error_missing_login_credentials', 'You must provide both e-mail address and password.'));
        return;
      }
      
      $customer_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_CUSTOMERS ."
        where email = '". $GLOBALS['system']->database->input($email) ."'
        and password = '". $GLOBALS['system']->functions->password_checksum($email, $password) ."'
        limit 1;"
      );
      $customer = $GLOBALS['system']->database->fetch($customer_query);
      
      if (empty($customer)) {
        sleep(10);
        $GLOBALS['system']->notices->add('errors', $GLOBALS['system']->language->translate('error_login_incorrect', 'Wrong e-mail and password combination or the account does not exist.'));
        return;
      }
      
      $this->load($customer['id']);
      
      $GLOBALS['system']->session->regenerate_id();
      
      $GLOBALS['system']->cart->load();
      
      if (empty($redirect_url)) $redirect_url = $GLOBALS['system']->document->link(WS_DIR_HTTP_HOME);
      
      $GLOBALS['system']->notices->add('success', str_replace(array('%firstname', '%lastname'), array($this->data['firstname'], $this->data['lastname']), $GLOBALS['system']->language->translate('success_welcome_back_user', 'Welcome back %firstname %lastname.')));
      header('Location: '. $redirect_url);
      exit;
    }
    
    public function logout($redirect_url='') {
      $this->reset();
      $GLOBALS['system']->cart->reset();
      
      $GLOBALS['system']->session->regenerate_id();
      
      $GLOBALS['system']->notices->add('success', $GLOBALS['system']->language->translate('description_logged_out', 'You are now logged out.'));
      
      if ($redirect_url) {
        header('Location: ' . $redirect_url);
        exit;
      }
    }
  }
  
?>