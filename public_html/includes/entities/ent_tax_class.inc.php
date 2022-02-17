<?php

  class ent_tax_class {
    public $data;
    public $previous;

    public function __construct($tax_class_id=null) {

      if (!empty($tax_class_id)) {
        $this->load($tax_class_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."tax_classes;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $this->previous = $this->data;
    }

    public function load($tax_class_id) {

      if (!preg_match('#^[0-9]+$#', $tax_class_id)) throw new Exception('Invalid tax class (ID: '. $tax_class_id .')');

      $this->reset();

      $tax_class_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."tax_classes
        where id = ". (int)$tax_class_id ."
        limit 1;"
      );

      if ($tax_class = database::fetch($tax_class_query)) {
        $this->data = array_replace($this->data, array_intersect_key($tax_class, $this->data));
      } else {
        throw new Exception('Could not find tax class (ID: '. (int)$tax_class_id .') in database.');
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."tax_classes
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );

        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."tax_classes
        set code = '". database::input($this->data['code']) ."',
          name = '". database::input($this->data['name']) ."',
          description = '". database::input($this->data['description']) ."',
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->previous = $this->data;

      cache::clear_cache('tax_classes');
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_PREFIX ."tax_classes
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->reset();

      cache::clear_cache('tax_classes');
    }
  }
