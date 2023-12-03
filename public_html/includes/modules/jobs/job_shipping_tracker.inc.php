<?php

  #[AllowDynamicProperties]
  class job_shipping_tracker extends abs_module {

    public $id = __CLASS__;
    public $name = 'Shipping Tracker';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $support_link = '';
    public $website = 'https://www.litecart.net/';
    public $priority = 0;

    public function process($force, $last_run) {

      if (empty($this->settings['status'])) return;

      if (!$force) {
        switch ($this->settings['frequency']) {
          case '15 min':
            if (strtotime($last_run) > strtotime('-15 minutes')) return;
            break;
          case 'Hourly':
            if (strtotime($last_run) > strtotime('-1 h')) return;
            break;
          case '3 Hours':
            if (strtotime($last_run) > strtotime('-3 h')) return;
            break;
          case '6 Hours':
            if (strtotime($last_run) > strtotime('-6 h')) return;
            break;
          case '12 Hours':
            if (strtotime($last_run) > strtotime('-12 h')) return;
            break;
          case 'Daily':
            if (strtotime($last_run) > strtotime('-24 h')) return;
            break;
        }
      }

      $orders_query = database::query(
        "select id, shipping_option_id, shipping_tracking_id
        from ". DB_TABLE_PREFIX ."orders
        where shipping_tracking_id != ''
        and order_status_id in (
          select id from ". DB_TABLE_PREFIX ."order_statuses
          where is_trackable
        )
        and date_created > '". date('Y-m-d H:i:s', strtotime('-30 days')) ."'
        order by date_created asc
        limit 10;"
      );

      echo 'Found '. database::num_rows($orders_query) .' orders to track' . PHP_EOL;

      while ($order = database::fetch($orders_query)) {
        $order = new ent_order($order['id']);

        try {

          echo 'Tracking order '. $order->data['id'] .' with tracking no '. $order->data['shipping_tracking_id'] . '...';

          list($module_id, $option_id) = explode(':', $order->data['shipping_option']['id']);

          if (empty($module_id)) {
            throw new Exception('No module ID');
          }

          if (!$result = $order->shipping->run('track', $module_id, $order)) {
            throw new Exception('Nothing returned, skipping.');
          }

          if (!empty($result['error'])) {
            throw new Exception($result['error']);
          }

          echo ' [OK]'. PHP_EOL;

        } catch (Exception $e) {
          echo ' [Error]' . PHP_EOL . $e->getMessage() . PHP_EOL;
        }
      }
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
          'key' => 'frequency',
          'default_value' => 'Hourly',
          'title' => language::translate(__CLASS__.':title_frequency', 'Frequency'),
          'description' => language::translate(__CLASS__.':description_check_frequency', 'How often the modification scanner should run.'),
          'function' => 'radio("15 min","Hourly","3 Hours","6 Hours","12 Hours","Daily")',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
          'function' => 'number()',
        ),
      );
    }
  }
