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

      if (!empty($this->settings['free_shipping_table'])) {

      // Calculate cart total
        $subtotal = 0;
        foreach ($order->data['items'] as $item) {
          $subtotal += $item['quantity'] * $item['price'];
        }

        $free_shipping_table = functions::csv_decode($this->settings['free_shipping_table'], ',');

        if (!empty($free_shipping_table)) {
          foreach ($free_shipping_table as $row) {
            if (empty($row['country_code']) || $row['country_code'] != $order->data['customer']['shipping_address']['country_code']) continue;
            if (!isset($row['min_subtotal']) || $row['min_subtotal'] < 0) continue;
            if ($subtotal < $row['min_subtotal']) continue;

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
          'key' => 'free_shipping_table',
          'default_value' => 'country_code,min_subtotal',
          'title' => language::translate(__CLASS__.':title_free_shipping_table', 'Free Shipping Table'),
          'description' => language::translate(__CLASS__.':description_free_shipping_table', 'Free shipping table in standard CSV format with column headers.'),
          'function' => 'bigtext()',
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
