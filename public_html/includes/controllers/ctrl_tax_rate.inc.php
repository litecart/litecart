<?php

  class ctrl_tax_rate {
    public $data = array();

    public function __construct($tax_rate_id=null) {
      if ($tax_rate_id !== null) {
        $this->load((int)$tax_rate_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_TAX_RATES .";"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = '';
      }
    }

    public function load($tax_rate_id) {
      $tax_rate_query = database::query(
        "select * from ". DB_TABLE_TAX_RATES ."
        where id = '". (int)$tax_rate_id ."'
        limit 1;"
      );
      $this->data = database::fetch($tax_rate_query);
      if (empty($this->data)) trigger_error('Could not find tax rate (ID: '. (int)$tax_rate_id .') in database.', E_USER_ERROR);
    }

    public function save() {
      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_TAX_RATES ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_TAX_RATES ."
        set
          tax_class_id = '". database::input($this->data['tax_class_id']) ."',
          geo_zone_id = '". database::input($this->data['geo_zone_id']) ."',
          code = '". database::input($this->data['code']) ."',
          name = '". database::input($this->data['name']) ."',
          description = '". database::input($this->data['description']) ."',
          type = '". database::input($this->data['type']) ."',
          rate = '". database::input($this->data['rate']) ."',
          customer_type = '". database::input($this->data['customer_type']) ."',
          tax_id_rule = '". database::input($this->data['tax_id_rule']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      cache::clear_cache('tax');
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_TAX_RATES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      $this->data['id'] = null;

      cache::clear_cache('tax');
    }
  }

?>