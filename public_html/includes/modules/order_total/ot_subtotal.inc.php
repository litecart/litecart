<?php

  #[AllowDynamicProperties]
  class ot_subtotal {
    public $id = __CLASS__;
    public $name = 'Subtotal';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'https://www.litecart.net';
    public $priority = 0;

    public function __construct() {
      $this->name = language::translate(__CLASS__.':title', 'Subtotal');
    }

    public function process($order) {

      if (empty($this->settings['status'])) return;

      $output = [];
      $value = 0;
      $tax = 0;

      if (!empty($order->data['items'])) {
        foreach ($order->data['items'] as $item) {
          $value += $item['price'] * $item['quantity'];
          $tax += $item['tax'] * $item['quantity'];
        }
      }

      $output[] = [
        'title' => $this->name,
        'value' => $value,
        'tax' => $tax,
        'calculate' => false,
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
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'number()',
        ],
      ];
    }

    public function install() {}

    public function uninstall() {}
  }
