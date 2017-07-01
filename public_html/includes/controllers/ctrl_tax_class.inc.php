<?php

  class ctrl_tax_class {
    public $data;

    public function __construct($tax_class_id=null) {

      if ($tax_class_id !== null) {
        $this->load((int)$tax_class_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_TAX_CLASSES .";"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }
    }

    public function load($tax_class_id) {

      $this->reset();

      $tax_class_query = database::query(
        "select * from ". DB_TABLE_TAX_CLASSES ."
        where id = '". (int)$tax_class_id ."'
        limit 1;"
      );

      if ($tax_class = database::fetch($tax_class_query)) {
        $this->data = array_replace($this->data, array_intersect_key($tax_class, $this->data));
      } else {
        trigger_error('Could not find tax class (ID: '. (int)$tax_class_id .') in database.', E_USER_ERROR);
      }
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_TAX_CLASSES ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_TAX_CLASSES ."
        set
          code = '". database::input($this->data['code']) ."',
          name = '". database::input($this->data['name']) ."',
          description = '". database::input($this->data['description']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      cache::clear_cache('tax_classes');
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_TAX_CLASSES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      $this->data['id'] = null;

      cache::clear_cache('tax_classes');
    }
  }
