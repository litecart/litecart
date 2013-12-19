<?php
  
  class lib_document {
    
    public $template = '';
    public $layout = 'default';
    public $snippets = array();
    public $settings = array();
    
    public function __construct(&$system) {
    }
    
    //public function load_dependencies() {
    //}
    
    //public function initiate() {
    //}
    
    //public function startup() {
    //}
    
    public function before_capture() {
    
    // Set before-snippets
      $this->snippets['title'] = array($GLOBALS['system']->settings->get('store_name'));
      
      $this->snippets['head_tags']['jquery'] = '<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>' . PHP_EOL
                                             . '<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>' . PHP_EOL
                                             . '<script>' . PHP_EOL
                                             . '  if (window.jQuery === undefined) document.write(unescape("%3Cscript src=\''. WS_DIR_EXT .'jquery/jquery-1.10.2.min.js\'%3E%3C/script%3E"));' . PHP_EOL
                                             . '  if (jQuery.migrateTrace === undefined) document.write(unescape("%3Cscript src=\''. WS_DIR_EXT .'jquery/jquery-migrate-1.2.1.min.js\'%3E%3C/script%3E"));' . PHP_EOL
                                             . '</script>';
      
      $this->snippets['javascript'][] = '  $(document).ready(function(){' . PHP_EOL
                                                  . '    $("body").on("keyup", "input[data-type=\'number\'], input[data-type=\'decimal\'], input[data-type=\'currency\']", function(){' . PHP_EOL
                                                  . '      $(this).val($(this).val().replace(",", "."));' . PHP_EOL
                                                  . '    });' . PHP_EOL
                                                  . '  });';
      
    // Set regional data
      if ($GLOBALS['system']->settings->get('regional_settings_screen_enabled')) {
        
        if (empty($GLOBALS['system']->customer->data['id']) && empty($GLOBALS['system']->session->data['region_data_set']) && empty($_COOKIE['skip_set_region_data'])) {
          
          $GLOBALS['system']->functions->draw_fancybox('', array(
            'centerOnScroll' => true,
            'hideOnContentClick' => false,
            'href' => $GLOBALS['system']->document->link(WS_DIR_HTTP_HOME . 'select_region.php', array('redirect' => $_SERVER['REQUEST_URI'])),
            'modal' => true,
            'speedIn' => 600,
            'transitionIn' => 'fade',
            'transitionOut' => 'fade',
            'type' => 'ajax',
            'scrolling' => 'false',
          ));
          
          $GLOBALS['system']->session->data['skip_set_region_data'] = true;
          setcookie('skip_set_region_data', 'true', time() + (60*60*24*10), WS_DIR_HTTP_HOME);
        }
      }
      
      if (substr($GLOBALS['system']->link->relpath($GLOBALS['system']->link->get_base_link()), 0, strlen(WS_DIR_ADMIN)) == WS_DIR_ADMIN) {
        $this->template = $GLOBALS['system']->settings->get('store_template_admin');
      } else {
        $this->template = $GLOBALS['system']->settings->get('store_template_catalog');
      }
    }
    
    public function after_capture() {
    
    // Set after-snippets
      $this->snippets['language'] = $GLOBALS['system']->language->selected['code'];
      $this->snippets['charset'] = $GLOBALS['system']->language->selected['charset'];
      $this->snippets['home_path'] = WS_DIR_HTTP_HOME;
      $this->snippets['template_path'] = WS_DIR_TEMPLATES . $this->template .'/';
    }
    
    public function prepare_output() {
      
    // Prepare title
      if (!is_array($this->snippets['title'])) $this->snippets['title'] = array($this->snippets['title']);
      $this->snippets['title'] = implode(' | ', array_reverse($this->snippets['title']));
      
    // Prepare javascript
      if (isset($this->snippets['javascript'])) {
        $this->snippets['javascript'] = '<script>' . PHP_EOL
                                      . implode(PHP_EOL . PHP_EOL, $this->snippets['javascript']) . PHP_EOL
                                      . '</script>' . PHP_EOL;
      }
      
    // Sort head tags
      if (!empty($this->snippets['head_tags'])) asort($this->snippets['head_tags']);
      
    // Prepare snippets
      foreach (array_keys($this->snippets) as $snippet) {
        if (is_array($this->snippets[$snippet])) $this->snippets[$snippet] = implode(PHP_EOL, $this->snippets[$snippet]);
      }
    }
    
    //public function before_output() {
    //}
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function expires($string=false) {
      if (strtotime($string) > time()) {
        header('Pragma:');
        header('Cache-Control: max-age='. (strtotime($string) - time()));
        header('Expires: '. date('r', strtotime($string)));
        $this->snippets['head_tags']['meta_expire'] = '<meta http-equiv="cache-control" content="public">' .PHP_EOL
                                                    . '<meta http-equiv="expires" content="'. date('r', strtotime($string)) .'">';
      } else {
        header('Cache-Control: no-cache');
        $this->snippets['head_tags']['meta_expire'] = '<meta http-equiv="cache-control" content="no-cache">' . PHP_EOL
                                                    . '<meta http-equiv="expires" content="'. date('r', strtotime($string)) .'">';
      }
    }
    
    public function stitch(&$html) {
      
      foreach ($this->snippets as $key => $replace) {
      
        if (is_array($replace)) $replace = implode(PHP_EOL, $replace);
        
        $search = array(
          '{snippet:'.$key.'}',
          '<!--snippet:'.$key.'-->',
        );
        $html = str_replace($search, $replace, $html, $replacements);
        
        if ($replacements) unset($this->snippets[$key]);
      }
      
      $html = preg_replace($search, '', $html);
    }
    
    public function href_link($document=null, $new_params=array(), $inherit_params=false, $skip_params=array(), $language_code=null) {
      return htmlspecialchars($this->link($document, $new_params, $inherit_params, $skip_params, $language_code));
    }
    
  // Substituted
    public function link($document=null, $new_params=array(), $inherit_params=false, $skip_params=array(), $language_code=null) {
      return $GLOBALS['system']->link->create_link($document, $new_params, $inherit_params, $skip_params, $language_code);
    }
  }
  
?>