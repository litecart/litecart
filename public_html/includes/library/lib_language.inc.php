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
      
    // Set language
      if (empty(self::$selected['code']) || empty(self::$languages[self::$selected['code']]['status'])) {
        self::set();
      }
      
    // Set mysql charset and reinitiate list of languages
      database::set_character(self::$selected['charset']);
      self::load();
      self::set(self::$selected['code']);
      
      if (!empty($_POST['set_language'])) {
        self::set($_POST['set_language']);
        header('Location: '. document::link());
        exit;
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
      setcookie('language_code', $code, (time()+3600*24)*30, WS_DIR_HTTP_HOME);
      
    // Set system locale
      if (!setlocale(LC_TIME, explode(',', self::$selected['locale']))) {
        trigger_error('Warning: Failed setting locale '. self::$selected['locale'] .' for '. self::$selected['code'], E_USER_WARNING);
      }
      
    // Chain select currency
      if (!empty(self::$selected['currency_code'])) {
        if (!empty(currency::$currencies[self::$selected['currency_code']])) {
          currency::set(self::$selected['currency_code']);
        }
      }
    }
    
    public static function identify() {
      
    // Return language from URI
      $code = current(explode('/', substr($_SERVER['REQUEST_URI'], strlen(WS_DIR_HTTP_HOME))));
      if (isset(self::$languages[$code])) return $code;
      
    // Return language from session
      if (isset(self::$selected['code']) && isset(self::$languages[self::$selected['code']])) return self::$selected['code'];
      
    // Return language from cookie
      if (isset($_COOKIE['language_code']) && isset(self::$languages[$_COOKIE['language_code']])) return $_COOKIE['language_code'];
      
    // Return language from browser
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
      
    // Return default language
      if (isset(self::$languages[settings::get('default_language_code')])) return settings::get('default_language_code');
      
    // Return system language
      if (isset(self::$languages[settings::get('store_language_code')])) return settings::get('store_language_code');
      
    // Return first language
      $languages = array_keys(self::$languages);
      return array_shift($languages);
    }
    
    public static function translate($code, $default='', $language_code='') {
      
      if (empty($language_code)) {
        if (empty(self::$selected['code'])) self::set(self::identify());
        $language_code = self::$selected['code'];
      }
      
    // Return from cache
      if (isset(self::$_cache['translations'][$language_code][$code])) {
        return self::$_cache['translations'][$language_code][$code];
      }
      
    // Get translation from database
      $translations_query = database::query(
        "select id, text_en, text_". database::input($language_code) .", pages from ". DB_TABLE_TRANSLATIONS ."
        where code = '". database::input($code) ."'
        limit 0, 1;"
      );
      $row = database::fetch($translations_query);
      
    // Set translation
      if (!empty($row['text_'.$language_code])) {
        $translation = $row['text_'.$language_code];
      }
      
    // Get identical translation
      if (empty($translation) && (!empty($row['text_en']) || !empty($default))) {
      
        $secondary_translations_query = database::query(
          "select * from ". DB_TABLE_TRANSLATIONS ."
          where text_". database::input($language_code) ." != ''
          and binary text_en = '". database::input(!empty($row['text_en']) ? $row['text_en'] : $default) ."'
          limit 1;"
        );
        $secondary_translation = database::fetch($secondary_translations_query);
        
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
      if (empty($translation) && !empty($row['text_en'])) {
        $translation = $row['text_en'];
      }
      
    // Fallback on injection translation
      if (empty($translation)) {
        $translation = $default;
      }
      
      self::$_cache['translations'][$language_code][$code] = $translation;
      
      $backtrace = current(debug_backtrace());
      if (!empty($backtrace['file'])) {
        $page = database::input(substr($backtrace['file'], strlen(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME)));
      } else {
        $page = substr(__FILE__, strlen(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME));
      }
      
      if (empty($row)) {
        database::query(
          "insert into ". DB_TABLE_TRANSLATIONS ."
          (code, pages, text_en, date_created, date_updated)
          values('". database::input($code) ."', '\'". str_replace(WS_DIR_HTTP_HOME, '', database::input($_SERVER['SCRIPT_NAME'])) ."\',', '". database::input($default) ."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
        );
        $row = array(
          'id' => database::insert_id(),
          'text_en' => $default,
          'pages' => '\''.$page.'\'',
        );
      }
      
      database::query(
        "update ". DB_TABLE_TRANSLATIONS ."
        set date_accessed = '". date('Y-m-d H:i:s') ."'
        ". (!in_array($page, explode(',', trim($row['pages'],','))) ? ",pages = '". database::input(implode(',', array_merge(array($page), explode(',', $row['pages'])))) ."'" : false) ."
        where id = '". database::input($row['id']) ."';"
      );
        
      return self::$_cache['translations'][$language_code][$code];
    }
    
    public static function number_format($number, $decimals=2) {
      return number_format($number, $decimals, self::$selected['decimal_point'], self::$selected['thousands_sep']);
    }
  }
  
?>