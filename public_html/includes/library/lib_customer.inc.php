<?php

  class customer {
    public static $data;

    public static function init() {

      if (empty(session::$data['customer']) || !is_array(session::$data['customer'])) {
        self::reset();
      }

    // Bind customer to session
      self::$data = &session::$data['customer'];

    // Login remembered customer automatically
      if (empty(self::$data['id']) && !empty($_COOKIE['customer_remember_me']) && empty($_POST)) {

        try {

          list($email, $key) = explode(':', $_COOKIE['customer_remember_me']);

          $customer_query = database::query(
            "select * from ". DB_TABLE_PREFIX ."customers
            where email = '". database::input($email) ."'
            limit 1;"
          );

          if (!$customer = database::fetch($customer_query)) {
            throw new Exception('Invalid email or the account has been removed');
          }

          $checksum = sha1($customer['email'] . $customer['password_hash'] . $_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));

          if ($checksum != $key) {
            if (++$customer['login_attempts'] < 3) {
              database::query(
                "update ". DB_TABLE_PREFIX ."customers
                set login_attempts = login_attempts + 1
                where id = ". (int)$customer['id'] ."
                limit 1;"
              );
            } else {
              database::query(
                "update ". DB_TABLE_PREFIX ."customers
                set login_attempts = 0,
                date_blocked_until = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
                where id = ". (int)$customer['id'] ."
                limit 1;"
              );
            }

            throw new Exception('Invalid checksum for cookie');
          }

          self::load($customer['id']);

          database::query(
            "update ". DB_TABLE_PREFIX ."customers
            set
              last_ip = '". database::input($_SERVER['REMOTE_ADDR']) ."',
              last_host = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
              login_attempts = 0,
              total_logins = total_logins + 1,
              date_login = '". date('Y-m-d H:i:s') ."'
            where id = ". (int)$customer['id'] ."
            limit 1;"
          );

        } catch (Exception $e) {
          header('Set-Cookie: customer_remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
        }
      }

      if (!empty(self::$data['id'])) {

        $customer_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."customers
          where id = ". (int)self::$data['id'] ."
          limit 1;"
        );

        if (!$customer = database::fetch($customer_query)) {
          die('The account has been removed');
        }

        if (!$customer['status']) {
          if (!empty($_COOKIE['customer_remember_me'])) {
            header('Set-Cookie: customer_remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax');
          }
          self::reset();
          die('Your account is disabled');
        }

        if (!empty($customer['date_expire_sessions'])) {
          if (!isset(session::$data['customer_security_timestamp']) || session::$data['customer_security_timestamp'] < strtotime($customer['date_expire_sessions'])) {
            self::reset();
            notices::add('errors', language::translate('error_session_expired_due_to_account_changes', 'Session expired due to changes in the account'));
            header('Location: '. document::ilink('login'));
            exit;
          }
        }
      }

      self::identify();

      event::register('after_capture', [__CLASS__, 'after_capture']);
      event::register('before_output', [__CLASS__, 'before_output']);
    }

    public static function after_capture() {

    // Load regional settings screen
      if (!preg_match('#^('. preg_quote(WS_DIR_ADMIN, '#') .')#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
        if (settings::get('regional_settings_screen')) {
          if (empty(customer::$data['id']) && empty(session::$data['skip_regional_settings_screen']) && empty($_COOKIE['skip_regional_settings_screen'])) {
            functions::draw_lightbox(document::ilink('regional_settings'), ['seamless' => true]);
          }
        }
      }
    }

    public static function before_output() {

      if (!preg_match('#^('. preg_quote(WS_DIR_ADMIN, '#') .')#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
        if (settings::get('regional_settings_screen')) {
          if (empty(session::$data['skip_regional_settings_screen']) && empty($_COOKIE['skip_regional_settings_screen'])) {
            session::$data['skip_regional_settings_screen'] = true;
            if (!empty($_COOKIE['cookies_accepted']) || !settings::get('cookie_policy')) {
              header('Set-Cookie: skip_regional_settings_screen=1; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; SameSite=Lax', false);
            }
          }
        }
      }
    }

    ######################################################################

    public static function identify() {

    // Build list of supported countries
      $countries_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."countries
        where status;"
      );

      $countries = [];
      while ($country = database::fetch($countries_query)) {
        $countries[] = $country['iso_code_2'];
      }

    // Unset non supported country
      if (!in_array(self::$data['country_code'], $countries)) self::$data['country_code'] = '';
      if (!in_array(self::$data['shipping_address']['country_code'], $countries)) self::$data['shipping_address']['country_code'] = '';

    // Set country from URI
      if (!empty($_GET['country'])) {
        if (in_array($_GET['country'], $countries)) self::$data['country_code'] = $_GET['country'];
      }

    // Set country from cookie
      if (empty(self::$data['country_code'])) {
        if (!empty($_COOKIE['country_code']) && in_array($_COOKIE['country_code'], $countries)) {
          self::$data['country_code'] = $_COOKIE['country_code'];
        }
      }

    // Get country from TLD
      if (empty(self::$data['country_code'])) {
        if (preg_match('#\.([a-z]{2})$#', $_SERVER['HTTP_HOST'], $matches)) {
          $matches[1] = strtr(strtoupper($matches[1]), [
            'UK' => 'GB', // ccTLD .uk is not a country
            'SU' => 'RU', // ccTLD .su is not a country
          ]);
          $countries_query = database::query(
            "select * from ". DB_TABLE_PREFIX ."countries
            where status
            and iso_code_2 = '". database::input(strtoupper($matches[1])) ."'
            limit 1;"
          );
          $country = database::fetch($countries_query);
          if (!empty($country['iso_code_2'])) self::$data['country_code'] = $country['iso_code_2'];
        }
      }

    // Get country from HTTP header (CloudFlare)
      if (empty(self::$data['country_code'])) {
        if (!empty($_SERVER['HTTP_CF_IPCOUNTRY']) && in_array($_SERVER['HTTP_CF_IPCOUNTRY'], $countries)) {
          self::$data['country_code'] = $_SERVER['HTTP_CF_IPCOUNTRY'];
        }
      }

    // Get country from browser locale
      if (empty(self::$data['country_code'])) {
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match('#(^[a-z]{2}-([a-z]{2}))#i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {
          if (!empty($matches[2]) && in_array(strtoupper($matches[2]), $countries)) self::$data['country_code'] = strtoupper($matches[2]);
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
        self::$data['country_code'] = $countries[0];
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
      if (!empty(self::$data['zone_code']) && empty(reference::country(self::$data['country_code'])->zones[self::$data['zone_code']])) {
        self::$data['zone_code'] = '';
      }

    // Set first zone in country
      if (empty(self::$data['zone_code']) && !empty(reference::country(self::$data['country_code'])->zones)) {
        self::$data['zone_code'] = array_keys(reference::country(self::$data['country_code'])->zones)[0];
      }

    // Set shipping country if empty
      if (empty(self::$data['shipping_address']['country_code'])) {
        self::$data['shipping_address']['country_code'] = self::$data['country_code'];
        self::$data['shipping_address']['zone_code'] = self::$data['zone_code'];
      }

    // Unset zone if not in country
      if (!isset(reference::country(self::$data['shipping_address']['country_code'])->zones[self::$data['shipping_address']['zone_code']])) {
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

      session::$data['customer'] = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."customers;"
      );

      while ($field = database::fetch($fields_query)) {
        if (preg_match('#^shipping_(.*)$#', $field['Field'], $matches)) {
          session::$data['customer']['shipping_address'][$matches[1]] = null;
        } else {
          session::$data['customer'][$field['Field']] = null;
        }
      }

      session::$data['customer']['display_prices_including_tax'] = null;
    }

    public static function load($customer_id) {

      self::reset();

      $customer_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."customers
        where id = ". (int)$customer_id ."
        limit 1;"
      );
      $customer = database::fetch($customer_query);

      foreach ($customer as $field => $value) {
        if (preg_match('#^shipping_(.*)$#', $field, $matches)) {
          session::$data['customer']['shipping_address'][$matches[1]] = $value;
        } else {
          session::$data['customer'][$field] = $value;
        }
      }

      if (empty(self::$data['different_shipping_address'])) {
        foreach (array_keys(self::$data['shipping_address']) as $key) {
          self::$data['shipping_address'][$key] = self::$data[$key];
        }
      }
    }

    public static function require_login() {
      if (!self::check_login()) {
        notices::add('warnings', language::translate('warning_must_login_page', 'You must be logged in to view the page.'));
        $redirect_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        header('Location: ' . document::ilink('login', ['redirect_url' => $redirect_url]));
        exit;
      }
    }

    public static function check_login() {
      if (!empty(self::$data['id'])) return true;
    }
  }
