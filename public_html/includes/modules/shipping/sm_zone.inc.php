<?php

  class sm_zone {
    private $system;
    public $id = __CLASS__;
    public $name = 'Zone Based Shipping';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'http://www.forum.com';
    public $website = 'http://www.site.com';
    
    public function __construct() {
      global $system;
      $this->system = $system;
      
      $this->name = $this->system->language->translate(__CLASS__.':title_zone_based_shipping', 'Zone Based Shipping');
    }
    
    public function options($items, $subtotal, $tax, $currency_code, $customer) {
      
      if ($this->settings['status'] != 'Enabled') return;
      
      $options = array();
      
      for ($i=1; $i <= 3; $i++) {
        if (empty($this->settings['geo_zone_id_'.$i])) continue;
        
        if (!$this->system->functions->reference_in_geo_zone($this->settings['geo_zone_id_'.$i], $customer['shipping_address']['country_code'], $customer['shipping_address']['zone_code'])) continue;
        
        $options[] = array(
          'id' => 'zone_'.$i,
          'icon' => $this->settings['icon'],
          'name' => $this->system->language->translate('title_flat_rate', 'Flat Rate'),
          'description' => $this->system->functions->reference_get_country_name($customer['country_code']),
          'fields' => '',
          'cost' => $this->settings['cost_'.$i],
          'tax_class_id' => $this->settings['tax_class_id'],
        );
      }
      
      if (empty($options)) {
        if ($this->settings['cost_x'] == 0) {
          return;
        } else {
          $options[] = array(
            'id' => 'zone_x',
            'icon' => $this->settings['icon'],
            'name' => $this->system->language->translate('title_flat_rate', 'Flat Rate'),
            'description' => $this->system->functions->reference_get_country_name($customer['country_code']),
            'fields' => '',
            'cost' => $this->settings['cost_x'],
            'tax_class_id' => $this->settings['tax_class_id'],
          );
        }
      }
      
      $options = array(
        'title' => $this->name,
        'options' => $options,
      );
      
      return $options;
    }
    
    public function before_select() {}
    
    public function before_process() {}
    
    public function after_process() {}
    
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
          'key' => 'icon',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_icon', 'Icon'),
          'description' => $this->system->language->translate(__CLASS__.':description_icon', 'Web path of the icon to be displayed.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'geo_zone_id_1',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_zone', 'Zone') .' 1: '. $this->system->language->translate(__CLASS__.':title_geo_zone', 'Geo Zone'),
          'description' => $this->system->language->translate(__CLASS__.':description_geo_zone', 'Geo zone to which the cost applies.'),
          'function' => 'geo_zones()',
        ),
        array(
          'key' => 'cost_1',
          'default_value' => '0.00',
          'title' => $this->system->language->translate(__CLASS__.':title_zone', 'Zone') .' 1: '. $this->system->language->translate(__CLASS__.':title_cost', 'Cost'),
          'description' => $this->system->language->translate(__CLASS__.':description_title_cost', 'The shipping cost excluding tax for the zone option.'),
          'function' => 'decimal()',
        ),
        array(
          'key' => 'geo_zone_id_2',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_zone', 'Zone') .' 2: '. $this->system->language->translate(__CLASS__.':title_geo_zone', 'Geo Zone'),
          'description' => $this->system->language->translate(__CLASS__.':description_geo_zone', 'Geo zone to which the cost applies.'),
          'function' => 'geo_zones()',
        ),
        array(
          'key' => 'cost_2',
          'default_value' => '0.00',
          'title' => $this->system->language->translate(__CLASS__.':title_zone', 'Zone') .' 2: '. $this->system->language->translate(__CLASS__.':title_cost', 'Cost'),
          'description' => $this->system->language->translate(__CLASS__.':description_title_cost', 'The shipping cost excluding tax for the zone option.'),
          'function' => 'decimal()',
        ),
        array(
          'key' => 'geo_zone_id_3',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_zone', 'Zone') .' 3: '. $this->system->language->translate(__CLASS__.':title_geo_zone', 'Geo Zone'),
          'description' => $this->system->language->translate(__CLASS__.':description_geo_zone', 'Geo zone to which the cost applies.'),
          'function' => 'geo_zones()',
        ),
        array(
          'key' => 'cost_3',
          'default_value' => '0.00',
          'title' => $this->system->language->translate(__CLASS__.':title_zone', 'Zone') .' 3: '. $this->system->language->translate(__CLASS__.':title_cost', 'Cost'),
          'description' => $this->system->language->translate(__CLASS__.':description_title_cost', 'The shipping cost excluding tax for the zone option.'),
          'function' => 'decimal()',
        ),
        array(
          'key' => 'cost_x',
          'default_value' => '0.00',
          'title' => $this->system->language->translate(__CLASS__.':title_non_matched_zones', 'Non-matched Zones') .': '. $this->system->language->translate(__CLASS__.':title_cost', 'Cost'),
          'description' => $this->system->language->translate(__CLASS__.':description_title_cost_x', 'The shipping cost excluding tax for any zones not matched above.'),
          'function' => 'decimal()',
        ),
        array(
          'key' => 'tax_class_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_tax_class', 'Tax Class'),
          'description' => $this->system->language->translate(__CLASS__.':description_tax_class', 'The tax class for the shipping cost.'),
          'function' => 'tax_classes()',
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