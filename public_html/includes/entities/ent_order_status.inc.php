<?php

  class ent_order_status {
    public $data;
    public $previous;

    public function __construct($order_status_id=null) {

      if (!empty($order_status_id)) {
        $this->load($order_status_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."order_statuses;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field);
      }

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."order_statuses_info;"
      );

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], ['id', 'order_status_id', 'language_code'])) continue;

        $this->data[$field['Field']] = [];
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = database::create_variable($field);
        }
      }

      $this->data['num_orders'] = 0;

      $this->previous = $this->data;
    }

    public function load($order_status_id) {

      if (!preg_match('#^[0-9]+$#', $order_status_id)) throw new Exception('Invalid order status (ID: '. $order_status_id .')');

      $this->reset();

      $order_status = database::query(
        "select * from ". DB_TABLE_PREFIX ."order_statuses
        where id = ". (int)$order_status_id ."
        limit 1;"
      )->fetch();

      if ($order_status) {
        $this->data = array_replace($this->data, array_intersect_key($order_status, $this->data));
      } else {
        throw new Exception('Could not find order_status (ID: '. (int)$order_status_id .') in database.');
      }

      $order_status_info_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."order_statuses_info
        where order_status_id = ". (int)$this->data['id'] .";"
      );

      while ($order_status_info = database::fetch($order_status_info_query)) {
        foreach ($order_status_info as $key => $value) {
          if (in_array($key, ['id', 'order_status_id', 'language_code'])) continue;
          $this->data[$key][$order_status_info['language_code']] = $value;
        }
      }

      $orders_query = database::query(
        "select count(*) as num_orders
        from ". DB_TABLE_PREFIX ."orders
        where order_status_id = ". (int)$this->data['id'] .";"
      );

      $this->data['num_orders'] = database::fetch($orders_query, 'num_orders');

      $this->previous = $this->data;
    }

    public function save() {

      if ($this->data['num_orders'] && $this->data['stock_action'] != $this->previous['stock_action']) {
        throw new Exception(language::translate('error_cannot_change_stock_action_while_used_by_orders', 'You cannot change stock action while there are orders using this status'));
      }

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."order_statuses
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );

        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."order_statuses
        set hidden = '". (empty($this->data['hidden']) ? '0' : '1') ."',
          state = '". database::input($this->data['state']) ."',
          icon = '". database::input($this->data['icon']) ."',
          color = '". database::input($this->data['color']) ."',
          is_sale = '". (empty($this->data['is_sale']) ? '0' : '1') ."',
          is_archived = '". (empty($this->data['is_archived']) ? '0' : '1') ."',
          is_trackable = '". (empty($this->data['is_trackable']) ? '0' : '1') ."',
          stock_action = '". database::input($this->data['stock_action']) ."',
          notify = '". (empty($this->data['notify']) ? '0' : '1') ."',
          priority = ". (int)$this->data['priority'] .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $order_status_info_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."order_statuses_info
          where order_status_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );

        if (!$order_status_info = database::fetch($order_status_info_query)) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."order_statuses_info
            (order_status_id, language_code)
            values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
          );
          $order_status_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."order_statuses_info
          set name = '". database::input($this->data['name'][$language_code]) ."',
            description = '". database::input($this->data['description'][$language_code]) ."',
            email_subject = '". database::input($this->data['email_subject'][$language_code], true) ."',
            email_message = '". database::input($this->data['email_message'][$language_code], true) ."'
          where id = ". (int)$order_status_info['id'] ."
          and order_status_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }

      $this->previous = $this->data;

      cache::clear_cache('order_statuses');
    }

    public function delete() {

      if ($this->data['num_orders']) {
        throw new Exception(language::translate('error_cannot_delete_order_status_while_used', 'Cannot delete the order status while it is in use by orders'));
      }

      database::query(
        "delete os, osi
        from ". DB_TABLE_PREFIX ."order_statuses os
        left join ". DB_TABLE_PREFIX ."order_statuses_info osi on (osi.order_status_id = os.id)
        where os.id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('order_statuses');
    }
  }
