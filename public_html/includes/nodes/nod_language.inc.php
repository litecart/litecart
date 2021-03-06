<?php

  class language {
    public static $selected = [];
    public static $languages = [];
    private static $_cache = [];
    private static $_cache_token;
    private static $_loaded_translations = [];

    public static function init() {

    // Bind selected language to session
      if (preg_match('#^'. preg_quote(WS_DIR_APP . BACKEND_ALIAS, '#') .'/#', $_SERVER['REQUEST_URI'])) {
        if (empty(session::$data['backend']['language'])) session::$data['backend']['language'] = [];
        self::$selected = &session::$data['backend']['language'];
      } else {
        if (!isset(session::$data['language'])) session::$data['language'] = [];
        self::$selected = &session::$data['language'];
      }

    // Get languages from database
      self::load();

    // Identify/set language
      self::set();

      self::$_cache_token = cache::token('translations', ['endpoint', 'language']);

      if (!self::$_cache['translations'] = cache::get(self::$_cache_token)) {
        $translations_query = database::query(
          "select id, code, if(text_". self::$selected['code'] ." is not null and text_". self::$selected['code'] ." != '', text_". self::$selected['code'] .", text_en) as text from ". DB_TABLE_PREFIX ."translations
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
        set ". (preg_match('#^'. preg_quote(ltrim(WS_DIR_ADMIN, '/'), '#') .'.*#', route::$request) ? "backend = 1" : "frontend = 1")  ."
        where code in ('". implode("', '", database::input(self::$_loaded_translations)) ."');"
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
        if (self::$languages[$language_code]['url_type'] != 'domain') continue;
        if (preg_match('#'. preg_quote(self::$languages[$language_code]['url_type'], '#') .'$#', $_SERVER['HTTP_HOST'])) {
          return $language_code;
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

      if (empty($language_code)) {
        $language_code = self::$selected['code'];
      }

      if (empty($language_code) || empty(self::$languages[$language_code])) {
        trigger_error('Unknown language code for translation ('. $language_code .')', E_USER_WARNING);
        return;
      }

    // Return from cache
      if (isset(self::$_cache['translations'][$language_code][$code])) {
        self::$_loaded_translations[] = $code;
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
        self::$_loaded_translations[] = $code;
        return self::$_cache['translations'][$language_code][$code] = $translation['text_'.$language_code];
      }

    // If we have an english translation
      if (!empty($translation['text_en'])) {

      // Find same english translation by different key
        $secondary_translation_query = database::query(
          "select id, text_en, `text_". $language_code ."` from ". DB_TABLE_PREFIX ."translations
          where text_en = '". database::input($translation['text_en']) ."'
          and (text_en is not null and text_en != '')
          and (text_". self::$selected['code'] ." is not null and text_". self::$selected['code'] ." != '')
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

          self::$_loaded_translations[] = $code;
          return self::$_cache['translations'][$language_code][$code] = $secondary_translation['text_'.$language_code];
        }

      // Return english translation
        self::$_loaded_translations[] = $code;
        return self::$_cache['translations'][$language_code][$code] = $translation['text_en'];
      }

    // Return default translation
      self::$_loaded_translations[] = $code;
      return self::$_cache['translations'][$language_code][$code] = $default;
    }

    public static function number_format($number, $decimals=2) {
      return number_format($number, $decimals, self::$selected['decimal_point'], self::$selected['thousands_sep']);
    }

    public static function strftime($format, $timestamp=null) {

      if ($timestamp === null) $timestamp = time();

      if (in_array(strtoupper(substr(PHP_OS, 0, 3)), ['WIN', 'MAC'])) {
        $format = preg_replace('#(?<!%)((?:%%)*)%P#', '\1%p', $format);
      }

      if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
        $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);

        $locale = setlocale(LC_TIME, 0);

        switch(true) {
          //case (preg_match('#\.(0)$#', $locale)):
          //  return '???';

          case (preg_match('#\.(874|1256)$#', $locale, $matches)):
            return iconv('UTF-8', "$locale", strftime($format, $timestamp));

          case (preg_match('#\.1250$#', $locale)):
            return mb_convert_encoding(strftime($format, $timestamp), self::$selected['charset'], 'ISO-8859-2');

          case (preg_match('#\.(1251|1252|1254)$#', $locale, $matches)):
            return mb_convert_encoding(strftime($format, $timestamp), self::$selected['charset'], 'Windows-'.$matches[1]);

          case (preg_match('#\.(1255|1256)$#', $locale, $matches)):
            return iconv(self::$selected['charset'], "Windows-{$matches[1]}", strftime($format, $timestamp));

          case (preg_match('#\.1257$#', $locale)):
            return mb_convert_encoding(strftime($format, $timestamp), self::$selected['charset'], 'ISO-8859-13');

          case (preg_match('#\.(932|936|950)$#', $locale, $matches)):
            return mb_convert_encoding(strftime($format, $timestamp), self::$selected['charset'], 'CP'.$matches[1]);

          case (preg_match('#\.(949)$#', $locale)):
            return mb_convert_encoding(strftime($format, $timestamp), self::$selected['charset'], 'EUC-KR');

          //case (preg_match('#\.(x-iscii-ma)$i#', $locale)):
          //  return '???';

          default:
            trigger_error("No predefined charset mapped for Windows locale $locale. Attempting automatic detection instead.", E_USER_NOTICE);
            return mb_convert_encoding(strftime($format, $timestamp), self::$selected['charset'], 'auto');
        }
      }

      return strftime($format, $timestamp);
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
