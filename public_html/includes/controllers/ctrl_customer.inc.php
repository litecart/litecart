<?php

  class ctrl_customer {
    public $data = array();

    public function __construct($customer_id=null) {
      if ($customer_id !== null) {
        $this->load((int)$customer_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_CUSTOMERS .";"
      );
      while ($field = database::fetch($fields_query)) {
        if (preg_match('#^shipping_(.*)$#', $field['Field'], $matches)) {
          $this->data['shipping_address'][$matches[1]] = '';
        } else {
          $this->data[$field['Field']] = null;
        }
      }

      $this->data['status'] = 1;
    }

    public function load($customer_id) {

      $customer_query = database::query(
        "select * from ". DB_TABLE_CUSTOMERS ."
        where id = '". database::input($customer_id) ."'
        limit 1;"
      );
      $customer = database::fetch($customer_query);
      if (empty($customer)) trigger_error('Could not find customer (ID: '. (int)$customer_id .') in database.', E_USER_ERROR);

      $map = array(
        'id',
        'code',
        'status',
        'email',
        'password',
        'tax_id',
        'company',
        'firstname',
        'lastname',
        'address1',
        'address2',
        'postcode',
        'country_code',
        'zone_code',
        'city',
        'phone',
        'mobile',
        'different_shipping_address',
        'newsletter',
        'notes',
      );
      foreach ($map as $key) {
        $this->data[$key] = $customer[$key];
      }

      $key_map = array(
        'shipping_company' => 'company',
        'shipping_firstname' => 'firstname',
        'shipping_lastname' => 'lastname',
        'shipping_address1' => 'address1',
        'shipping_address2' => 'address2',
        'shipping_postcode' => 'postcode',
        'shipping_city' => 'city',
        'shipping_country_code' => 'country_code',
        'shipping_zone_code' => 'zone_code',
      );
      foreach ($key_map as $skey => $tkey) {
        $this->data['shipping_address'][$tkey] = $customer[$skey];
      }
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_CUSTOMERS ."
          (email, date_created)
          values ('". database::input($this->data['email']) ."', '". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();

        if (!empty($this->data['email'])) {
          database::query(
            "update ". DB_TABLE_ORDERS ."
            set customer_id = '". (int)$this->data['id'] ."'
            where customer_email = '". database::input($this->data['email']) ."';"
          );
        }
      }

      database::query(
        "update ". DB_TABLE_CUSTOMERS ."
        set
          code = '". database::input($this->data['code']) ."',
          status = '". (!empty($this->data['status']) ? '1' : '0') ."',
          email = '". database::input($this->data['email']) ."',
          tax_id = '". database::input($this->data['tax_id']) ."',
          company = '". database::input($this->data['company']) ."',
          firstname = '". database::input($this->data['firstname']) ."',
          lastname = '". database::input($this->data['lastname']) ."',
          address1 = '". database::input($this->data['address1']) ."',
          address2 = '". database::input($this->data['address2']) ."',
          postcode = '". database::input($this->data['postcode']) ."',
          city = '". database::input($this->data['city']) ."',
          country_code = '". database::input($this->data['country_code']) ."',
          zone_code = '". database::input($this->data['zone_code']) ."',
          phone = '". database::input($this->data['phone']) ."',
          mobile = '". database::input($this->data['mobile']) ."',
          different_shipping_address = '". (!empty($this->data['different_shipping_address']) ? '1' : '0') ."',
          shipping_company = '". database::input($this->data['shipping_address']['company']) ."',
          shipping_firstname = '". database::input($this->data['shipping_address']['firstname']) ."',
          shipping_lastname = '". database::input($this->data['shipping_address']['lastname']) ."',
          shipping_address1 = '". database::input($this->data['shipping_address']['address1']) ."',
          shipping_address2 = '". database::input($this->data['shipping_address']['address2']) ."',
          shipping_postcode = '". database::input($this->data['shipping_address']['postcode']) ."',
          shipping_city = '". database::input($this->data['shipping_address']['city']) ."',
          shipping_country_code = '". database::input($this->data['shipping_address']['country_code']) ."',
          shipping_zone_code = '". database::input($this->data['shipping_address']['zone_code']) ."',
          newsletter = '". (!empty($this->data['newsletter']) ? '1' : '0') ."',
          notes = '". database::input($this->data['notes']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      $customer_modules = new mod_customer();
      $customer_modules->update($this->data);

      cache::clear_cache('customers');
    }

    public function set_password($password) {

      if (empty($this->data['email'])) trigger_error('Cannot set password without an email address', E_USER_ERROR);

      if (empty($this->data['id'])) {
        $this->save();
      }

      $password_hash = functions::password_checksum($this->data['email'], $password, PASSWORD_SALT);

      database::query(
        "update ". DB_TABLE_CUSTOMERS ."
        set
          password = '". $password_hash ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      $this->data['password'] = $password_hash;
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_CUSTOMERS ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      cache::clear_cache('customers');

      $this->data['id'] = null;
    }
  }

?>