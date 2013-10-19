<?php

  class sm_weight_table {
    public $id = __CLASS__;
    public $name = 'Weight Based Shipping';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'http://www.litecart.net';
    
    public function __construct() {
      
      $this->name = language::translate(__CLASS__.':title_weight_based_shipping', 'Weight Based Shipping');
    }
    
    public function options($items, $subtotal, $tax, $currency_code, $customer) {
      
      if (empty($this->settings['status'])) return;
      
    // If destination is not in geo zone
      if (!empty($this->settings['geo_zone_id'])) {
        if (!functions::reference_in_geo_zone($this->settings['geo_zone_id'], $customer['shipping_address']['country_code'], $customer['shipping_address']['zone_code'])) return;
      }
      
    // Calculate cart total
      $weight = 0;
      foreach ($items as $item) {
        $weight += weight::convert($item['quantity'] * $item['weight'], $item['weight_class'], $this->settings['weight_class']);
      }
      
      $cost = $this->calculate_cost($weight);
      
      $options = array(
        'title' => $this->name,
        'options' => array(
          array(
            'id' => '1',
            'icon' => $this->settings['icon'],
            'name' => language::translate(__CLASS__.':title_option_name_1', 'Cost by Weight'),
            'description' => language::number_format($weight) .' '. $this->settings['weight_class'],
            'fields' => '',
            'cost' => $cost,
            'tax_class_id' => $this->settings['tax_class_id'],
          ),
        )
      );
      
      return $options;
    }
    
    private function calculate_cost($shipping_weight) {
      
      if (empty($this->settings['rate_table'])) return 0;
      
      $rate_table = explode(";" , $this->settings['rate_table']);
      foreach ($rate_table as $rate) {
        list($rate_weight, $rate_cost) = explode(':', $rate);
        if (!isset($cost) || $shipping_weight >= $rate_weight) {
          $cost = $rate_cost;
        }
      }
      
      return $cost;
    }
    
    public function before_select() {}
    
    public function before_process() {}
    
    public function after_process() {}
    
    function settings() {
      return array(
        array(
          'key' => 'status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_status', 'Status'),
          'description' => language::translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'toggle("e/d")',
        ),
        array(
          'key' => 'icon',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_icon', 'Icon'),
          'description' => language::translate(__CLASS__.':description_icon', 'Web path of the icon to be displayed.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'rate_table',
          'default_value' => '5:8.95;10:15.95',
          'title' => language::translate(__CLASS__.':title_weight_rate_table', 'Weight Rate Table'),
          'description' => language::translate(__CLASS__.':description_weight_rate_table', 'Ascending rate table of the shipping cost. The format must be weight:cost;weight:cost;.. (I.e. 5:8.95;10:15.95;..)'),
          'function' => 'mediumtext()',
        ),
        array(
          'key' => 'weight_class',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_weight_class', 'Weight Class'),
          'description' => language::translate(__CLASS__.':description_weight_class', 'The weight class for the rate table.'),
          'function' => 'weight_classes()',
        ),
        array(
          'key' => 'tax_class_id',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_tax_class', 'Tax Class'),
          'description' => language::translate('modules:description_tax_class', 'The tax class for the shipping cost.'),
          'function' => 'tax_classes()',
        ),
        array(
          'key' => 'geo_zone_id',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_geo_zone_limitation', 'Geo Zone Limitation'),
          'description' => language::translate(__CLASS__.':description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.'),
          'function' => 'geo_zones()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
    
?>