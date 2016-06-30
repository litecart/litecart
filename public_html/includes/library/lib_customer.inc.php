<?php

  class customer {
    public static $data;

    public static function construct() {
    }

    public static function load_dependencies() {
      self::$data = &session::$data['customer'];
    }

    public static function initiate() {

      if (empty(session::$data['customer']) || !is_array(session::$data['customer'])) {
        self::reset();
      }

      if (empty(self::$data['id']) && !empty($_COOKIE['customer_remember_me']) && empty($_POST)) {
        list($email, $key) = explode(':', $_COOKIE['customer_remember_me']);

        $customer_query = database::query(
          "select * from ". DB_TABLE_CUSTOMERS ."
          where email = '". database::input($email) ."'
          limit 1;"
        );
        $customer = database::fetch($customer_query);

        $do_login = false;
        if (!empty($customer)) {
          $checksum = sha1($customer['email'] . $customer['password'] . PASSWORD_SALT . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));
          if ($checksum == $key) $do_login = true;
        }

        if ($do_login) {
          self::load($customer['id']);
        } else {
          setcookie('customer_remember_me', '', 1, WS_DIR_HTTP_HOME);
        }
      }

      self::identify();
    }

    //public static function startup() {
    //}

    public static function before_capture() {

    // Set regional data
      if (!preg_match('#^('. preg_quote(WS_DIR_ADMIN, '#') .')#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
        if (settings::get('regional_settings_screen_enabled')) {
          if (empty(customer::$data['id']) && empty(session::$data['skip_regional_settings_screen']) && empty($_COOKIE['skip_regional_settings_screen'])) {

            functions::draw_fancybox('', array(
              'centerOnScroll' => true,
              'hideOnContentClick' => true,
              'href' => document::ilink('regional_settings', array('redirect' => $_SERVER['REQUEST_URI'])),
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
    }

    //public static function after_capture() {
    //}

    //public static function prepare_output() {
    //}

    public static function before_output() {

      if (!preg_match('#^('. preg_quote(WS_DIR_ADMIN, '#') .')#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
        if (settings::get('regional_settings_screen_enabled')) {
          if (empty(session::$data['skip_regional_settings_screen']) && empty($_COOKIE['skip_regional_settings_screen'])) {
            session::$data['skip_regional_settings_screen'] = true;
            setcookie('skip_regional_settings_screen', true, strtotime('+30 days'), WS_DIR_HTTP_HOME);
          }
        }
      }
    }

    //public static function shutdown() {
    //}

    ######################################################################

    public static function identify() {

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
      if (!in_array(self::$data['shipping_address']['country_code'], $countries)) self::$data['shipping_address']['country_code'] = '';

    // Set country from URI
      if (!empty($_GET['country'])) {
        if (isset($countries[$_GET['country']])) self::$data['country_code'] = $_GET['country'];
      }

    // Set country from cookie
      if (empty(self::$data['country_code'])) {
        if (!empty($_COOKIE['country_code']) && in_array($_COOKIE['country_code'], $countries)) {
          self::$data['country_code'] = $_COOKIE['country_code'];
        }
      }

    // Get country from browser locale (primary)
      if (empty(self::$data['country_code'])) {
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match('#(^[a-z]{2}-([A-Z]{2}))#', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {
          if (!empty($matches[2]) && in_array($matches[2], $countries)) self::$data['country_code'] = $matches[2];
        }
      }

    // Get country from TLD
      if (empty(self::$data['country_code'])) {
        if (preg_match('#\.([a-z]{2})$#', $_SERVER['SERVER_NAME'], $matches)) {
          $countries_query = database::query(
            "select * from ". DB_TABLE_COUNTRIES ."
            where status
            and iso_code_2 = '". database::input(strtoupper($matches[1])) ."'
            limit 1;"
          );
          $country = database::fetch($countries_query);
          if (!empty($country['iso_code_2']) && in_array($country['iso_code_2'], $countries)) self::$data['country_code'] = $country['iso_code_2'];
        }
      }

    // Set default country
      if (empty(self::$data['country_code']) && in_array(settings::get('default_country_code'), $countries)) {
        self::$data['country_code'] = settings::get('default_country_code');
      }

    // Set store country
      if (empty(self::$data['country_code']) && in_array(settings::get('store_country_code'), $countries)) {
        self::$data['country_code'] = settings::get('store_country_code');
      }

    // Set first country in list
      if (empty(self::$data['country_code'])) {
        self::$data['country_code'] = $countries[0]['iso_code_2'];
      }

    // Set zone from cookie
      if (empty(self::$data['zone_code'])) {
        if (!empty($_COOKIE['zone_code'])) {
          self::$data['zone_code'] = $_COOKIE['zone_code'];
        }
      }

    // Set default zone
      if (empty(self::$data['zone_code']) && self::$data['country_code'] == settings::get('default_country_code')) {
        self::$data['zone_code'] = settings::get('default_zone_code');
      }

    // Set store zone
      if (empty(self::$data['zone_code']) && self::$data['country_code'] == settings::get('store_country_code')) {
        self::$data['zone_code'] = settings::get('store_zone_code');
      }

    // Unset zone if not in country
      if (!functions::reference_verify_zone_code(self::$data['country_code'], self::$data['zone_code'])) {
        self::$data['zone_code'] = '';
      }

    // Set shipping country if empty
      if (empty(self::$data['shipping_address']['country_code'])) {
        self::$data['shipping_address']['country_code'] = self::$data['country_code'];
        self::$data['shipping_address']['zone_code'] = self::$data['zone_code'];
      }

    // Set shipping zone if empty
      if (empty(self::$data['shipping_address']['zone_code'])) {
        self::$data['shipping_address']['zone_code'] = self::$data['zone_code'];
      }

    // Unset zone if not in country
      if (!functions::reference_verify_zone_code(self::$data['shipping_address']['country_code'], self::$data['shipping_address']['zone_code'])) {
        self::$data['shipping_address']['zone_code'] = '';
      }

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
        header('Location: ' . document::ilink('login', array('redirect_url' => $_SERVER['REQUEST_URI'])));
        exit;
      }
    }

    public static function check_login() {
      if (!empty(self::$data['id'])) return true;
    }

    public static function password_reset($email) {

      if (empty($email)) {
        notices::add('errors', language::translate('error_missing_email', 'To reset your password you must provide an email address.'));
        return;
      }

      $customer_query = database::query(
        "select * from ". DB_TABLE_CUSTOMERS ."
        where email = '". database::input($email) ."'
        limit 1;"
      );
      $customer = database::fetch($customer_query);

      if (empty($customer)) {
        sleep(rand(3, 10));
        notices::add('errors', language::translate('error_email_not_in_database', 'The email address does not exist in our database.'));
        return;
      }

      $new_password = functions::password_generate(6);

      $customer_query = database::query(
        "update ". DB_TABLE_CUSTOMERS ."
        set password = '". functions::password_checksum($email, $new_password) ."'
        where email = '". database::input($email) ."'
        limit 1;"
      );

      $message = str_replace(array('%email', '%password', '%store_link'), array($email, $new_password, document::ilink('')), language::translate('email_body_password_reset', "We have set a new password for your account at %store_link. Use your email %email and new password %password to log in."));

      functions::email_send(
        null,
        $email,
        language::translate('email_subject_new_password', 'New Password'),
        $message
      );

      notices::add('success', language::translate('success_password_reset', 'A new password has been sent to your email address.'));
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

    public static function login($login, $password, $redirect_url='', $customer_remember_me=false) {

      setcookie('customer_remember_me', '', 1, WS_DIR_HTTP_HOME);

      if (empty($login) || empty($password)) {
        notices::add('errors', language::translate('error_missing_login_credentials', 'You must provide both email address and password.'));
        return;
      }

      $customer_query = database::query(
        "select * from ". DB_TABLE_CUSTOMERS ."
        where email like '". database::input($login) ."'
        limit 1;"
      );
      $customer = database::fetch($customer_query);

      if (empty($customer) || (!empty($customer['password']) && $customer['password'] != functions::password_checksum($customer['email'], $password))) {
        sleep(3);
        notices::add('errors', language::translate('error_login_invalid', 'Wrong password or the account is disabled, or does not exist'));
        return;
      }

      if (empty($customer['status'])) {
        notices::add('errors', language::translate('error_account_inactive', 'Your account is inactive, contact customer support'));
        return;
      }

      if (empty($customer['password'])) {
        $customer['password'] = functions::password_checksum($customer['email'], $password);
        $customer_query = database::query(
          "update ". DB_TABLE_CUSTOMERS ."
          set password = '". database::input($customer['password']) ."'
          where id = ". (int)$customer['id'] ."
          limit 1;"
        );
      }

      self::load($customer['id']);

      session::regenerate_id();

      if ($customer_remember_me) {
        $checksum = sha1($customer['email'] . $customer['password'] . PASSWORD_SALT . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));
        setcookie('customer_remember_me', $customer['email'] .':'. $checksum, strtotime('+1 year'), WS_DIR_HTTP_HOME);
      } else {
        setcookie('customer_remember_me', '', 1, WS_DIR_HTTP_HOME);
      }

      if (empty($redirect_url)) $redirect_url = document::ilink('');

      notices::add('success', str_replace(array('%firstname', '%lastname'), array(self::$data['firstname'], self::$data['lastname']), language::translate('success_logged_in_as_user', 'You are now logged in as %firstname %lastname.')));
      header('Location: '. $redirect_url);
      exit;
    }

    public static function logout($redirect_url='') {

      self::reset();

      cart::reset();
      session::$data['cart']['uid'] = null;

      setcookie('cart[uid]', '', 1, WS_DIR_HTTP_HOME);
      setcookie('customer_remember_me', '', 1, WS_DIR_HTTP_HOME);

      session::regenerate_id();

      notices::add('success', language::translate('description_logged_out', 'You are now logged out.'));

      if (empty($redirect_url)) $redirect_url = document::ilink('');

      header('Location: ' . $redirect_url);
      exit;
    }
  }

?>