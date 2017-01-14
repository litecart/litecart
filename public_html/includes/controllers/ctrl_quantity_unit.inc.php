<?php

  class ctrl_quantity_unit {
    public $data = array();

    public function __construct($quantity_unit_id=null) {
      if ($quantity_unit_id !== null) {
        $this->load((int)$quantity_unit_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_QUANTITY_UNITS .";"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_QUANTITY_UNITS_INFO .";"
      );

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], array('id', 'quantity_unit_id', 'language_code'))) continue;
        $this->data[$field['Field']] = array();
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = '';
        }
      }
    }

    public function load($quantity_unit_id) {
      $quantity_unit_query = database::query(
        "select * from ". DB_TABLE_QUANTITY_UNITS ."
        where id = '". (int)$quantity_unit_id ."'
        limit 1;"
      );
      $this->data = database::fetch($quantity_unit_query);
      if (empty($this->data)) trigger_error('Could not find quantity unit (ID: '. (int)$quantity_unit_id .') in database.', E_USER_ERROR);

      $quantity_unit_info_query = database::query(
        "select name, description, language_code from ". DB_TABLE_QUANTITY_UNITS_INFO ."
        where quantity_unit_id = '". (int)$this->data['id'] ."';"
      );
      while ($quantity_unit_info = database::fetch($quantity_unit_info_query)) {
        foreach ($quantity_unit_info as $key => $value) {
          $this->data[$key][$quantity_unit_info['language_code']] = $value;
        }
      }
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_QUANTITY_UNITS ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_QUANTITY_UNITS ."
        set decimals = '". (int)$this->data['decimals'] ."',
            separate = '". (int)$this->data['separate'] ."',
            priority = '". (int)$this->data['priority'] ."',
            date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $quantity_unit_info_query = database::query(
          "select * from ". DB_TABLE_QUANTITY_UNITS_INFO ."
          where quantity_unit_id = '". (int)$this->data['id'] ."'
          and language_code = '". $language_code ."'
          limit 1;"
        );
        $quantity_unit_info = database::fetch($quantity_unit_info_query);

        if (empty($quantity_unit_info['id'])) {
          database::query(
            "insert into ". DB_TABLE_QUANTITY_UNITS_INFO ."
            (quantity_unit_id, language_code)
            values ('". (int)$this->data['id'] ."', '". $language_code ."');"
          );
          $quantity_unit_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_QUANTITY_UNITS_INFO ."
          set
            name = '". database::input($this->data['name'][$language_code]) ."',
            description = '". database::input($this->data['description'][$language_code]) ."'
          where id = '". (int)$quantity_unit_info['id'] ."'
          and quantity_unit_id = '". (int)$this->data['id'] ."'
          and language_code = '". $language_code ."'
          limit 1;"
        );
      }

      cache::clear_cache('quantity_units');
    }

    public function delete() {

      if (database::num_rows(database::query("select id from ". DB_TABLE_PRODUCTS ." where quantity_unit_id = '". (int)$this->data['id'] ."' limit 1;"))) {
        trigger_error('Cannot delete the quantity unit because there are products using it', E_USER_ERROR);
        return;
      }

      database::query(
        "delete from ". DB_TABLE_QUANTITY_UNITS_INFO ."
        where quantity_unit_id = '". (int)$this->data['id'] ."';"
      );

      database::query(
        "delete from ". DB_TABLE_QUANTITY_UNITS ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      cache::clear_cache('quantity_units');

      $this->data['id'] = null;
    }
  }

?>