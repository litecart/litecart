<?php

  class pm_cod {
    private $system;
    public $id = __CLASS__;
    public $name = 'Cash on Delivery';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'http://www.litecart.net';
    public $website = 'http://www.litecart.net';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = &$system;
    }
    
    public function options() {
    
    // If not enabled
      if ($this->settings['status'] != 'Enabled') return;
      
    // If not in geo zone
      if (!empty($this->settings['geo_zone_id'])) {
        if (!$this->system->functions->reference_in_geo_zone($this->settings['geo_zone_id'], $this->system->customer->data['country_code'], $this->system->customer->data['zone_code'])) return;
      }
      
      $method = array(
        'title' => $this->system->language->translate(__CLASS__.':title_cash_on_delivery', 'Cash on Delivery'),
        'description' => '',
        'options' => array(
          array(
            'id' => 'cod',
            'icon' => '',
            'name' => $this->system->functions->reference_get_country_name($this->system->customer->data['shipping_address']['country_code']),
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
          'key' => 'fee',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_payment_fee', 'Payment Fee'),
          'description' => $this->system->language->translate(__CLASS__.':description_payment_fee', 'Adds a payment fee to the order.'),
          'function' => 'decimal()',
        ),
        array(
          'key' => 'tax_class_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_tax_class', 'Tax Class'),
          'description' => $this->system->language->translate(__CLASS__.':description_tax_class', 'The tax class for the fee.'),
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
          'title' => $this->system->language->translate('title_geo_zone_limitation', 'Geo Zone Limitation'),
          'description' => $this->system->language->translate('modules:description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.'),
          'function' => 'geo_zones()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => $this->system->language->translate('title_priority', 'Priority'),
          'description' => $this->system->language->translate('modules:description_priority', 'Process this module in the given priority order.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
    
?>