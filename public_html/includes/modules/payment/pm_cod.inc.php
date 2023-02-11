<?php

  #[AllowDynamicProperties]
  class pm_cod {
    public $name = 'Cash on Delivery';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'https://www.litecart.net';
    public $priority = 0;

    public function __construct() {
      $this->name = language::translate(__CLASS__.':title_cash_on_delivery', 'Cash on Delivery');
    }

    public function options($items, $subtotal, $tax, $currency_code, $customer) {

      if (empty($this->settings['status'])) return;

      $country_code = fallback($customer['shipping_address']['country_code'], $customer['country_code']);
      $zone_code = fallback($customer['shipping_address']['zone_code'], $customer['zone_code']);

      if (!empty($this->settings['geo_zones'])) {
        if (!reference::country($country_code)->in_geo_zone($this->settings['geo_zones'], $customer)) return;
      }

      return [
        [
          'id' => 'cod',
          'icon' => $this->settings['icon'],
          'name' => $this->name,
          'description' => '',
          'fields' => '',
          'fee' => $this->settings['fee'],
          'tax_class_id' => $this->settings['tax_class_id'],
          'confirm' => language::translate(__CLASS__.':title_confirm_order', 'Confirm Order'),
        ],
      ];
    }

    public function transfer($order) {
      return [
        'action' => '',
        'method' => '',
        'fields' => '',
      ];
    }

    public function verify($order) {
      return [
        'order_status_id' => $this->settings['order_status_id'],
        'payment_transaction_id' => '',
        'errors' => '',
      ];
    }

    function settings() {
      return [
        [
          'key' => 'status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_status', 'Status'),
          'description' => language::translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'toggle("e/d")',
        ],
        [
          'key' => 'icon',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_icon', 'Icon'),
          'description' => language::translate(__CLASS__.':description_icon', 'Path to an image to be displayed.'),
          'function' => 'text()',
        ],
        [
          'key' => 'fee',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_payment_fee', 'Payment Fee'),
          'description' => language::translate(__CLASS__.':description_payment_fee', 'Adds a payment fee to the order.'),
          'function' => 'decimal()',
        ],
        [
          'key' => 'tax_class_id',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_tax_class', 'Tax Class'),
          'description' => language::translate(__CLASS__.':description_tax_class', 'The tax class for the fee.'),
          'function' => 'tax_class()',
        ],
        [
          'key' => 'order_status_id',
          'default_value' => '0',
          'title' => language::translate('title_order_status', 'Order Status'),
          'description' => language::translate('modules:description_order_status', 'Give orders made with this payment method the following order status.'),
          'function' => 'order_status()',
        ],
        [
          'key' => 'geo_zones[]',
          'default_value' => '',
          'title' => language::translate('title_geo_zone_limitation', 'Geo Zone Limitation'),
          'description' => language::translate('modules:description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.'),
          'function' => 'geo_zone()',
        ],
        [
          'key' => 'priority',
          'default_value' => '0',
          'title' => language::translate('title_priority', 'Priority'),
          'description' => language::translate('modules:description_priority', 'Process this module in the given priority order.'),
          'function' => 'number()',
        ],
      ];
    }
  }
