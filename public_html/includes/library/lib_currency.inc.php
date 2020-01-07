<?php

  class currency {
    public static $currencies;
    public static $selected;

    public static function init() {

    // Bind selected to session
      if (!isset(session::$data['currency']) || !is_array(session::$data['currency'])) session::$data['currency'] = array();
      self::$selected = &session::$data['currency'];

    // Load currencies
      self::load();

    // Identify/set currency
      self::set();
    }

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

      if (!empty($_COOKIE['cookies_accepted'])) {
        header('Set-Cookie: currency_code='. $code .'; path='. WS_DIR_APP .'; expires='. gmdate('r', strtotime('+3 months')) .'; SameSite=Strict');
      }
    }

    public static function identify() {

      $all_currencies = array_keys(self::$currencies);

      $enabled_currencies = array();
      foreach (self::$currencies as $currency) {
        if (!empty(user::$data['id']) || $currency['status'] == 1) $enabled_currencies[] = $currency['code'];
      }

    // Return chained currency with language
      if (!empty(language::$selected['currency_code'])) {
        if (in_array(language::$selected['currency_code'], $all_currencies)) {
          return language::$selected['currency_code'];
        }
      }

    // Return currency from URI query
      if (!empty($_GET['currency'])) {
        if (in_array($_GET['currency'], $all_currencies)) return $_GET['currency'];
      }

    // Return currency from session
      if (isset(self::$selected['code']) && in_array(self::$selected['code'], $all_currencies)) return self::$selected['code'];

    // Set currency from cookie
      if (!empty($_COOKIE['currency_code']) && in_array($_COOKIE['currency_code'], $all_currencies)) {
        return $_COOKIE['currency_code'];
      }

    // Get currency from country (via browser locale)
      if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match('#^([a-z]{2}-[A-Z]{2})#', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        if (preg_match('#-([A-Z]{2})#', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {
          if (!empty($matches[1])) $country_code = $matches[1];
        }
        if (!empty($country_code)) {
          $countries_query = database::query(
            "select * from ". DB_TABLE_COUNTRIES ."
            where iso_code_2 = '". database::input($country_code) ."'
            limit 1;"
          );
          $country = database::fetch($countries_query);

          if (!empty($country['currency_code']) && in_array($country['currency_code'], $enabled_currencies)) {
            return $country['currency_code'];
          }
        }
      }

    // Get currency from country (via TLD)
      if (preg_match('#\.([a-z]{2})$#', $_SERVER['HTTP_HOST'], $matches)) {
        $countries_query = database::query(
          "select * from ". DB_TABLE_COUNTRIES ."
          where iso_code_2 = '". database::input(strtoupper($matches[1])) ."'
          limit 1;"
        );
        $country = database::fetch($countries_query);
        if (!empty($country['currency_code']) && in_array($country['currency_code'], $enabled_currencies)) {
          return $country['currency_code'];
        }
      }

    // Return default currency
      if (in_array(settings::get('default_currency_code'), $all_currencies)) return settings::get('default_currency_code');

    // Return store currency
      if (in_array(settings::get('store_currency_code'), $all_currencies)) return settings::get('store_currency_code');

    // Return first currency
      return (!empty($enabled_currencies)) ? $enabled_currencies[0] : $all_currencies[0];
    }

    public static function calculate($value, $to, $from=null) {

      if (empty($from)) $from = settings::get('store_currency_code');

      if (!isset(self::$currencies[$from])) trigger_error("Cannot convert from currency $from as the currency does not exist", E_USER_WARNING);
      if (!isset(self::$currencies[$to])) trigger_error("Cannot convert to currency $to as the currency does not exist", E_USER_WARNING);

      return $value * self::$currencies[$from]['value'] / self::$currencies[$to]['value'];
    }

    public static function convert($value, $from, $to=null) {

      if (empty($to)) $to = settings::get('store_currency_code');

      return self::calculate($value, $to, $from);
    }

    public static function format($value, $auto_decimals=true, $currency_code=null, $currency_value=null) {

    // Backwards compatibility
      if (is_bool($currency_code) === true) {
        trigger_error(__METHOD__.'() does no longer support a boolean value for third argument', E_USER_DEPRECATED);
        @list($value, $auto_decimals, , $currency_code, $currency_value) = func_get_args();
      }

      if ($currency_code === null) $currency_code = self::$selected['code'];

      if ($currency_value === null) $currency_value = isset(self::$currencies[$currency_code]) ? (float)self::$currencies[$currency_code]['value'] : 0;

      $decimals = isset(self::$currencies[$currency_code]['decimals']) ? (int)self::$currencies[$currency_code]['decimals'] : 2;
      $amount = round($value / $currency_value, $decimals);
      $prefix = !empty(self::$currencies[$currency_code]['prefix']) ? self::$currencies[$currency_code]['prefix'] : '';
      $suffix = !empty(self::$currencies[$currency_code]['suffix']) ? self::$currencies[$currency_code]['suffix'] : '';

      if (empty(self::$currencies[$currency_code])) $suffix = ' ' . $currency_code;

      if ($auto_decimals && settings::get('auto_decimals')) {
        if ($amount - floor($amount) == 0) {
          $decimals = 0;
        }
      }

      return $prefix . number_format($amount, $decimals, language::$selected['decimal_point'], language::$selected['thousands_sep']) . $suffix;
    }

    public static function format_raw($value, $currency_code=null, $currency_value=null) {

      if (empty($currency_code)) $currency_code = self::$selected['code'];
      if (!isset(self::$currencies[$currency_code])) trigger_error("Cannot format amount as currency $currency_code does not exist", E_USER_WARNING);

      if (empty($currency_value)) $currency_value = currency::$currencies[$currency_code]['value'];

      return number_format($value / $currency_value, currency::$currencies[$currency_code]['decimals'], '.', '');
    }

  // Round a store currency amount in a remote currency
    public static function round($value, $currency_code) {

      if (empty($currency_code)) $currency_code = self::$selected['code'];
      if (!isset(self::$currencies[$currency_code])) trigger_error("Cannot format amount as currency $currency_code does not exist", E_USER_WARNING);

      $value = self::convert($value, settings::get('store_currency_code'), $currency_code);
      $value = round($value, self::$currencies[$currency_code]['decimals']);
      $value = self::convert($value, $currency_code, settings::get('store_currency_code'));

      return $value;
    }
  }
