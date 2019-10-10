<?php

  class ent_delivery_status {
    public $data;
    public $previous;

    public function __construct($delivery_status_id=null) {

      if ($delivery_status_id !== null) {
        $this->load($delivery_status_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_DELIVERY_STATUSES .";"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_DELIVERY_STATUSES_INFO .";"
      );

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], array('id', 'delivery_status_id', 'language_code'))) continue;

        $this->data[$field['Field']] = array();
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = null;
        }
      }

      $this->previous = $this->data;
    }

    public function load($delivery_status_id) {

      if (!preg_match('#^[0-9]+$#', $delivery_status_id)) throw new Exception('Invalid delivery status (ID: '. $delivery_status_id .')');

      $this->reset();

      $delivery_status_query = database::query(
        "select * from ". DB_TABLE_DELIVERY_STATUSES ."
        where id = ". (int)$delivery_status_id ."
        limit 1;"
      );

      if ($delivery_status = database::fetch($delivery_status_query)) {
        $this->data = array_replace($this->data, array_intersect_key($delivery_status, $this->data));;
      } else {
        throw new Exception('Could not find delivery status (ID: '. (int)$delivery_status_id .') in database.');
      }

      $delivery_status_info_query = database::query(
        "select * from ". DB_TABLE_DELIVERY_STATUSES_INFO ."
        where delivery_status_id = ". (int)$this->data['id'] .";"
      );

      while ($delivery_status_info = database::fetch($delivery_status_info_query)) {
        foreach ($delivery_status_info as $key => $value) {
          if (in_array($key, array('id', 'delivery_status_id', 'language_code'))) continue;
          $this->data[$key][$delivery_status_info['language_code']] = $value;
        }
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_DELIVERY_STATUSES ."
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_DELIVERY_STATUSES ."
        set date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $delivery_status_info_query = database::query(
          "select * from ". DB_TABLE_DELIVERY_STATUSES_INFO ."
          where delivery_status_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );

        if (!$delivery_status_info = database::fetch($delivery_status_info_query)) {
          database::query(
            "insert into ". DB_TABLE_DELIVERY_STATUSES_INFO ."
            (delivery_status_id, language_code)
            values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
          );
          $delivery_status_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_DELIVERY_STATUSES_INFO ."
          set
            name = '". database::input($this->data['name'][$language_code]) ."',
            description = '". database::input($this->data['description'][$language_code]) ."'
          where id = ". (int)$delivery_status_info['id'] ."
          and delivery_status_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }

      $this->previous = $this->data;

      cache::clear_cache('delivery_statuses');
    }

    public function delete() {

      if (database::num_rows(database::query("select id from ". DB_TABLE_PRODUCTS ." where delivery_status_id = ". (int)$this->data['id'] ." limit 1;"))) {
        throw new Exception('Cannot delete the delivery status because there are products using it');
      }

      database::query(
        "delete from ". DB_TABLE_DELIVERY_STATUSES_INFO ."
        where delivery_status_id = ". (int)$this->data['id'] .";"
      );

      database::query(
        "delete from ". DB_TABLE_DELIVERY_STATUSES ."
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->reset();

      cache::clear_cache('delivery_statuses');
    }
  }
