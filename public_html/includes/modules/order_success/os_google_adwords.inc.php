<?php

  class os_google_adwords {
    public $id = __CLASS__;
    public $name = 'Google AdWords Conversion Tracking';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'http://adwords.google.com';
    public $website = 'http://adwords.google.com';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = &$system;
    }
    
    public function process() {
      global $order;
      
      if ($this->settings['status'] != 'Enabled') return;
      
    /*
    // Tweak per top-domain
      switch (substr($_SERVER['HTTP_HOST'], strrpos($_SERVER['HTTP_HOST'], '.'))) {
        case '.dk':
          $this->settings['account_id'] = '';
          break;
        case '.no':
          $this->settings['account_id'] = '';
          break;
        case '.se':
          $this->settings['account_id'] = '';
          break;
      }
    */
      
      $output = '<!-- BOF: '. $this->name .' -->' . PHP_EOL
              . '<script type="text/javascript">' . PHP_EOL
              . '  var google_conversion_id = '. (int)$this->settings['conversion_id'] .';' . PHP_EOL
              . '  var google_conversion_language = "'. $this->system->language->selected['code'] .'";' . PHP_EOL
              . '  var google_conversion_format = "'. $this->settings['conversion_format'] .'";' . PHP_EOL
              . '  var google_conversion_color = "ffffff";' . PHP_EOL
              . '  var google_conversion_label = "'. $this->settings['conversion_label'] .'";' . PHP_EOL
              . '  var google_conversion_value = '. $order->data['payment_due'] .';' . PHP_EOL
              . '<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js"></script>' . PHP_EOL
              . '<!-- EOF: '. $this->name .' -->' . PHP_EOL;
      
      return $output;
    }
    
    function settings() {
      
      return array(
        array(
          'key' => 'status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'conversion_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_conversion_id', 'Conversion ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_conversion_id', 'Your Google AdWords conversion ID.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'conversion_format',
          'default_value' => '1',
          'title' => $this->system->language->translate(__CLASS__.':title_conversion_format', 'Conversion Format'),
          'description' => $this->system->language->translate(__CLASS__.':description_conversion_id', 'Google AdWords conversion format.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'conversion_color',
          'default_value' => 'ffffff',
          'title' => $this->system->language->translate(__CLASS__.':title_conversion_color', 'Conversion Color'),
          'description' => $this->system->language->translate(__CLASS__.':description_conversion_id', 'Google AdWords conversion color.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'conversion_label',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_conversion_id', 'Conversion Label'),
          'description' => $this->system->language->translate(__CLASS__.':description_conversion_label', 'Google Analytics conversion label.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_priority', 'Priority'),
          'description' => $this->system->language->translate(__CLASS__.':description_module_priority', 'Process this module in the given priority order.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
  
?>