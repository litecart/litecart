<?php
  
  class document {
    
    private static $_cache = array();
    public static $template = '';
    public static $layout = 'default';
    public static $snippets = array();
    public static $settings = array();
    
    public static function construct() {
    }
    
    //public static function load_dependencies() {
    //}
    
    //public static function initiate() {
    //}
    
    //public static function startup() {
    //}
    
    public static function before_capture() {
    
    // Set before-snippets
      self::$snippets['title'] = array(settings::get('store_name'));
      
      self::$snippets['head_tags']['jquery'] = '<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>' . PHP_EOL
                                             . '<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>' . PHP_EOL
                                             . '<script>' . PHP_EOL
                                             . '  if (window.jQuery === undefined) document.write(unescape("%3Cscript src=\''. WS_DIR_EXT .'jquery/jquery-1.11.1.min.js\'%3E%3C/script%3E"));' . PHP_EOL
                                             . '  if (jQuery.migrateTrace === undefined) document.write(unescape("%3Cscript src=\''. WS_DIR_EXT .'jquery/jquery-migrate-1.2.1.min.js\'%3E%3C/script%3E"));' . PHP_EOL
                                             . '</script>';
      
    // Set template
      if (substr(link::relpath(link::get_base_link()), 0, strlen(WS_DIR_ADMIN)) == WS_DIR_ADMIN) {
        self::$template = settings::get('store_template_admin');
      } else {
        self::$template = settings::get('store_template_catalog');
      }
    }
    
    public static function after_capture() {
    
    // Set after-snippets
      self::$snippets['language'] = language::$selected['code'];
      self::$snippets['charset'] = language::$selected['charset'];
      self::$snippets['home_path'] = WS_DIR_HTTP_HOME;
      self::$snippets['template_path'] = WS_DIR_TEMPLATES . self::$template .'/';
    }
    
    public static function prepare_output() {
      
    // Prepare title
      if (!empty(self::$snippets['title'])) {
        if (!is_array(self::$snippets['title'])) self::$snippets['title'] = array(self::$snippets['title']);
        self::$snippets['title'] = implode(' | ', array_reverse(self::$snippets['title']));
      }
      
    // Prepare javascript
      if (isset(self::$snippets['javascript'])) {
        self::$snippets['javascript'] = '<script>' . PHP_EOL
                                      . implode(PHP_EOL . PHP_EOL, self::$snippets['javascript']) . PHP_EOL
                                      . '</script>' . PHP_EOL;
      }
      
    // Sort head tags
      if (!empty(self::$snippets['head_tags'])) asort(self::$snippets['head_tags']);
      
    // Prepare snippets
      foreach (array_keys(self::$snippets) as $snippet) {
        if (is_array(self::$snippets[$snippet])) self::$snippets[$snippet] = implode(PHP_EOL, self::$snippets[$snippet]);
      }
    }
    
    public static function before_output() {
      
    // Get template settings
      self::$settings = unserialize(settings::get('store_template_catalog_settings'));
      
    // Clean orphan snippets
      $search = array(
        '/\{snippet:[^\}]+\}/',
        '/<!--snippet:[^-->]+-->/',
      );
      
      $GLOBALS['output'] = preg_replace($search, '', $GLOBALS['output']);
    }
    
    //public static function shutdown() {
    //}
    
    ######################################################################
    
    public static function expires($string=false) {
      if (strtotime($string) > time()) {
        header('Pragma:');
        header('Cache-Control: max-age='. (strtotime($string) - time()));
        header('Expires: '. date('r', strtotime($string)));
        self::$snippets['head_tags']['meta_expire'] = '<meta http-equiv="cache-control" content="public">' .PHP_EOL
                                                    . '<meta http-equiv="expires" content="'. date('r', strtotime($string)) .'">';
      } else {
        header('Cache-Control: no-cache');
        self::$snippets['head_tags']['meta_expire'] = '<meta http-equiv="cache-control" content="no-cache">' . PHP_EOL
                                                    . '<meta http-equiv="expires" content="'. date('r', strtotime($string)) .'">';
      }
    }
    
    public static function stitch(&$html) {
      
      foreach (self::$snippets as $key => $replace) {
      
        if (is_array($replace)) $replace = implode(PHP_EOL, $replace);
        
        $search = array(
          '{snippet:'.$key.'}',
          '<!--snippet:'.$key.'-->',
        );
        $html = str_replace($search, $replace, $html, $replacements);

        $html = str_replace($search, $replace, $html, $replacements);
        
        if ($replacements) unset(self::$snippets[$key]);
      }
      
      $html = preg_replace($search, '', $html);
    }
    
    public static function link($document=null, $new_params=array(), $inherit_params=false, $skip_params=array(), $language_code=null) {
      return link::create_link($document, $new_params, $inherit_params, $skip_params, $language_code);
    }

    public static function href_link($document=null, $new_params=array(), $inherit_params=false, $skip_params=array(), $language_code=null) {
      return htmlspecialchars(self::link($document, $new_params, $inherit_params, $skip_params, $language_code));
    }
  }
  
?>