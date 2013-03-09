<?php

  class pm_invoice {
    private $system;
    public $id = __CLASS__;
    public $name = 'Invoice';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'http://www.forum.com';
    public $website = 'http://www.site.com';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = &$system;
    }
    
    public function options() {
    
      if ($this->settings['status'] != 'Enabled') return;
      
      if (!empty($this->settings['geo_zone_id'])) {
        if (!$this->system->functions->reference_in_geo_zone($this->settings['geo_zone_id'], $destination['country_code'], $destination['zone_code'])) return;
      }
      
      $method = array(
        'title' => 'Invoice',
        'options' => array(
          array(
            'id' => 'invoice',
            'icon' => $this->settings['icon'],
            'name' => $this->system->language->translate(__CLASS__.':title_invoice', 'Invoice'),
            'description' => '',
            'fields' => '',
            'cost' => $this->settings['fee'],
            'tax_class_id' => $this->settings['tax_class_id'],
            'confirm' => $this->system->language->translate(__CLASS__.':title_confirm_order', 'Confirm Order'),
          ),
        )
      );
      
      return $method;
    }
    
    public function pre_check() {
    }
    
    public function transfer() {
      return array(
        'action' => '',
        'method' => '',
        'fields' => '',
      );
    }
    
    public function verify() {
      return array(
        'order_status_id' => $this->settings['order_status_id'],
        'payment_transaction_id' => '',
        'errors' => '',
      );
    }
    
    public function after_process() {
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
          'key' => 'icon',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_icon', 'Icon'),
          'description' => $this->system->language->translate(__CLASS__.':description_icon', 'Web path of the icon to be displayed.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'fee',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_payment_fee', 'Payment Fee'),
          'description' => $this->system->language->translate(__CLASS__.':description_payment_fee', 'Adds a payment fee to the order.'),
          'function' => 'int()',
        ),
        array(
          'key' => 'tax_class_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_tax_class', 'Tax Class'),
          'description' => $this->system->language->translate(__CLASS__.':description_tax_class', 'The tax class for the shipping cost.'),
          'function' => 'tax_classes()',
        ),
        array(
          'key' => 'order_status_id',
          'default_value' => '0',
          'title' => $this->system->language->translate('title_order_status', 'Order Status'),
          'description' => $this->system->language->translate('modules:description_order_status', 'Give orders made with this payment method the following order status.'),
          'function' => 'order_status()',
        ),
        array(
          'key' => 'geo_zone_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_geo_zone', 'Geo Zone'),
          'description' => $this->system->language->translate(__CLASS__.':description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.'),
          'function' => 'geo_zones()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => $this->system->language->translate('title_priority', 'Priority'),
          'description' => $this->system->language->translate(__CLASS__.':description_priority', 'Displays this module by the given priority order value.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
    
?>