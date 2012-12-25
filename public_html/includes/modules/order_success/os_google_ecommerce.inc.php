<?php

  class os_google_ecommerce {
    public $id = __CLASS__;
    public $name = 'Google Analytics E-commerce Tracking';
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
      
    // Tweak per domain
      switch (substr($_SERVER['HTTP_HOST'], strrpos($_SERVER['HTTP_HOST'], '.'))) {
        case '.dk':
          $this->settings['account_id'] = 'UA-35148917-2';
          break;
        case '.no':
          $this->settings['account_id'] = 'UA-35148917-3';
          break;
        case '.se':
          $this->settings['account_id'] = 'UA-35148917-1';
          break;
      }
      
      $output = '<!-- BOF: '. $this->name .' -->' . PHP_EOL
              . '<script type="text/javascript">' . PHP_EOL
              . '  var _gaq = _gaq || [];' . PHP_EOL
              . '  _gaq.push(["_setAccount", "'. $this->settings['account_id'] .'"]);' . PHP_EOL
              . '  _gaq.push(["_trackPageview"]);' . PHP_EOL
              . '  _gaq.push(["_addTrans",' . PHP_EOL
              . '    "'. $order->data['id'] .'",' . PHP_EOL           // order ID - required
              . '    "'. $this->system->settings->get('store_name') .'",' . PHP_EOL  // affiliation or store name
              . '    "'. ($order->data['payment_due']) .'",' . PHP_EOL          // total - required
              . '    "'. $order->data['tax']['total'] .'",' . PHP_EOL           // tax
              . '    "'. (!empty($order->data['order_total']['ot_shipping_fee']['value']) ? $order->data['order_total']['ot_shipping_fee']['value'] + $order->data['order_total']['ot_shipping_fee']['tax'] : '0') .'",' . PHP_EOL              // shipping
              . '    "'. $order->data['customer']['city'] .'",' . PHP_EOL       // city
              . '    "'. $this->system->functions->reference_get_zone_name($order->data['customer']['country_code'], $order->data['customer']['zone_code']) .'",' . PHP_EOL     // state or province
              . '    "'. $this->system->functions->reference_get_country_name($order->data['customer']['country_code']) .'"' . PHP_EOL             // country
              . '  ]);' . PHP_EOL;
              
      foreach (array_keys($order->data['items']) as $key) {
        $output .= '  _gaq.push(["_addItem",' . PHP_EOL
                 . '    "'. $order->data['id'] .'",' . PHP_EOL           // order ID - required
                 . '    "'. $order->data['items'][$key]['product_id'] .'",' . PHP_EOL           // SKU/code - required
                 . '    "'. $order->data['items'][$key]['name'] .'",' . PHP_EOL       // product name
                 . '    "",' . PHP_EOL     // category or variation
                 . '    "'. $order->data['items'][$key]['price'] .'",' . PHP_EOL             // unit price - required
                 . '    "'. $order->data['items'][$key]['quantity'] .'"' . PHP_EOL             // quantity - required
                 . '  ]);' . PHP_EOL;
      }
      
      foreach (array_keys($order->data['order_total']) as $key) {
        if (empty($order->data['order_total'][$key]['calculate'])) continue;
        $output .= '  _gaq.push(["_addItem",' . PHP_EOL
                 . '    "'. $order->data['id'] .'",' . PHP_EOL           // order ID - required
                 . '    "'. $key .'",' . PHP_EOL           // SKU/code - required
                 . '    "'. $order->data['order_total'][$key]['title'] .'",' . PHP_EOL       // product name
                 . '    "",' . PHP_EOL     // category or variation
                 . '    "'. $order->data['order_total'][$key]['value'] .'",' . PHP_EOL             // unit price - required
                 . '    "1"' . PHP_EOL             // quantity - required
                 . '  ]);' . PHP_EOL;
      }
              
      $output .= '  (function() {' . PHP_EOL
               . '    var ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true;' . PHP_EOL
               . '    ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";' . PHP_EOL
               . '    var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ga, s);' . PHP_EOL
               . '  })();' . PHP_EOL
               . '</script>' . PHP_EOL
               . '<!-- EOF: '. $this->name .' -->' . PHP_EOL;
      
      return $output;
    }
    
    function settings() {
      
      return array(
        array(
          'key' => 'status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_status', 'Status'),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'account_id',
          'default_value' => 'UA-XXXXX-X',
          'title' => $this->system->language->translate(__CLASS__.':title_account_id', 'Account ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_account_id', 'Your Google Analytics account ID.'),
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