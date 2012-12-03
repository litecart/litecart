<?php
  
  class sm_free {
    private $system;
    public $id = __CLASS__;
    public $name = 'Free Shipping';
    public $description = '';
    public $author = 'ACME Corporation';
    public $version = '1.0';
    public $support_link = 'http://www.forum.com';
    public $website = 'http://www.site.com';
    
    public function __construct() {
      global $system;
      $this->system = $system;
      
      $this->name = $this->system->language->translate(__CLASS__.':title_free_shipping', 'Free Shipping');
    }
    
    public function options($items, $subtotal, $tax, $currency_code, $customer) {
      
      if ($this->settings['status'] != 'Enabled') return;
      
    // If destination is not in geo zone
      if (!empty($this->settings['geo_zone_id'])) {
        if (!$this->system->functions->reference_in_geo_zone($this->settings['geo_zone_id'], $customer['shipping_address']['country_code'], $customer['shipping_address']['zone_code'])) return;
      }
      
    // Calculate cart total
      $subtotal = 0;
      foreach ($items as $item) {
        $subtotal += $item['quantity'] * $item['price'];
      }
      
    // If below minimum amount
      if ($subtotal < $this->settings['minimum_amount']) return;
      
    // Set options
      $options = array(
        'title' => $this->name,
        'options' => array(
          array(
            'id' => 'free',
            'icon' => '',
            'name' => $this->system->language->translate('title_free', 'Free'),
            'description' => sprintf('Free shipping for orders above %s (excluding tax).', $this->system->currency->format($this->settings['minimum_amount'])),
            'fields' => '',
            'cost' => 0,
            'tax_class_id' => 0,
          ),
        )
      );
      
    // Return options
      return $options;
    }
    
    public function before_select() {}
    
    public function before_process() {}
    
    public function after_process() {}
    
    public function settings() {
      return array(
        array(
          'key' => 'status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_status', 'Status'),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'icon',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_icon', 'Icon'),
          'description' => $this->system->language->translate(__CLASS__.':description_icon', 'Web path of the icon to be displayed.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'minimum_amount',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_minimum_cart_amount', 'Minimum Cart Amount'),
          'description' => $this->system->language->translate(__CLASS__.':description_minimum_cart_amount', 'Enable free shipping for orders above the given subtotal amount (excluding tax).'),
          'function' => 'decimal()',
        ),
        array(
          'key' => 'geo_zone_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_geo_zone_limitation', 'Geo Zone Limitation'),
          'description' => $this->system->language->translate(__CLASS__.':description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.'),
          'function' => 'geo_zones()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_priority', 'Priority'),
          'description' => $this->system->language->translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
  
?>