<?php

  class ctrl_country {
    public $data = array();

    public function __construct($country_code=null) {
      if ($country_code !== null) {
        $this->load($country_code);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_COUNTRIES .";"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = '';
      }
    }

    public function load($country_code) {
      $country_query = database::query(
        "select * from ". DB_TABLE_COUNTRIES ."
        where iso_code_2 = '". database::input($country_code) ."'
        limit 1;"
      );
      $this->data = database::fetch($country_query);
      if (empty($this->data)) trigger_error('Could not find country (Code: '. htmlspecialchars($country_code) .') in database.', E_USER_ERROR);

      $zones_query = database::query(
        "select * from ". DB_TABLE_ZONES ."
        where country_code = '". database::input($this->data['iso_code_2']) ."'
        order by name;"
      );

      $this->data['zones'] = array();
      while ($zone = database::fetch($zones_query)) {
        $this->data['zones'][$zone['id']] = $zone;
      }
    }

    public function save() {
      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_COUNTRIES ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_COUNTRIES ."
        set
          status = '". (int)$this->data['status'] ."',
          iso_code_2 = '". database::input($this->data['iso_code_2']) ."',
          iso_code_3 = '". database::input($this->data['iso_code_3']) ."',
          name = '". database::input($this->data['name']) ."',
          domestic_name = '". database::input($this->data['domestic_name']) ."',
          tax_id_format = '". database::input($this->data['tax_id_format']) ."',
          address_format = '". database::input($this->data['address_format']) ."',
          postcode_format = '". database::input($this->data['postcode_format']) ."',
          currency_code = '". database::input($this->data['currency_code']) ."',
          phone_code = '". database::input($this->data['phone_code']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      database::query(
        "delete from ". DB_TABLE_ZONES ."
        where country_code = '". database::input($this->data['iso_code_2']) ."'
        and id not in ('". @implode("', '", array_column($this->data['zones'], 'id')) ."');"
      );

      if (!empty($this->data['zones'])) {
        foreach ($this->data['zones'] as $zone) {
          if (empty($zone['id'])) {
            database::query(
              "insert into ". DB_TABLE_ZONES ."
              (country_code, date_created)
              values ('". database::input($this->data['iso_code_2']) ."', '". date('Y-m-d H:i:s') ."');"
            );
            $zone['id'] = database::insert_id();
          }
          database::query(
            "update ". DB_TABLE_ZONES ."
            set code = '". database::input($zone['code']) ."',
            name = '". database::input($zone['name']) ."',
            date_updated =  '". date('Y-m-d H:i:s') ."'
            where country_code = '". database::input($this->data['iso_code_2']) ."'
            and id = '". (int)$zone['id'] ."'
            limit 1;"
          );
        }
      }
    }

    public function delete() {

      if ($this->data['code'] == settings::get('system_country')) {
        trigger_error('Cannot delete the store system country', E_USER_ERROR);
        return;
      }

      if ($this->data['code'] == settings::get('default_country_code')) {
        trigger_error('Cannot delete the store default country', E_USER_ERROR);
        return;
      }

      database::query(
        "delete from ". DB_TABLE_ZONES ."
        where code = '". database::input($this->data['iso_code_2']) ."';"
      );

      database::query(
        "delete from ". DB_TABLE_COUNTRIES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      $this->data['id'] = null;
    }
  }

?>