<?php

  class language {
    public static $selected = [];
    public static $languages = [];
    private static $_cache = [];
    private static $_cache_token;
    private static $_accessed_translations = [];

    public static function init() {

    // Bind selected language to session
      if (preg_match('#^'. preg_quote(WS_DIR_APP . BACKEND_ALIAS, '#') .'/#', $_SERVER['REQUEST_URI'])) {
        if (!isset(session::$data['backend']['language'])) session::$data['backend']['language'] = [];
        self::$selected = &session::$data['backend']['language'];
      } else {
        if (!isset(session::$data['language'])) session::$data['language'] = [];
        self::$selected = &session::$data['language'];
      }

    // Get languages from database
      self::load();

    // Identify/set language
      self::set();

    // Reload languages if not UTF-8
      if (strtoupper(self::$selected['charset']) != 'UTF-8') {
        self::load();
        self::set(self::$selected['code']);
      }

      self::$_cache_token = cache::token('translations', ['endpoint', 'language'], 'memory');

      if (!self::$_cache['translations'] = cache::get(self::$_cache_token)) {
        self::$_cache['translations'] = [];

        $translations_query = database::query(
          "select id, code, if(text_". self::$selected['code'] ." != '', text_". self::$selected['code'] .", text_en) as text from ". DB_TABLE_PREFIX ."translations
          where ". (preg_match('#^'. preg_quote(ltrim(WS_DIR_ADMIN, '/'), '#') .'.*#', route::$request) ? "backend = 1" : "frontend = 1") ."
          having text != '';"
        );

        while ($translation = database::fetch($translations_query)) {
          self::$_cache['translations'][self::$selected['code']][$translation['code']] = $translation['text'];
        }
      }

      event::register('before_capture', [__CLASS__, 'before_capture']);
      event::register('before_output', [__CLASS__, 'before_output']);
      event::register('shutdown', [__CLASS__, 'shutdown']);
    }

    public static function before_capture() {
      header('Content-Language: '. self::$selected['code']);
    }

    public static function before_output() {
      if (empty(self::$selected['code'])) trigger_error('Error: No language set', E_USER_ERROR);
    }

    public static function shutdown() {

      database::query(
        "update ". DB_TABLE_PREFIX ."translations
        set ". (preg_match('#^'. preg_quote(ltrim(WS_DIR_ADMIN, '/'), '#') .'.*#', route::$request) ? "backend = 1" : "frontend = 1") .",
          date_accessed = '". date('Y-m-d H:i:s') ."'
        where code in ('". implode("', '", database::input(self::$_accessed_translations)) ."');"
      );

      cache::set(self::$_cache_token, self::$_cache['translations']);
    }

    ######################################################################

    public static function load() {

      self::$languages = [];

      $languages_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."languages
        where status
        order by priority, name;"
      );

      while ($row = database::fetch($languages_query)) {
        self::$languages[$row['code']] = $row;
      }
    }

    public static function set($code=null) {

      if (empty($code)) $code = self::identify();

      if (!isset(self::$languages[$code])) {
        trigger_error('Cannot set unsupported language ('. $code .')', E_USER_WARNING);
        $code = self::identify();
      }

      if (preg_match('#^'. preg_quote(WS_DIR_APP . BACKEND_ALIAS, '#') .'/#', $_SERVER['REQUEST_URI'])) {
        session::$data['backend']['language'] = self::$languages[$code];
      } else {
        session::$data['language'] = self::$languages[$code];
      }

      if (!empty($_COOKIE['cookies_accepted']) || !settings::get('cookie_policy')) {
        header('Set-Cookie: language_code='. $code .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; SameSite=Lax', false);
      }

    // Set system locale
      if (!setlocale(LC_TIME, preg_split('#\s*,\s*#', self::$selected['locale'], -1, PREG_SPLIT_NO_EMPTY))) {
        trigger_error('Warning: Failed setting locale '. self::$selected['locale'] .' for '. self::$selected['code'], E_USER_WARNING);
      }

    // Set PHP multibyte charset
      mb_internal_encoding(self::$selected['charset']);

    // Set RegEx multibyte encoding
      mb_regex_encoding(self::$selected['charset']);

    // Set PHP output encoding
      mb_http_output(self::$selected['charset']);

    // Set mysql charset and collation
      database::set_encoding(self::$selected['charset']);
    }

    public static function identify() {

      $all_languages = array_keys(self::$languages);

      $enabled_languages = [];
      foreach (self::$languages as $language) {
        if (!empty(user::$data['id']) || $language['status'] == 1) $enabled_languages[] = $language['code'];
      }

    // Return language by regional domain
      foreach ($enabled_languages as $language_code) {
        if (!empty(self::$languages[$language_code]) && self::$languages[$language_code]['url_type'] == 'domain') {
          if (!empty(self::$languages[$language_code]['domain_name']) && preg_match('#^'. preg_quote(self::$languages[$language_code]['domain_name'], '#') .'$#', $_SERVER['HTTP_HOST'])) {
            return $language_code;
          }
        }
      }

    // Return language from URI query
      if (!empty($_GET['language'])) {
        if (in_array($_GET['language'], $all_languages)) return $_GET['language'];
      }

    // Return language from URI path
      $code = current(explode('/', substr($_SERVER['REQUEST_URI'], strlen(WS_DIR_APP))));
      if (in_array($code, $all_languages)) return $code;

    // Return language from session
      if (isset(self::$selected['code']) && in_array(self::$selected['code'], $all_languages)) return self::$selected['code'];

    // Return language from cookie
      if (isset($_COOKIE['language_code']) && in_array($_COOKIE['language_code'], $all_languages)) return $_COOKIE['language_code'];

    // Return language from country (TLD)
      if (preg_match('#\.([a-z]{2})$#', $_SERVER['HTTP_HOST'], $matches)) {
        $countries_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."countries
          where iso_code_2 = '". database::input(strtoupper($matches[1])) ."'
          limit 1;"
        );
        $country = database::fetch($countries_query);
        if (!empty($country['language_code']) && in_array($country['language_code'], $enabled_languages)) return $country['language_code'];
      }

    // Return language from browser request headers
      if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browser_locales = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
      } elseif (isset($_SERVER['LC_CTYPE'])) {
        $browser_locales = explode(',', $_SERVER['LC_CTYPE']);
      } else {
        $browser_locales = [];
      }
      foreach ($browser_locales as $browser_locale) {
        if (preg_match('#('. implode('|', array_keys(self::$languages)) .')-?.*#', $browser_locale, $reg)) {
          if (!empty($reg[1]) && in_array($reg[1], $enabled_languages)) return $reg[1];
        }
      }

    // Return default language
      if (in_array(settings::get('default_language_code'), $all_languages)) return settings::get('default_language_code');

    // Return system language
      if (in_array(settings::get('store_language_code'), $all_languages)) return settings::get('store_language_code');

    // Return first language
      return (!empty($enabled_languages)) ? $enabled_languages[0] : $all_languages[0];
    }

    public static function translate($code, $default=null, $language_code=null) {

      $code = strtolower($code);

      self::$_accessed_translations[] = $code;

      if (empty($language_code)) {
        $language_code = self::$selected['code'];
      }

      if (empty($language_code) || empty(self::$languages[$language_code])) {
        trigger_error('Unknown language code for translation ('. $language_code .')', E_USER_WARNING);
        return;
      }

    // Return from cache
      if (isset(self::$_cache['translations'][$language_code][$code])) {
        return self::$_cache['translations'][$language_code][$code];
      }

    // Get translation from database
      $translation_query = database::query(
        "select id, text_en, `text_". $language_code ."` from ". DB_TABLE_PREFIX ."translations
        where code = '". database::input($code) ."'
        limit 1;"
      );

    // Create translation if it doesn't exist
      if (!$translation = database::fetch($translation_query)) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."translations
          (code, text_en, html, date_created, date_updated)
          values ('". database::input($code) ."', '". database::input($default, true) ."', '". (($default != strip_tags($default)) ? 1 : 0) ."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
        );
      }

    // Return translation
      if (!empty($translation['text_'.$language_code])) {
        return self::$_cache['translations'][$language_code][$code] = $translation['text_'.$language_code];
      }

    // If we have an english translation
      if (!empty($translation['text_en'])) {

      // Find same english translation by different key
        $secondary_translation_query = database::query(
          "select id, text_en, `text_". $language_code ."` from ". DB_TABLE_PREFIX ."translations
          where text_en = '". database::input($translation['text_en']) ."'
          and text_en != ''
          and text_". self::$selected['code'] ." != ''
          limit 1;"
        );

        if ($secondary_translation = database::fetch($secondary_translation_query)) {
          database::query(
            "update ". DB_TABLE_PREFIX ."translations
            set `text_". $language_code ."` = '". database::input($translation['text_'.$language_code], true) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
            where text_en = '". database::input($translation['text_en']) ."'
            and text_". self::$selected['code'] ." = '';"
          );

          return self::$_cache['translations'][$language_code][$code] = $secondary_translation['text_'.$language_code];
        }

      // Return english translation
        return self::$_cache['translations'][$language_code][$code] = $translation['text_en'];
      }

    // Return default translation
      return self::$_cache['translations'][$language_code][$code] = $default;
    }

    public static function number_format($number, $decimals=2) {
      return number_format((float)$number, $decimals, self::$selected['decimal_point'], self::$selected['thousands_sep']);
    }

    public static function strftime($format, $timestamp=null) {

      if ($timestamp === null) {
        $timestamp = new \DateTime();

      } elseif (is_numeric($timestamp)) {
        $timestamp = new \DateTime('@' . $timestamp, new DateTimeZone('UTC'));
        $timestamp->setTimezone(new DateTimeZone(date_default_timezone_get()));

      } elseif (is_string($timestamp)) {
        $timestamp = new \DateTime($timestamp);
      }

      if (!($timestamp instanceof \DateTimeInterface)) {
        throw new \InvalidArgumentException('$timestamp argument is neither a valid UNIX timestamp, a valid date-time string or a DateTime object.');
      }

      $intl_formats = [
        '%a' => 'EEE',	// An abbreviated textual representation of the day	Sun through Sat
        '%A' => 'EEEE',	// A full textual representation of the day	Sunday through Saturday
        '%b' => 'MMM',	// Abbreviated month name, based on the locale	Jan through Dec
        '%B' => 'MMMM',	// Full month name, based on the locale	January through December
        '%h' => 'MMM',	// Abbreviated month name, based on the locale (an alias of %b)	Jan through Dec
        '%p' => 'aa',	// UPPER-CASE 'AM' or 'PM' based on the given time	Example: AM for 00:31, PM for 22:23
        '%P' => 'aa',	// lower-case 'am' or 'pm' based on the given time	Example: am for 00:31, pm for 22:23
      ];

      $intl_formatter = function (\DateTimeInterface $timestamp, string $format) use ($intl_formats) {
        $tz = $timestamp->getTimezone();
        $date_type = IntlDateFormatter::FULL;
        $time_type = IntlDateFormatter::FULL;
        $pattern = '';

        // %c = Preferred date and time stamp based on locale
        // Example: Tue Feb 5 00:45:10 2009 for February 5, 2009 at 12:45:10 AM
        if ($format == '%c') {
          $date_type = IntlDateFormatter::LONG;
          $time_type = IntlDateFormatter::SHORT;
        }
        // %x = Preferred date representation based on locale, without the time
        // Example: 02/05/09 for February 5, 2009
        elseif ($format == '%x') {
          $date_type = IntlDateFormatter::SHORT;
          $time_type = IntlDateFormatter::NONE;
        }
        // Localized time format
        elseif ($format == '%X') {
          $date_type = IntlDateFormatter::NONE;
          $time_type = IntlDateFormatter::MEDIUM;
        }
        else {
          $pattern = $intl_formats[$format];
        }

        return (new IntlDateFormatter(null, $date_type, $time_type, $tz, null, $pattern))->format($timestamp);
      };

      $translation_table = [
      // Day
        '%a' => $intl_formatter,
        '%A' => $intl_formatter,
        '%d' => 'd',
        '%e' => 'j',
      // Day number in year, 001 to 366
        '%j' => function ($timestamp) {
          return sprintf('%03d', $timestamp->format('z')+1);
        },
        '%u' => 'N',
        '%w' => 'w',

      // Week
        '%U' => function ($timestamp) {
          // Number of weeks between date and first Sunday of year
          $day = new \DateTime(sprintf('%d-01 Sunday', $timestamp->format('Y')));
          return intval(($timestamp->format('z') - $day->format('z')) / 7);
        },
      // Number of weeks between date and first Monday of year
        '%W' => function ($timestamp) {
          $day = new \DateTime(sprintf('%d-01 Monday', $timestamp->format('Y')));
          return intval(($timestamp->format('z') - $day->format('z')) / 7);
        },
        '%V' => 'W',

      // Month
        '%b' => $intl_formatter,
        '%B' => $intl_formatter,
        '%h' => $intl_formatter,
        '%m' => 'm',

      // Year
        '%C' => function ($timestamp) {
          // Century (-1): 19 for 20th century
          return (int) $timestamp->format('Y') / 100;
        },
        '%g' => function ($timestamp) {
          return substr($timestamp->format('o'), -2);
        },
        '%G' => 'o',
        '%y' => 'y',
        '%Y' => 'Y',

      // Time
        '%H' => 'H',
        '%k' => 'G',
        '%I' => 'h',
        '%l' => 'g',
        '%M' => 'i',
        '%p' => $intl_formatter, // AM PM (this is reversed on purpose!)
        '%P' => $intl_formatter, // am pm
        '%r' => 'G:i:s A', // %I:%M:%S %p
        '%R' => 'H:i', // %H:%M
        '%S' => 's',
        '%X' => $intl_formatter,// Preferred time representation based on locale, without the date

      // Timezone
        '%z' => 'O',
        '%Z' => 'T',

      // Time and Date Stamps
        '%c' => $intl_formatter,
        '%D' => 'm/d/Y',
        '%F' => 'Y-m-d',
        '%s' => 'U',
        '%x' => $intl_formatter,
      ];

      $out = preg_replace_callback('/(?<!%)(%[a-zA-Z])/', function ($match) use ($translation_table, $timestamp) {
        if ($match[1] == '%n') {
          return "\n";
        }
        elseif ($match[1] == '%t') {
          return "\t";
        }

        if (!isset($translation_table[$match[1]])) {
          throw new \InvalidArgumentException(sprintf('Format "%s" is unknown in time format', $match[1]));
        }

        $replace = $translation_table[$match[1]];

        if (is_string($replace)) {
          return $timestamp->format($replace);
        }
        else {
          return $replace($timestamp, $match[1]);
        }
      }, $format);

      $out = str_replace('%%', '%', $out);
      return $out;
    }

    public static function convert_characters($variable, $from_charset=null, $to_charset=null) {

      if (empty($from_charset)) $from_charset = self::$selected['charset'];
      if (empty($to_charset)) $to_charset = self::$selected['charset'];

      if ($from_charset == $to_charset) return $variable;

      if (!mb_convert_variables($to_charset, $from_charset, $variable)) {
        trigger_error('Could not encode variable from '. $from_charset .' to '. $to_charset, E_USER_WARNING);
        return false;
      }

      return $variable;
    }
  }
