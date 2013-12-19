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
      
      self::$snippets['head_tags']['jquery'] = '<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>' . PHP_EOL
                                             . '<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>' . PHP_EOL
                                             . '<script>' . PHP_EOL
                                             . '  if (window.jQuery === undefined) document.write(unescape("%3Cscript src=\''. WS_DIR_EXT .'jquery/jquery-1.10.2.min.js\'%3E%3C/script%3E"));' . PHP_EOL
                                             . '  if (jQuery.migrateTrace === undefined) document.write(unescape("%3Cscript src=\''. WS_DIR_EXT .'jquery/jquery-migrate-1.2.1.min.js\'%3E%3C/script%3E"));' . PHP_EOL
                                             . '</script>';
      
      self::$snippets['javascript'][] = '  $(document).ready(function(){' . PHP_EOL
                                                  . '    $("body").on("keyup", "input[data-type=\'number\'], input[data-type=\'decimal\'], input[data-type=\'currency\']", function(){' . PHP_EOL
                                                  . '      $(this).val($(this).val().replace(",", "."));' . PHP_EOL
                                                  . '    });' . PHP_EOL
                                                  . '  });';
      
    // Set regional data
      if (settings::get('regional_settings_screen_enabled')) {
        
        if (empty(customer::$data['id']) && empty(session::$data['region_data_set']) && empty($_COOKIE['skip_set_region_data'])) {
          
          functions::draw_fancybox('', array(
            'centerOnScroll' => true,
            'hideOnContentClick' => false,
            'href' => document::link(WS_DIR_HTTP_HOME . 'select_region.php', array('redirect' => $_SERVER['REQUEST_URI'])),
            'modal' => true,
            'speedIn' => 600,
            'transitionIn' => 'fade',
            'transitionOut' => 'fade',
            'type' => 'ajax',
            'scrolling' => 'false',
          ));
          
          session::$data['skip_set_region_data'] = true;
          setcookie('skip_set_region_data', 'true', time() + (60*60*24*10), WS_DIR_HTTP_HOME);
        }
      }
      
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
      if (!is_array(self::$snippets['title'])) self::$snippets['title'] = array(self::$snippets['title']);
      self::$snippets['title'] = implode(' | ', array_reverse(self::$snippets['title']));
      
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