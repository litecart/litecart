<?php

  class ent_quantity_unit {
    public $data;
    public $previous;

    public function __construct($quantity_unit_id=null) {

      if (!empty($quantity_unit_id)) {
        $this->load($quantity_unit_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."quantity_units;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."quantity_units_info;"
      );

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], ['id', 'quantity_unit_id', 'language_code'])) continue;

        $this->data[$field['Field']] = [];
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = null;
        }
      }

      $this->previous = $this->data;
    }

    public function load($quantity_unit_id) {

      if (!preg_match('#^[0-9]+$#', $quantity_unit_id)) throw new Exception('Invalid quantity unit (ID: '. $quantity_unit_id .')');

      $this->reset();

      $quantity_unit_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."quantity_units
        where id = ". (int)$quantity_unit_id ."
        limit 1;"
      );

      if ($quantity_unit = database::fetch($quantity_unit_query)) {
        $this->data = array_replace($this->data, array_intersect_key($quantity_unit, $this->data));
      } else {
        throw new Exception('Could not find quantity unit (ID: '. (int)$quantity_unit_id .') in database.');
      }

      $quantity_unit_info_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."quantity_units_info
        where quantity_unit_id = ". (int)$this->data['id'] .";"
      );

      while ($quantity_unit_info = database::fetch($quantity_unit_info_query)) {
        foreach ($quantity_unit_info as $key => $value) {
          if (in_array($key, ['id', 'quantity_unit_id', 'language_code'])) continue;
          $this->data[$key][$quantity_unit_info['language_code']] = $value;
        }
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."quantity_units
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."quantity_units
        set decimals = ". (int)$this->data['decimals'] .",
          separate = ". (int)$this->data['separate'] .",
          priority = ". (int)$this->data['priority'] .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $quantity_unit_info_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."quantity_units_info
          where quantity_unit_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );

        if (!$quantity_unit_info = database::fetch($quantity_unit_info_query)) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."quantity_units_info
            (quantity_unit_id, language_code)
            values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
          );
          $quantity_unit_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."quantity_units_info
          set name = '". database::input($this->data['name'][$language_code]) ."',
            description = '". database::input($this->data['description'][$language_code]) ."'
          where id = ". (int)$quantity_unit_info['id'] ."
          and quantity_unit_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }

      $this->previous = $this->data;

      cache::clear_cache('quantity_units');
    }

    public function delete() {

      if (database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products where quantity_unit_id = ". (int)$this->data['id'] ." limit 1;"))) {
        throw new Exception('Cannot delete the quantity unit because there are products using it');
      }


      database::query(
        "delete qu, qui
        from ". DB_TABLE_PREFIX ."quantity_units qu
        left join ". DB_TABLE_PREFIX ."quantity_units_info qui on (qui.quantity_unit_id = qu.id)
        where qu.id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('quantity_units');
    }
  }
