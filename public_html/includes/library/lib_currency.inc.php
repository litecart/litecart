<?php

  class currency {
    public static $currencies;
    public static $selected;

    public static function construct() {
    }

    public static function load_dependencies() {

    // Bind selected to session
      if (!isset(session::$data['currency']) || !is_array(session::$data['currency'])) session::$data['currency'] = array();
      self::$selected = &session::$data['currency'];
    }

    public static function initiate() {

    // Load currencies
      self::load();

    // Set upon HTTP POST request
      if (!empty($_POST['set_currency'])) {
        trigger_error('set_currency via HTTP POST is deprecated, use &language=xx instead', E_USER_DEPRECATED);
        self::set($_POST['set_currency']);
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }

    // Identify/set currency
      self::set();
    }

    //public static function startup() {
    //}

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

    public static function load() {

      self::$currencies = array();

    // Get currencies from database
      $currencies_query = database::query(
        "select * from ". DB_TABLE_CURRENCIES ."
        where status
        order by priority;"
      );
      while ($row = database::fetch($currencies_query)) {
        self::$currencies[$row['code']] = $row;
      }
    }

    public static function set($code=null) {

      if (empty($code)) $code = self::identify();

      if (!isset(self::$currencies[$code])) {
        trigger_error('Cannot set unsupported currency ('. $code .')', E_USER_WARNING);
        $code = self::identify();
      }

      session::$data['currency'] = self::$currencies[$code];
      setcookie('currency_code', $code, time()+(60*60*24*30), WS_DIR_HTTP_HOME);
    }

    public static function identify() {

    // Return chained currency with language
      if (!empty(language::$selected['currency_code'])) {
        if (!empty(self::$currencies[language::$selected['currency_code']])) {
          return language::$selected['currency_code'];
        }
      }

    // Return currency from URI query
      if (!empty($_GET['currency'])) {
        if (isset(self::$currencies[$_GET['currency']])) return $_GET['currency'];
      }

    // Return currency from session
      if (isset(self::$selected['code']) && isset(self::$currencies[self::$selected['code']])) return self::$selected['code'];

    // Set currency from cookie
      if (!empty($_COOKIE['currency_code']) && isset(self::$currencies[$_COOKIE['currency_code']])) {
        return $_COOKIE['currency_code'];
      }

    // Get currency from country (via browser locale)
      if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match('#^([a-z]{2}-[A-Z]{2})#', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        if (preg_match('/-([A-Z]{2})/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {
          if (!empty($matches[1])) $country_code = $matches[1];
        }
        if (!empty($country_code)) {
          $countries_query = database::query(
            "select * from ". DB_TABLE_COUNTRIES ."
            where iso_code_2 = '". database::input($country_code) ."'
            limit 1;"
          );
          $country = database::fetch($countries_query);

          if (!empty($country['currency_code']) && isset(self::$currencies[$country['currency_code']])) {
            return $country['currency_code'];
          }
        }
      }

    // Get currency from country (via TLD)
      if (preg_match('#\.([a-z]{2})$#', $_SERVER['SERVER_NAME'], $matches)) {
        $countries_query = database::query(
          "select * from ". DB_TABLE_COUNTRIES ."
          where iso_code_2 = '". database::input(strtoupper($matches[1])) ."'
          limit 1;"
        );
        $country = database::fetch($countries_query);
        if (!empty($country['currency_code']) && isset(self::$currencies[$country['currency_code']])) return $country['currency_code'];
      }

    // Return default currency
      if (isset(self::$currencies[settings::get('default_currency_code')])) return settings::get('default_currency_code');

    // Return store currency
      if (isset(self::$currencies[settings::get('store_currency_code')])) return settings::get('store_currency_code');

    // Return first currency
      $currencies = array_keys(self::$currencies);
      return array_shift($currencies);
    }

    public static function calculate($value, $to, $from=null) {

      if (empty($from)) $from = settings::get('store_currency_code');

      if (!isset(self::$currencies[$from])) trigger_error('Currency ('. $from .') does not exist', E_USER_WARNING);
      if (!isset(self::$currencies[$to])) trigger_error('Currency ('. $to .') does not exist', E_USER_WARNING);

      return $value / self::$currencies[$from]['value'] * self::$currencies[$to]['value'];
    }

    public static function convert($value, $from, $to=null) {

      if (empty($to)) $to = settings::get('store_currency_code');

      return self::calculate($value, $to, $from);
    }

    public static function format($value, $auto_decimals=true, $raw=false, $currency_code=null, $currency_value=null) {

      if ($raw) return self::format_raw($value, $currency_code, $currency_value);

      if (empty($currency_code)) $currency_code = self::$selected['code'];

      if (empty($currency_value) && isset(self::$currencies[$currency_code])) $currency_value = (float)self::$currencies[$currency_code]['value'];

      if (!isset(currency::$currencies[$currency_code]) && !empty($currency_value)) {
        return number_format($value * $currency_value, 2, '.', ',') .' '. $currency_code;
      }

      if (settings::get('auto_decimals') && $auto_decimals && round($value, self::$currencies[$currency_code]['decimals']) - floor($value) == 0) {
        $decimals = 0;
      } else {
        $decimals = (int)self::$currencies[$currency_code]['decimals'];
      }

      return self::$currencies[$currency_code]['prefix'] . number_format($value * $currency_value, $decimals, language::$selected['decimal_point'], language::$selected['thousands_sep']) . self::$currencies[$currency_code]['suffix'];
    }

    public static function format_raw($value, $currency_code=null, $currency_value=null) {

      if (empty($currency_code)) $currency_code = self::$selected['code'];
      if (!isset(self::$currencies[$currency_code])) trigger_error('Currency ('. $currency_code .') does not exist', E_USER_WARNING);

      if (empty($currency_value)) $currency_value = currency::$currencies[$currency_code]['value'];

      return number_format($value * $currency_value, currency::$currencies[$currency_code]['decimals'], '.', '');
    }

  // Round a store currency amount in a remote currency
    public static function round($value, $currency_code) {

      if (empty($currency_code)) $currency_code = self::$selected['code'];
      if (!isset(self::$currencies[$currency_code])) trigger_error('Currency ('. $currency_code .') does not exist', E_USER_WARNING);

      $value = self::convert($value, settings::get('store_currency_code'), $currency_code);
      $value = round($value, self::$currencies[$currency_code]['decimals']);
      $value = self::convert($value, $currency_code, settings::get('store_currency_code'));

      return $value;
    }

  // Align an amount - friendly price
    public static function align($value, $step=1, $subtract=0) {

      /* Examples:
       *   currency::align(12.34, 0.5, 0.01); // Returns 12.49
       *   currency::align(12.34, 5, 0.01);   // Returns 9.99
       *   currency::align(10.7, 2);          // Returns 10
       */

      $value += $subtract;
      if ($step == 0 || $step == 1) return round($value) - $subtract;

      return (round($value / $step) * $step) - $subtract;
    }
  }

?>