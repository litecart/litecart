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

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."delivery_statuses;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field['Type']);
      }

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."delivery_statuses_info;"
      );

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], ['id', 'delivery_status_id', 'language_code'])) continue;

        $this->data[$field['Field']] = [];
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = database::create_variable($field['Type']);
        }
      }

      $this->previous = $this->data;
    }

    public function load($delivery_status_id) {

      if (!preg_match('#^[0-9]+$#', $delivery_status_id)) throw new Exception('Invalid delivery status (ID: '. $delivery_status_id .')');

      $this->reset();

      $delivery_status_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."delivery_statuses
        where id = ". (int)$delivery_status_id ."
        limit 1;"
      );

      if ($delivery_status = database::fetch($delivery_status_query)) {
        $this->data = array_replace($this->data, array_intersect_key($delivery_status, $this->data));
      } else {
        throw new Exception('Could not find delivery status (ID: '. (int)$delivery_status_id .') in database.');
      }

      $delivery_status_info_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."delivery_statuses_info
        where delivery_status_id = ". (int)$this->data['id'] .";"
      );

      while ($delivery_status_info = database::fetch($delivery_status_info_query)) {
        foreach ($delivery_status_info as $key => $value) {
          if (in_array($key, ['id', 'delivery_status_id', 'language_code'])) continue;
          $this->data[$key][$delivery_status_info['language_code']] = $value;
        }
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."delivery_statuses
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."delivery_statuses
        set date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $delivery_status_info_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."delivery_statuses_info
          where delivery_status_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );

        if (!$delivery_status_info = database::fetch($delivery_status_info_query)) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."delivery_statuses_info
            (delivery_status_id, language_code)
            values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
          );
          $delivery_status_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."delivery_statuses_info
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

      if (database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products where delivery_status_id = ". (int)$this->data['id'] ." limit 1;"))) {
        throw new Exception('Cannot delete the delivery status because there are products using it');
      }

      database::query(
        "delete from ". DB_TABLE_PREFIX ."delivery_statuses_info
        where delivery_status_id = ". (int)$this->data['id'] .";"
      );

      database::query(
        "delete from ". DB_TABLE_PREFIX ."delivery_statuses
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->reset();

      cache::clear_cache('delivery_statuses');
    }
  }
