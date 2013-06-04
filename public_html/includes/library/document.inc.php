<?php
  
  class document {
    
    private $system;
    private $cache = array();
    public $template = '';
    public $layout = 'default';
    public $snippets = array();
    
    public function __construct(&$system) {
      $this->system = &$system;
    }
    
    //public function load_dependencies() {
    //}
    
    //public function initiate() {
    //}
    
    //public function startup() {
    //}
    
    public function before_capture() {
    
    // Set before-snippets
      $this->snippets['title'] = array($this->system->settings->get('store_name'));
      
    // Set regional data
      if ($this->system->settings->get('regional_settings_screen_enabled') == 'true') {
        
        if (empty($this->system->customer->data['id']) && empty($this->system->session->data['region_data_set']) && empty($_COOKIE['skip_set_region_data'])) {
          
          $this->system->functions->draw_fancybox('', array(
            'centerOnScroll' => true,
            'hideOnContentClick' => false,
            'href' => $this->system->document->link(WS_DIR_HTTP_HOME . 'select_region.php', array('redirect' => $_SERVER['REQUEST_URI'])),
            'modal' => true,
            'speedIn' => 600,
            'transitionIn' => 'fade',
            'transitionOut' => 'fade',
            'type' => 'ajax',
            'scrolling' => 'false',
          ));
          
          $this->system->session->data['skip_set_region_data'] = true;
          setcookie('skip_set_region_data', 'true', time() + (60*60*24*10), WS_DIR_HTTP_HOME);
        }
      }
      
      if (substr($this->system->link->relpath($this->system->link->get_base_link()), 0, strlen(WS_DIR_ADMIN)) == WS_DIR_ADMIN) {
        $this->template = $this->system->settings->get('store_template_admin');
      } else {
        $this->template = $this->system->settings->get('store_template_catalog');
      }
    }
    
    public function after_capture() {
    
    // Set after-snippets
      $this->snippets['language'] = $this->system->language->selected['code'];
      $this->snippets['charset'] = $this->system->language->selected['charset'];
      $this->snippets['home_path'] = WS_DIR_HTTP_HOME;
      $this->snippets['template_path'] = WS_DIR_TEMPLATES . $this->template .'/';
    }
    
    public function prepare_output() {
      
    // Prepare title
      if (!is_array($this->snippets['title'])) $this->snippets['title'] = array($this->snippets['title']);
      $this->snippets['title'] = implode(' | ', array_reverse($this->snippets['title']));
      
    // Prepare javascript
      if (isset($this->snippets['javascript'])) {
        $this->snippets['javascript'] = '<script type="text/javascript">' . PHP_EOL
                                      . '<!--' . PHP_EOL
                                      . implode(PHP_EOL . PHP_EOL, $this->snippets['javascript']) . PHP_EOL
                                      . '//-->' . PHP_EOL
                                      . '</script>' . PHP_EOL;
      }
      
    // Sort head tags
      if (!empty($this->snippets['head_tags'])) asort($this->snippets['head_tags']);
      
    // Prepare snippets
      foreach (array_keys($this->snippets) as $snippet) {
        if (is_array($this->snippets[$snippet])) $this->snippets[$snippet] = implode(PHP_EOL, $this->snippets[$snippet]);
      }
    }
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function expires($string=false) {
      if (strtotime($string) > mktime()) {
        header('Pragma:');
        header('Cache-Control: max-age='. (strtotime($string) - mktime()));
        header('Expires: '. date('r', strtotime($string)));
        $this->snippets['head_tags']['meta_expire'] = '<meta http-equiv="cache-control" content="public">' .PHP_EOL
                                                    . '<meta http-equiv="expires" content="'. date('r', strtotime($string)) .'">';
      } else {
        header('Cache-Control: no-cache');
        $this->snippets['head_tags']['meta_expire'] = '<meta http-equiv="cache-control" content="no-cache">' . PHP_EOL
                                                    . '<meta http-equiv="expires" content="'. date('r', strtotime($string)) .'">';
      }
    }
    
    public function href_link($document=null, $new_params=array(), $inherit_params=false, $skip_params=array(), $language_code=null) {
      return htmlspecialchars($this->link($document, $new_params, $inherit_params, $skip_params, $language_code));
    }
    
  // Substituted
    public function link($document=null, $new_params=array(), $inherit_params=false, $skip_params=array(), $language_code=null) {
      return $this->system->link->create_link($document, $new_params, $inherit_params, $skip_params, $language_code);
    }
  }
  
?>