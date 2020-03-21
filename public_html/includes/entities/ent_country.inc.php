<?php

  class ent_country {
    public $data;
    public $previous;

    public function __construct($country_code=null) {

      if ($country_code !== null) {
        $this->load($country_code);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_PREFIX ."countries;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $this->previous = $this->data;
    }

    public function load($country_code) {

      if (!preg_match('#^[A-Z]{2}$#', $country_code)) throw new Exception('Invalid country code ('. $country_code .')');

      $this->reset();

      $country_query = database::query(
        "select * from ". DB_PREFIX ."countries
        where iso_code_2 = '". database::input($country_code) ."'
        limit 1;"
      );

      if ($country = database::fetch($country_query)) {
        $this->data = array_replace($this->data, array_intersect_key($country, $this->data));
      } else {
        throw new Exception('Could not find country (Code: '. htmlspecialchars($country_code) .') in database.');
      }

      $zones_query = database::query(
        "select * from ". DB_PREFIX ."zones
        where country_code = '". database::input($this->data['iso_code_2']) ."'
        order by name;"
      );

      $this->data['zones'] = [];
      while ($zone = database::fetch($zones_query)) {
        $this->data['zones'][$zone['id']] = $zone;
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_PREFIX ."countries
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_PREFIX ."countries
        set
          status = ". (int)$this->data['status'] .",
          iso_code_1 = '". database::input($this->data['iso_code_1']) ."',
          iso_code_2 = '". database::input($this->data['iso_code_2']) ."',
          iso_code_3 = '". database::input($this->data['iso_code_3']) ."',
          name = '". database::input($this->data['name']) ."',
          domestic_name = '". database::input($this->data['domestic_name']) ."',
          tax_id_format = '". database::input($this->data['tax_id_format']) ."',
          address_format = '". database::input($this->data['address_format']) ."',
          postcode_format = '". database::input($this->data['postcode_format']) ."',
          language_code = '". database::input($this->data['language_code']) ."',
          currency_code = '". database::input($this->data['currency_code']) ."',
          phone_code = '". database::input($this->data['phone_code']) ."',
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      database::query(
        "delete from ". DB_PREFIX ."zones
        where country_code = '". database::input($this->data['iso_code_2']) ."'
        and id not in ('". @implode("', '", array_column($this->data['zones'], 'id')) ."');"
      );

      if (!empty($this->data['zones'])) {
        foreach ($this->data['zones'] as $zone) {
          if (empty($zone['id'])) {
            database::query(
              "insert into ". DB_PREFIX ."zones
              (country_code, date_created)
              values ('". database::input($this->data['iso_code_2']) ."', '". date('Y-m-d H:i:s') ."');"
            );
            $zone['id'] = database::insert_id();
          }

          database::query(
            "update ". DB_PREFIX ."zones
            set code = '". database::input($zone['code']) ."',
            name = '". database::input($zone['name']) ."',
            date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
            where country_code = '". database::input($this->data['iso_code_2']) ."'
            and id = ". (int)$zone['id'] ."
            limit 1;"
          );
        }
      }

      $this->previous = $this->data;

      cache::clear_cache('countries');
    }

    public function delete() {

      if ($this->data['code'] == settings::get('store_country_code')) {
        throw new Exception('Cannot delete the store country');
      }

      if ($this->data['code'] == settings::get('default_country_code')) {
        throw new Exception('Cannot delete the default country');
      }

      database::query(
        "delete from ". DB_PREFIX ."zones
        where code = '". database::input($this->data['iso_code_2']) ."';"
      );

      database::query(
        "delete from ". DB_PREFIX ."countries
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->reset();

      cache::clear_cache('countries');
    }
  }
