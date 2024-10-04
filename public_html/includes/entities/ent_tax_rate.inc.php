<?php

  class ent_tax_rate {
    public $data;
    public $previous;

    public function __construct($tax_rate_id=null) {

      if ($tax_rate_id !== null) {
        $this->load($tax_rate_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."tax_rates;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field);
      }

      $this->previous = $this->data;
    }

    public function load($tax_rate_id) {

      if (empty($tax_rate_id)) throw new Exception('Invalid tax rate (ID: n/a)');

      $this->reset();

      $tax_rate_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."tax_rates
        where ". (preg_match('#^[0-9]+$#', $tax_rate_id) ? "id = ". (int)$tax_rate_id : "code = '". database::input($tax_rate_id) ."'") ."
        limit 1;"
      );

      if ($tax_rate = database::fetch($tax_rate_query)) {
        $this->data = array_replace($this->data, array_intersect_key($tax_rate, $this->data));
      } else {
        throw new Exception('Could not find tax rate (ID: '. (int)$tax_rate_id .') in database.');
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."tax_rates
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."tax_rates
        set
          tax_class_id = ". (int)$this->data['tax_class_id'] .",
          geo_zone_id = ". (int)$this->data['geo_zone_id'] .",
          code = '". database::input($this->data['code']) ."',
          name = '". database::input($this->data['name']) ."',
          description = '". database::input($this->data['description']) ."',
          type = '". database::input($this->data['type']) ."',
          rate = ". (float)$this->data['rate'] .",
          address_type = '". database::input($this->data['address_type']) ."',
          rule_companies_with_tax_id = ". (int)$this->data['rule_companies_with_tax_id'] .",
          rule_companies_without_tax_id = ". (int)$this->data['rule_companies_without_tax_id'] .",
          rule_individuals_with_tax_id = ". (int)$this->data['rule_individuals_with_tax_id'] .",
          rule_individuals_without_tax_id = ". (int)$this->data['rule_individuals_without_tax_id'] .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->previous = $this->data;

      cache::clear_cache('tax_rates');
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_PREFIX ."tax_rates
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->reset();

      cache::clear_cache('tax_rates');
    }
  }
