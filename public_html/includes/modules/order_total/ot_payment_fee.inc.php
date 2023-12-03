<?php

  #[AllowDynamicProperties]
  class ot_payment_fee extends abs_module {
    public $name = 'Payment Fee';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'https://www.litecart.net';
    public $priority = 0;

    public function __construct() {
      $this->name = language::translate(__CLASS__.':title', 'Payment Fee');
    }

    public function process($order) {

      if (empty($this->settings['status'])) return;

      $output = [];

      if (empty($order->data['payment_option']['fee']) || (float)$order->data['payment_option']['fee'] == 0) return;

      $output[] = [
        'title' => $order->data['payment_option']['name'],
        'amount' => $order->data['payment_option']['fee'],
        'tax' => tax::get_tax($order->data['payment_option']['fee'], $order->data['payment_option']['tax_class_id'], $order->data['customer']),
        'calculate' => true,
      ];

      return $output;
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
          'key' => 'priority',
          'default_value' => '30',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'number()',
        ],
      ];
    }
  }
