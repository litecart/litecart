<?php
  
  class language {
  
    private $system;
    
    public $selected = array();
    public $languages = array();
    private $_cache = array();
    private $_cache_id = '';
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    public function load_dependencies() {
      
    // Bind selected language to session
      if (!isset($this->system->session->data['language'])) $this->system->session->data['language'] = array();
      $this->selected = &$this->system->session->data['language'];
      
    // Get languages from database
      $this->load();
      
    // Set language
      if (empty($this->selected['code']) || empty($this->language[$this->selected['code']]['status'])) {
        $this->set();
      }
      
    // Set mysql charset and reinitiate list of languages
      $this->system->database->set_character($this->selected['charset']);
      $this->load();
      $this->set($this->selected['code']);
      
      if (!empty($_POST['set_language'])) {
        $this->set($_POST['set_language']);
        header('Location: '. $this->system->document->link());
        exit;
      }
    }
    
    //public function initiate() {
    //}
    
    public function startup() {
      
    // Import cached translations
      $this->_cache_id = $this->system->cache->cache_id('translations', array('language', 'basename'));
      $this->_cache = $this->system->cache->get($this->_cache_id, 'file');
      
      header('Content-Language: '. $this->selected['code']);
    }
    
    public function before_capture() {
      
      if (empty($this->selected['code'])) trigger_error('Error: No language set', E_USER_ERROR);
      
      $translations_query = $this->system->database->query(
        "select id, code, text_en, text_". $this->selected['code'] ." from ". DB_TABLE_TRANSLATIONS ."
        where find_in_set('". $this->system->database->input(str_replace(WS_DIR_HTTP_HOME, '', $_SERVER['SCRIPT_NAME'])) ."', pages)"
      );
      
      $translations = array();
      while ($row = $this->system->database->fetch($translations_query)) {
      
        if (!empty($row['text_'.$this->selected['code']])) {
          $this->_cache['translations'][$this->selected['code']][$row['code']] = $row['text_'.$this->selected['code']];
          
        } else if (!empty($row['text_en'])) {
          $this->_cache['translations'][$this->selected['code']][$row['code']] = $row['text_en'];
        }
        
        $translation_ids[] = $row['id'];
      }
      
      if (isset($translation_ids)) {
        $this->system->database->query(
          "update ". DB_TABLE_TRANSLATIONS ."
          set date_accessed = '". date('Y-m-d H:i:s') ."'
          where id in ('". implode('\',\'', $translation_ids) ."');"
        );
      }
    }
    
    //public function after_capture() {
    //}
    
    //public function prepare_output() {
    //}
    
    //public function before_output() {
    //}
    
    public function shutdown() {
      $this->system->cache->set($this->_cache_id, 'file', $this->_cache);
    }
    
    ######################################################################
    
    public function load() {
      
      $this->languages = array();
      
      $languages_query = $this->system->database->query(
        "select * from ". DB_TABLE_LANGUAGES ."
        where status
        order by priority, name;"
      );
      
      while ($row = $this->system->database->fetch($languages_query)) {
        $this->languages[$row['code']] = $row;
      }
    }
    
    public function set($code=null) {
      
      if (empty($code)) $code = $this->identify();
      
      if (!isset($this->languages[$code])) {
        trigger_error('Cannot set unsupported language ('. $code .')', E_USER_WARNING);
        $code = $this->identify();
      }
      
      $this->system->session->data['language'] = $this->languages[$code];
      setcookie('language_code', $code, (time()+3600*24)*30, WS_DIR_HTTP_HOME);
      
    // Set system locale
      if (!setlocale(LC_TIME, explode(',', $this->selected['locale']))) {
        trigger_error('Warning: Failed setting locale '. $this->selected['locale'] .' for '. $this->selected['code'], E_USER_WARNING);
      }
      
    // Chain select currency
      if (!empty($this->selected['currency_code'])) {
        if (!empty($this->system->currency->currencies[$this->selected['currency_code']])) {
          $this->system->currency->set($this->selected['currency_code']);
        }
      }
    }
    
    public function identify() {
      
    // Build list of supported languages
      $languages = array();
      foreach ($this->languages as $language) {
        if ($language['status']) {
          $languages[] = $language['code'];
        }
      }
      
    // Return language from URI
      $code = current(explode('/', substr($_SERVER['REQUEST_URI'], strlen(WS_DIR_HTTP_HOME))));
      if (in_array($code, $languages)) return $code;
      
    // Return language from session
      if (isset($this->selected['code']) && in_array($this->selected['code'], $languages)) return $this->selected['code'];
      
    // Return language from cookie
      if (isset($_COOKIE['language_code']) && in_array($_COOKIE['language_code'], $languages)) return $_COOKIE['language_code'];
      
    // Return language from browser
      if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browser_locales = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
      } elseif (isset($_SERVER['LC_CTYPE'])) {
        $browser_locales = explode(',', $_SERVER['LC_CTYPE']);
      } else {
        $browser_locales = array();
      }
      foreach ($browser_locales as $browser_locale) {
        if (preg_match('/('. implode('|', $languages) .')-?.*/', $browser_locale, $reg)) {
          if (!empty($reg[1])) return $reg[1];
        }
      }
      
    // Return default language
      if (isset($this->languages[$this->system->settings->get('default_language_code')])) return $this->system->settings->get('default_language_code');
      
    // Return system language
      if (isset($this->languages[$this->system->settings->get('store_language_code')])) return $this->system->settings->get('store_language_code');
      
    // Return first language
      return array_shift(array_keys($this->languages));
    }
    
    public function translate($code, $default='', $language_code='') {
      
      if (empty($language_code)) {
        if (empty($this->selected['code'])) $this->set($this->identify());
        $language_code = $this->selected['code'];
      }
      
    // Return from cache
      if (isset($this->_cache['translations'][$language_code][$code])) {
        return $this->_cache['translations'][$language_code][$code];
      }
      
    // Get translation from database
      $translations_query = $this->system->database->query(
        "select id, text_en, text_". $this->system->database->input($language_code) .", pages from ". DB_TABLE_TRANSLATIONS ."
        where code = '". $this->system->database->input($code) ."'
        limit 0, 1;"
      );
      $row = $this->system->database->fetch($translations_query);
      
    // Set translation
      if (!empty($row['text_'.$language_code])) {
        $translation = $row['text_'.$language_code];
      }
      
    // Get identical translation
      if (empty($translation) && (!empty($row['text_en']) || !empty($default))) {
      
        $secondary_translations_query = $this->system->database->query(
          "select * from ". DB_TABLE_TRANSLATIONS ."
          where text_". $this->system->database->input($language_code) ." != ''
          and binary text_en = '". $this->system->database->input(!empty($row['text_en']) ? $row['text_en'] : $default) ."'
          limit 1;"
        );
        $secondary_translation = $this->system->database->fetch($secondary_translations_query);
        
        if (!empty($secondary_translation)) {
          $this->system->database->query(
            "update ". DB_TABLE_TRANSLATIONS ."
            set text_". $this->system->database->input($language_code) ." = '". $this->system->database->input($secondary_translation['text_'.$language_code]) ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
            where code = '". $this->system->database->input($code) ."'
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
      
      $this->_cache['translations'][$language_code][$code] = $translation;
      
      $backtrace = current(debug_backtrace());
      $page = $this->system->database->input(substr($backtrace['file'], strlen(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME)));
      
      if (empty($row)) {
        $this->system->database->query(
          "insert into ". DB_TABLE_TRANSLATIONS ."
          (code, pages, text_en, date_created, date_updated)
          values('". $this->system->database->input($code) ."', '\'". str_replace(WS_DIR_HTTP_HOME, '', $this->system->database->input($_SERVER['SCRIPT_NAME'])) ."\',', '". $this->system->database->input($default) ."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
        );
        $row = array(
          'id' => $this->system->database->insert_id(),
          'text_en' => $default,
          'pages' => '\''.$page.'\'',
        );
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_TRANSLATIONS ."
        set date_accessed = '". date('Y-m-d H:i:s') ."'
        ". (!in_array($page, explode(',', trim($row['pages'],','))) ? ",pages = '". $this->system->database->input(implode(',', array_merge(array($page), explode(',', $row['pages'])))) ."'" : false) ."
        where id = '". $this->system->database->input($row['id']) ."';"
      );
        
      return $this->_cache['translations'][$language_code][$code];
    }
    
    public function number_format($number, $decimals=2) {
      return number_format($number, $decimals, $this->selected['decimal_point'], $this->selected['thousands_sep']);
    }
  }
  
?>