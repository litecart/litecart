<?php

  class ent_customer {
    public $data;
    public $previous;

    public function __construct($customer_id=null) {

      if ($customer_id !== null) {
        $this->load($customer_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."customers;"
      );

      while ($field = database::fetch($fields_query)) {
        if (preg_match('#^shipping_(.*)$#', $field['Field'], $matches)) {
          $this->data['shipping_address'][$matches[1]] = database::create_variable($field['Type']);
        } else {
          $this->data[$field['Field']] = database::create_variable($field['Type']);
        }
      }

      $this->data['status'] = 1;
      $this->data['newsletter'] = '';

      $this->previous = $this->data;
    }

    public function load($customer_id) {

      if (!preg_match('#(^[0-9]+$|@)#', $customer_id)) throw new Exception('Invalid customer (ID: '. $customer_id .')');

      $this->reset();

      $customer_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."customers
        ". (preg_match('#^[0-9]+$#', $customer_id) ? "where id = '". (int)$customer_id ."'" : "") ."
        ". (preg_match('#@#', $customer_id) ? "where lower(email) = '". database::input(strtolower($customer_id)) ."'" : "") ."
        limit 1;"
      );

      if ($customer = database::fetch($customer_query)) {
        $this->data = array_replace($this->data, array_intersect_key($customer, $this->data));
      } else {
        throw new Exception('Could not find customer (ID: '. (int)$customer_id .') in database.');
      }

      foreach ($customer as $field => $value) {
        if (preg_match('#^shipping_(.*)$#', $field, $matches)) {
          unset($this->data['shipping_'.$matches[1]]);
          $this->data['shipping_address'][$matches[1]] = $value;
        }
      }

      if (empty($this->data['different_shipping_address'])) {
        foreach (array_keys($this->data['shipping_address']) as $key) {
          $this->data['shipping_address'][$key] = '';
        }
        $this->data['shipping_address']['country_code'] = $this->data['country_code'];
        $this->data['shipping_address']['zone_code'] = $this->data['zone_code'];
      }

      $newsletter_recipient_query = database::query(
        "select id from ". DB_TABLE_PREFIX ."newsletter_recipients
        where email = '". database::input($this->data['email']) ."'
        limit 1;"
      );

      if (database::num_rows($newsletter_recipient_query)) {
        $this->data['newsletter'] = 1;
      } else {
        $this->data['newsletter'] = 0;
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."customers
          (email, date_created)
          values ('". database::input($this->data['email']) ."', '". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );

        $this->data['id'] = database::insert_id();

        database::query(
          "update ". DB_TABLE_PREFIX ."orders
          set customer_id = ". (int)$this->data['id'] ."
          where lower(customer_email) = '". database::input(strtolower($this->data['email'])) ."'
          and customer_id = 0;"
        );
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."customers
        set
          code = '". database::input($this->data['code']) ."',
          status = '". (!empty($this->data['status']) ? '1' : '0') ."',
          email = '". database::input(strtolower($this->data['email'])) ."',
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
          shipping_phone = '". database::input($this->data['shipping_address']['phone']) ."',
          notes = '". database::input($this->data['notes']) ."',
          password_reset_token = '". database::input($this->data['password_reset_token']) ."',
          date_blocked_until = ". (!empty($this->data['date_blocked_until']) ? "'". database::input($this->data['date_blocked_until']) ."'" : "NULL") .",
          date_expire_sessions = ". (!empty($this->data['date_expire_sessions']) ? "'". database::input($this->data['date_expire_sessions']) ."'" : "NULL") .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      if (!empty($this->previous['email']) && $this->previous['email'] != $this->data['email']) {
        database::query(
          "update ". DB_TABLE_PREFIX ."newsletter_recipients
          set email = '". database::input(strtolower($this->data['email'])) ."',
            firstname = '". database::input($this->data['firstname']) ."',
            lastname = '". database::input($this->data['lastname']) ."'
          where lower(email) = '". database::input(strtolower($this->previous['email'])) ."';"
        );
      }

      if (!empty($this->data['newsletter'])) {
        database::query(
          "insert ignore into ". DB_TABLE_PREFIX ."newsletter_recipients
          (email, firstname, lastname, client_ip, hostname, user_agent, date_created)
          values ('". database::input(strtolower($this->data['email'])) ."', '". database::input($this->data['firstname']) ."', '". database::input($this->data['lastname']) ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."', '". database::input($_SERVER['HTTP_USER_AGENT']) ."', '". date('Y-m-d H:i:s') ."');"
        );
      } else if (!empty($this->previous['id'])) {
        database::query(
          "delete from ". DB_TABLE_PREFIX ."newsletter_recipients
          where lower(email) = '". database::input(strtolower($this->data['email'])) ."';"
        );
      }

      $customer_modules = new mod_customer();

      if (!empty($this->previous['id'])) {
        $customer_modules->update($this->data, $this->previous);
      } else {
        $customer_modules->update($this->data);
      }

      $this->previous = $this->data;

      cache::clear_cache('customers');
    }

    public function set_password($password) {

      if (empty($this->data['id'])) {
        $this->save();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."customers
        set password_hash = '". database::input($this->data['password_hash'] = password_hash($password, PASSWORD_DEFAULT)) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->previous['password_hash'] = $this->data['password_hash'];
    }

    public function delete() {

      database::query(
        "update ". DB_TABLE_PREFIX ."orders
        set customer_id = 0
        where customer_id = ". (int)$this->data['id'] .";"
      );

      database::query(
        "delete c, nr
        from ". DB_TABLE_PREFIX ."customers c
        left join ". DB_TABLE_PREFIX ."newsletter_recipients nr on (nr.email = c.email)
        where c.id = ". (int)$this->data['id'] .";"
      );

      $customer_modules = new mod_customer();
      $customer_modules->delete($this->previous);

      $this->reset();

      cache::clear_cache('customers');
    }
  }
