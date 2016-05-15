<?php

  class language {
    public static $selected = array();
    public static $languages = array();
    private static $_cache = array();
    private static $_cache_id = '';

    //public static function construct() {
    //}

    public static function load_dependencies() {

    // Bind selected language to session
      if (!isset(session::$data['language'])) session::$data['language'] = array();
      self::$selected = &session::$data['language'];

    // Get languages from database
      self::load();

    // Set upon HTTP POST request
      if (!empty($_POST['set_language'])) {
        trigger_error('set_language via HTTP POST is deprecated, use &language=xx instead', E_USER_DEPRECATED);
        self::set($_POST['set_language']);
        header('Location: '. document::link());
        exit;
      }

    // Identify/set language
      self::set();

    // Reload languages if not UTF-8
      if (strtoupper(self::$selected['charset']) != 'UTF-8') {
        self::load();
        self::set(self::$selected['code']);
      }
    }

    //public static function initiate() {
    //}

    public static function startup() {

    // Import cached translations
      self::$_cache_id = cache::cache_id('translations', array('language', 'basename'));
      self::$_cache = cache::get(self::$_cache_id, 'file');

      header('Content-Language: '. self::$selected['code']);
    }

    public static function before_capture() {

      if (empty(self::$selected['code'])) trigger_error('Error: No language set', E_USER_ERROR);

      $translations_query = database::query(
        "select id, code, text_en, text_". self::$selected['code'] ." from ". DB_TABLE_TRANSLATIONS ."
        where find_in_set('". database::input(str_replace(WS_DIR_HTTP_HOME, '', $_SERVER['SCRIPT_NAME'])) ."', pages)"
      );

      $translations = array();
      while ($row = database::fetch($translations_query)) {

        if (!empty($row['text_'.self::$selected['code']])) {
          self::$_cache['translations'][self::$selected['code']][$row['code']] = $row['text_'.self::$selected['code']];

        } else if (!empty($row['text_en'])) {
          self::$_cache['translations'][self::$selected['code']][$row['code']] = $row['text_en'];
        }

        $translation_ids[] = $row['id'];
      }

      if (isset($translation_ids)) {
        database::query(
          "update ". DB_TABLE_TRANSLATIONS ."
          set date_accessed = '". date('Y-m-d H:i:s') ."'
          where id in ('". implode('\',\'', $translation_ids) ."');"
        );
      }
    }

    //public static function after_capture() {
    //}

    //public static function prepare_output() {
    //}

    //public static function before_output() {
    //}

    public static function shutdown() {
      cache::set(self::$_cache_id, 'file', self::$_cache);
    }

    ######################################################################

    public static function load() {

      self::$languages = array();

      $languages_query = database::query(
        "select * from ". DB_TABLE_LANGUAGES ."
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

      session::$data['language'] = self::$languages[$code];
      setcookie('language_code', $code, round(time()+3600*24*30), WS_DIR_HTTP_HOME);

    // Set system locale
      if (!setlocale(LC_TIME, explode(',', self::$selected['locale']))) {
        trigger_error('Warning: Failed setting locale '. self::$selected['locale'] .' for '. self::$selected['code'], E_USER_WARNING);
      }

    // Set PHP multibyte charset
      mb_internal_encoding(self::$selected['charset']);

    // Set PHP output encoding
      mb_http_output(self::$selected['charset']);

    // Set mysql charset and collation
      database::set_encoding(self::$selected['charset']);
    }

    public static function identify() {

    // Return language from URI query
      if (!empty($_GET['language'])) {
        if (isset(self::$languages[$_GET['language']])) return $_GET['language'];
      }

    // Return language from URI path
      $code = current(explode('/', substr($_SERVER['REQUEST_URI'], strlen(WS_DIR_HTTP_HOME))));
      if (isset(self::$languages[$code])) return $code;

    // Return language from session
      if (isset(self::$selected['code']) && isset(self::$languages[self::$selected['code']])) return self::$selected['code'];

    // Return language from cookie
      if (isset($_COOKIE['language_code']) && isset(self::$languages[$_COOKIE['language_code']])) return $_COOKIE['language_code'];

    // Return language from browser request headers
      if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browser_locales = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
      } elseif (isset($_SERVER['LC_CTYPE'])) {
        $browser_locales = explode(',', $_SERVER['LC_CTYPE']);
      } else {
        $browser_locales = array();
      }
      foreach ($browser_locales as $browser_locale) {
        if (preg_match('/('. implode('|', array_keys(self::$languages)) .')-?.*/', $browser_locale, $reg)) {
          if (!empty($reg[1]) && isset(self::$languages[$reg[1]])) return $reg[1];
        }
      }

    // Return language from country (TLD)
      if (preg_match('#\.([a-z]{2})$#', $_SERVER['SERVER_NAME'], $matches)) {
        $countries_query = database::query(
          "select * from ". DB_TABLE_COUNTRIES ."
          where iso_code_2 = '". database::input(strtoupper($matches[1])) ."'
          limit 1;"
        );
        $country = database::fetch($countries_query);
        if (!empty($country['language_code']) && isset(self::$languages[$country['language_code']])) return $country['language_code'];
      }

    // Return default language
      if (isset(self::$languages[settings::get('default_language_code')])) return settings::get('default_language_code');

    // Return system language
      if (isset(self::$languages[settings::get('store_language_code')])) return settings::get('store_language_code');

    // Return first language
      $languages = array_keys(self::$languages);
      return array_shift($languages);
    }

    public static function translate($code, $default=null, $language_code=null) {

      $code = strtolower($code);

      if (empty($language_code)) {
        $language_code = language::$selected['code'];
        $default_language_code = 'en';
      } else {
        $default_language_code = $language_code;
      }

      if (empty($language_code) || empty(language::$languages[$language_code])) {
        trigger_error('Unknown language code for translation ('. $language_code .')', E_USER_WARNING);
        return;
      }

    // Return from cache
      if (isset(self::$_cache['translations'][$language_code][$code])) {
        return self::$_cache['translations'][$language_code][$code];
      }

    // Get translation from database
      $primary_translation_query = database::query(
        "select id, text_en, text_". database::input($language_code) .", pages from ". DB_TABLE_TRANSLATIONS ."
        where code = '". database::input($code) ."'
        limit 0, 1;"
      );
      $primary_translation = database::fetch($primary_translation_query);
      $primary_translation['pages'] = str_replace("'", '', $primary_translation['pages']);
      $primary_translation['pages'] = implode(',', array_unique(explode(',', $primary_translation['pages'])));

    // Set translation
      if (!empty($primary_translation['text_'.$language_code])) {
        $translation = $primary_translation['text_'.$language_code];
      }

    // Get identical translation
      if (empty($translation) && (!empty($primary_translation['text_en']) || !empty($default))) {

        $secondary_translation_query = database::query(
          "select * from ". DB_TABLE_TRANSLATIONS ."
          where text_". database::input($language_code) ." != ''
          and binary text_en = '". database::input(!empty($primary_translation['text_en']) ? $primary_translation['text_en'] : $default) ."'
          limit 1;"
        );
        $secondary_translation = database::fetch($secondary_translation_query);

        if (!empty($secondary_translation)) {
          database::query(
            "update ". DB_TABLE_TRANSLATIONS ."
            set text_". database::input($language_code) ." = '". database::input($secondary_translation['text_'.$language_code]) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
            where code = '". database::input($code) ."'
            limit 1;"
          );
          $translation = $secondary_translation['text_'.$language_code];
        }
      }

    // Fallback on english translation
      if (empty($translation) && !empty($primary_translation['text_en'])) {
        $translation = $primary_translation['text_en'];
      }

    // Fallback on injection translation
      if (!isset($translation)) {
        $translation = $default;
      }

      self::$_cache['translations'][$language_code][$code] = $translation;

      $backtrace = current(debug_backtrace());
      if (!empty($backtrace['file'])) {
        $page = substr(str_replace("\\", '/', $backtrace['file']), strlen(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME));
      } else {
        $page = substr(str_replace("\\", '/', __FILE__), strlen(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME));
      }

      if ($default !== null) {
        if (empty($primary_translation['id'])) {
          database::query(
            "insert into ". DB_TABLE_TRANSLATIONS ."
            (code, pages, text_". database::input($default_language_code) .", date_created, date_updated)
            values('". database::input($code) ."', '". database::input($page) ."', '". database::input($default) ."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
          );
          $primary_translation = array(
            'id' => database::insert_id(),
            'text_en' => $default,
            'pages' => $page,
          );
        }

        database::query(
          "update ". DB_TABLE_TRANSLATIONS ."
          set date_accessed = '". date('Y-m-d H:i:s') ."'
          ". (!in_array($page, explode(',', $primary_translation['pages'])) ? ",pages = concat_ws(',', if(pages = '', NULL, pages), '". database::input($page) ."')" : "") ."
          where id = '". (int)$primary_translation['id'] ."';"
        );
      }

      return self::$_cache['translations'][$language_code][$code];
    }

    public static function number_format($number, $decimals=2) {
      return number_format($number, $decimals, self::$selected['decimal_point'], self::$selected['thousands_sep']);
    }

    public static function strftime($format, $timestamp=null) {

      if ($timestamp === null) $timestamp = time();

      if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
        $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);

        $locale = setlocale(LC_TIME, 0);

        switch(true) {
          //case (preg_match('#\.(0)$#', $locale)):
          //  return '???';

          case (preg_match('#\.(874|1256)$#', $locale, $matches)):
            return iconv('UTF-8', "$locale_charset", strftime($format, $timestamp));

          case (preg_match('#\.1250$#', $locale)):
            return mb_convert_encoding(strftime($format, $timestamp), language::$selected['charset'], 'ISO-8859-2');

          case (preg_match('#\.(1251|1252|1254)$#', $locale, $matches)):
            return mb_convert_encoding(strftime($format, $timestamp), language::$selected['charset'], 'Windows-'.$matches[1]);

          case (preg_match('#\.(1255|1256)$#', $locale, $matches)):
            return iconv(language::$selected['charset'], "Windows-{$matches[1]}", strftime($format, $timestamp));

          case (preg_match('#\.1257$#', $locale)):
            return mb_convert_encoding(strftime($format, $timestamp), language::$selected['charset'], 'ISO-8859-13');

          case (preg_match('#\.(932|936|950)$#', $locale)):
            return mb_convert_encoding(strftime($format, $timestamp), language::$selected['charset'], 'CP'.$matches[1]);

          case (preg_match('#\.(949)$#', $locale)):
            return mb_convert_encoding(strftime($format, $timestamp), language::$selected['charset'], 'EUC-KR');

          //case (preg_match('#\.(x-iscii-ma)$i#', $locale)):
          //  return '???';

          default:
            trigger_error("Unknown charset for system locale ($locale)", E_USER_NOTICE);
            return mb_convert_encoding(strftime($format, $timestamp), language::$selected['charset'], 'auto');
        }
      }

      return strftime($format, $timestamp);
    }
  }

?>