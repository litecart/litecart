<?php

  class ot_subtotal {
    public $id = __CLASS__;
    public $name = 'Subtotal';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'http://www.litecart.net';
    public $priority = 0;

    public function __construct() {
      $this->name = language::translate(__CLASS__.':title_subtotal', 'Subtotal');
    }

    public function process($order) {

      if (empty($this->settings['status'])) return;

      $output = array();
      $value = 0;
      $tax = 0;

      if (!empty($order->data['items'])) {
        foreach ($order->data['items'] as $item) {
          $value += $item['price'] * $item['quantity'];
          $tax += $item['tax'] * $item['quantity'];
        }
      }

      $output[] = array(
        'title' => language::translate('title_subtotal', 'Subtotal'),
        'value' => $value,
        'tax' => $tax,
        'calculate' => false,
      );

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
          'key' => 'priority',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'int()',
        ),
      );
    }

    public function install() {}

    public function uninstall() {}
  }
