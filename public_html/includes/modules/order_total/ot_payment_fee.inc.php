<?php

  class ot_payment_fee {
    public $id = __CLASS__;
    public $name = 'Payment Fee';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'http://www.litecart.net';
    public $priority = 0;

    public function __construct() {

      $this->name = language::translate(__CLASS__.':title_payment_fee', 'Payment Fee');
    }

    public function process($order) {

      if (empty($this->settings['status'])) return;

      if (empty($GLOBALS['payment']->data['selected']['cost'])) return;

      $output = array();

      $output[] = array(
        'title' => $GLOBALS['payment']->data['selected']['title'] .' ('. $GLOBALS['payment']->data['selected']['name'] .')',
        'value' => $GLOBALS['payment']->data['selected']['cost'],
        'tax' => tax::get_tax($GLOBALS['payment']->data['selected']['cost'], $GLOBALS['payment']->data['selected']['tax_class_id'], $order->data['customer']),
        'calculate' => true,
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
          'default_value' => '30',
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