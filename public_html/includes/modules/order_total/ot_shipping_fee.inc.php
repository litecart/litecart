<?php

  class ot_shipping_fee {
    public $id = __CLASS__;
    public $name = 'Shipping Fee';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'http://www.litecart.net';
    public $priority = 0;

    public function __construct() {
      $this->name = language::translate(__CLASS__.':title_shipping_fee', 'Shipping Fee');
    }

    public function process($order) {

      if (empty($this->settings['status'])) return;

      if (empty($GLOBALS['shipping']->data['selected']['cost'])) return;

      $output = array();

      $output[] = array(
        'title' => $GLOBALS['shipping']->data['selected']['title'] .' ('. $GLOBALS['shipping']->data['selected']['name'] .')',
        'value' => $GLOBALS['shipping']->data['selected']['cost'],
        'tax' => tax::get_tax($GLOBALS['shipping']->data['selected']['cost'], $GLOBALS['shipping']->data['selected']['tax_class_id'], $order->data['customer']),
        'calculate' => true,
      );

      if (!empty($this->settings['free_shipping_amount']) && $this->settings['free_shipping_amount'] > 0) {
        if (empty($this->settings['free_shipping_geo_zone_id']) || functions::reference_in_geo_zone($this->settings['free_shipping_geo_zone_id'], $order->data['customer']['shipping_address']['country_code'], $order->data['customer']['shipping_address']['zone_code'])) {

        // Calculate cart total
          $subtotal = 0;
          foreach ($order->data['items'] as $item) {
            $subtotal += $item['quantity'] * $item['price'];
          }

        // If below minimum amount
          if ($subtotal >= $this->settings['free_shipping_amount']) {
            $output[] = array(
              'title' => language::translate('title_free_shipping', 'Free Shipping'),
              'value' => -$GLOBALS['shipping']->data['selected']['cost'],
              'tax' => -tax::get_tax($GLOBALS['shipping']->data['selected']['cost'], $GLOBALS['shipping']->data['selected']['tax_class_id'], $order->data['customer']),
              'tax_class_id' => $GLOBALS['shipping']->data['selected']['tax_class_id'],
              'calculate' => true,
            );
          }
        }
      }

      return $output;
    }

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
          'key' => 'free_shipping_amount',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_free_shipping_amount', 'Free Shipping Amount'),
          'description' => language::translate(__CLASS__.':description_free_shipping_amount', 'Enable free shipping for orders that meet the given cart total amount or above (excluding tax). 0 = disabled'),
          'function' => 'decimal()',
        ),
        array(
          'key' => 'free_shipping_geo_zone_id',
          'default_value' => '',
          'title' => language::translate('title_free_shipping_geo_zone', 'Free Shipping Geo Zone'),
          'description' => language::translate('modules:description_free_shipping_geo_zone', 'Limit free shipping to the selected geo zone. Otherwise leave blank.'),
          'function' => 'geo_zones()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '20',
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