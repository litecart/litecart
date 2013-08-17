<?php

  class sm_flat_rate {
    public $id = __CLASS__;
    public $name = 'Flat Rate';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'http://www.litecart.net';
    
    public function __construct() {
      
      $this->name = $GLOBALS['system']->language->translate(__CLASS__.':title_flat_rate_shipping', 'Flat Rate Shipping');
    }
    
    public function options($items, $subtotal, $tax, $currency_code, $customer) {
      
      if (empty($this->settings['status'])) return;
      
    // If destination is not in geo zone
      if (!empty($this->settings['geo_zone_id'])) {
        if (!$GLOBALS['system']->functions->reference_in_geo_zone($this->settings['geo_zone_id'], $customer['shipping_address']['country_code'], $customer['shipping_address']['zone_code'])) return;
      }
      
      $options = array(
        'title' => $GLOBALS['system']->language->translate(__CLASS__.':title_flat_rate_shipping', 'Flat Rate Shipping'),
        'options' => array(
          array(
            'id' => 'flat',
            'icon' => $this->settings['icon'],
            'name' => $GLOBALS['system']->language->translate('title_flat_rate', 'Flat Rate'),
            'description' => '',
            'fields' => '',
            'cost' => $this->settings['cost'],
            'tax_class_id' => $this->settings['tax_class_id'],
          ),
        )
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
          'default_value' => '1',
          'title' => $GLOBALS['system']->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $GLOBALS['system']->language->translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'toggle("e/d")',
        ),
        array(
          'key' => 'icon',
          'default_value' => '',
          'title' => $GLOBALS['system']->language->translate(__CLASS__.':title_icon', 'Icon'),
          'description' => $GLOBALS['system']->language->translate(__CLASS__.':description_icon', 'Web path of the icon to be displayed.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'cost',
          'default_value' => '0',
          'title' => $GLOBALS['system']->language->translate(__CLASS__.':title_cost', 'Cost'),
          'description' => $GLOBALS['system']->language->translate(__CLASS__.':description_cost', 'The shipping cost excluding tax.'),
          'function' => 'currency()',
        ),
        array(
          'key' => 'tax_class_id',
          'default_value' => '',
          'title' => $GLOBALS['system']->language->translate(__CLASS__.':title_tax_class', 'Tax Class'),
          'description' => $GLOBALS['system']->language->translate(__CLASS__.':description_tax_class', 'The tax class for the shipping cost.'),
          'function' => 'tax_classes()',
        ),
        array(
          'key' => 'geo_zone_id',
          'default_value' => '',
          'title' => $GLOBALS['system']->language->translate(__CLASS__.':title_geo_zone_limitation', 'Geo Zone Limitation'),
          'description' => $GLOBALS['system']->language->translate(__CLASS__.':description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.'),
          'function' => 'geo_zones()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => $GLOBALS['system']->language->translate(__CLASS__.':title_priority', 'Priority'),
          'description' => $GLOBALS['system']->language->translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
    
?>