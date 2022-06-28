<?php

  class currency {
    public static $currencies;
    public static $selected;

    public static function init() {

    // Bind selected to session
      if (!isset(session::$data['currency']) || !is_array(session::$data['currency'])) session::$data['currency'] = [];
      self::$selected = &session::$data['currency'];

    // Load currencies
      self::load();

    // Identify/set currency
      self::set();
    }

    ######################################################################

    public static function load() {

    // Get currencies from database
      self::$currencies = database::query(
        "select * from ". DB_TABLE_PREFIX ."currencies
        where status
        order by priority;"
      )->fetch_all(null, 'code');
    }

    public static function set($code='') {

      if (empty($code)) $code = self::identify();

      if (!isset(self::$currencies[$code])) {
        trigger_error('Cannot set unsupported currency ('. $code .')', E_USER_WARNING);
        $code = self::identify();
      }

      session::$data['currency'] = self::$currencies[$code];

    // Sort by relevance / fallback order
      uasort(self::$currencies, function($a, $b){
        $pos_a = array_search($a['code'], [self::$selected['code'], settings::get('site_currency_code')]);
        $pos_b = array_search($b['code'], [self::$selected['code'], settings::get('site_currency_code')]);

        if ($pos_a === false && $b === false) return 0;
        else if ($pos_a === false) return 1;
        else if ($b === false) return -1;
        else return $pos_a - $pos_b;
      });

      if (!empty($_COOKIE['cookies_accepted']) || !settings::get('cookie_policy')) {
        header('Set-Cookie: currency_code='. $code .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; SameSite=Lax', false);
      }
    }

    public static function identify() {

      $all_currencies = array_keys(self::$currencies);

      $enabled_currencies = [];
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

    // Get currency from country
      if (!empty(customer::$data['country_code'])) {

        $country = database::query(
          "select * from ". DB_TABLE_PREFIX ."countries
          where iso_code_2 = '". database::input(customer::$data['country_code']) ."'
          limit 1;"
        )->fetch();

        if ($country) {
          if (!empty($country['currency_code']) && in_array($country['currency_code'], $enabled_currencies)) {
            return $country['currency_code'];
          }
        }
      }

    // Get currency from country (via TLD)
      if (preg_match('#\.([a-z]{2})$#', $_SERVER['HTTP_HOST'], $matches)) {

        $country = database::query(
          "select * from ". DB_TABLE_PREFIX ."countries
          where iso_code_2 = '". database::input(strtoupper($matches[1])) ."'
          limit 1;"
        )->fetch();

        if ($country && in_array($country['currency_code'], $enabled_currencies)) {
          return $country['currency_code'];
        }
      }

    // Return default currency
      if (in_array(settings::get('default_currency_code'), $all_currencies)) return settings::get('default_currency_code');

    // Return store currency
      if (in_array(settings::get('site_currency_code'), $all_currencies)) return settings::get('site_currency_code');

    // Return first currency
      return (!empty($enabled_currencies)) ? $enabled_currencies[0] : $all_currencies[0];
    }

    public static function calculate($value, $to, $from='') {

      if (empty($from)) $from = settings::get('site_currency_code');

      if (!isset(self::$currencies[$from])) trigger_error("Cannot convert from currency $from as the currency does not exist", E_USER_WARNING);
      if (!isset(self::$currencies[$to])) trigger_error("Cannot convert to currency $to as the currency does not exist", E_USER_WARNING);

      return $value * self::$currencies[$from]['value'] / self::$currencies[$to]['value'];
    }

    public static function convert($value, $from, $to='') {

      if (empty($to)) $to = settings::get('site_currency_code');

      return self::calculate($value, $to, $from);
    }

    public static function format($value, $auto_decimals=true, $currency_code='', $currency_value=null) {

      if (empty($currency_code)) {
        $currency_code = self::$selected['code'];
      }

      if (empty(self::$currencies[$currency_code]) && empty($currency_value)) {
        trigger_error("Cannot format amount as currency $currency_code does not exist", E_USER_WARNING);
      }

      if (empty($currency_value)) {
        if (empty(self::$currencies[$currency_code]['value'])) return false;
        $currency_value = self::$currencies[$currency_code]['value'];
      }

      $amount = (float)self::format_raw($value, $currency_code, $currency_value);
      $decimals = isset(self::$currencies[$currency_code]['decimals']) ? (int)self::$currencies[$currency_code]['decimals'] : 2;
      $prefix = isset(self::$currencies[$currency_code]['prefix']) ? self::$currencies[$currency_code]['prefix'] : '';
      $suffix = isset(self::$currencies[$currency_code]['suffix']) ? self::$currencies[$currency_code]['suffix'] : ' ' . $currency_code;

      if ($auto_decimals && settings::get('auto_decimals')) {
        if ($amount == floor($amount)) $decimals = 0;
      }

      return $prefix . number_format((float)$amount, (int)$decimals, language::$selected['decimal_point'], language::$selected['thousands_sep']) . $suffix;
    }

    public static function format_html($value, $auto_decimals=true, $currency_code=null, $currency_value=null) {

      if (empty($currency_code)) {
        $currency_code = self::$selected['code'];
      }

      if (empty(self::$currencies[$currency_code]) && empty($currency_value)) {
        trigger_error("Cannot format amount as currency $currency_code does not exist", E_USER_WARNING);
      }

      if (empty($currency_value)) {
        if (empty(self::$currencies[$currency_code]['value'])) return false;
        $currency_value = self::$currencies[$currency_code]['value'];
      }

      $amount = (float)self::format_raw($value, $currency_code, $currency_value);
      $decimals = isset(self::$currencies[$currency_code]['decimals']) ? (int)self::$currencies[$currency_code]['decimals'] : 2;
      $prefix = !empty(self::$currencies[$currency_code]['prefix']) ? self::$currencies[$currency_code]['prefix'] : '';
      $suffix = !empty(self::$currencies[$currency_code]['suffix']) ? self::$currencies[$currency_code]['suffix'] : '';

      if ($auto_decimals && settings::get('auto_decimals')) {
        if ($amount == floor($amount)) $decimals = 0;
      }

      if ($decimals) {
        list($integers, $fractions) = explode('.', number_format($amount, $decimals, '.', ''));
      } else {
        $integers = $amount;
        $fractions = 0;
      }

      return '<span class="currency-amount"><small class="currency">'. $currency_code . '</small> ' . $prefix . number_format((int)$integers, 0, '', language::$selected['thousands_sep']) . ($fractions ? '<span class="decimals">'. language::$selected['decimal_point'] . $fractions .'</span>' : '') . $suffix . '</span>';
    }

    public static function format_raw($value, $currency_code=null, $currency_value=null) {

      if ((float)$value == 0) {
        return 0;
      }

      if (empty($currency_code)) {
        $currency_code = self::$selected['code'];
      }

      if (!empty(self::$currencies[$currency_code])) {
        $decimals = self::$currencies[$currency_code]['decimals'];
      } else {
        $decimals = 2;
      }

      if (empty(self::$currencies[$currency_code]) && empty($currency_value)) {
        trigger_error("Cannot format amount as currency $currency_code does not exist", E_USER_WARNING);
      }

      if (empty($currency_value) || $currency_value == 0) {
        if (empty(self::$currencies[$currency_code]['value'])) return false;
        if (!$currency_value = self::$currencies[$currency_code]['value']) return 0;
      }

      return number_format((float)$value / $currency_value, $decimals, '.', '');
    }

  // Round a store currency amount in a remote currency
    public static function round($value, $currency_code) {

      if (empty($currency_code)) $currency_code = self::$selected['code'];
      if (!isset(self::$currencies[$currency_code])) trigger_error("Cannot format amount as currency $currency_code does not exist", E_USER_WARNING);

      $value = self::convert($value, settings::get('site_currency_code'), $currency_code);
      $value = round((float)$value, (int)self::$currencies[$currency_code]['decimals']);
      $value = self::convert($value, $currency_code, settings::get('site_currency_code'));

      return $value;
    }
  }
