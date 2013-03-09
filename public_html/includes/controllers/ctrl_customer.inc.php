<?php

  class ctrl_customer {
    public $data = array();
    
    public function __construct($customer_id=null) {
      global $system;
      
      $this->system = &$system;
      
      if ($customer_id !== null) $this->load($customer_id);
    }
    
    public function load($customer_id) {
    
      $customer_query = $this->system->database->query(
        "select * from ". DB_TABLE_CUSTOMERS ."
        where id = '". $this->system->database->input($customer_id) ."'
        limit 1;"
      );
      $customer = $this->system->database->fetch($customer_query);
      if (empty($customer)) trigger_error('Could not find customer ('. $customer_id .') in database.', E_USER_ERROR);
      
      $key_map = array(
        'id' => 'id',
        'email' => 'email',
        'password' => 'password',
        'tax_id' => 'tax_id',
        'company' => 'company',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'address1' => 'address1',
        'address2' => 'address2',
        'postcode' => 'postcode',
        'country_code' => 'country_code',
        'zone_code' => 'zone_code',
        'city' => 'city',
        'phone' => 'phone',
        'mobile' => 'mobile',
        'different_shipping_address' => 'different_shipping_address',
        'newsletter' => 'newsletter',
      );
      foreach ($key_map as $skey => $tkey) {
        $this->data[$tkey] = $customer[$skey];
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
      foreach ($key_map as $skey => $tkey){
        $this->data['shipping_address'][$tkey] = $customer[$skey];
      }
    }
    
    public function save() {
    
      if (empty($this->data['id'])) {
        $this->system->database->query(
          "insert into ". DB_TABLE_CUSTOMERS ."
          (date_created)
          values ('". $this->system->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $this->system->database->insert_id();
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_CUSTOMERS ."
        set
          email = '". $this->system->database->input($this->data['email']) ."',
          tax_id = '". $this->system->database->input($this->data['tax_id']) ."',
          company = '". $this->system->database->input($this->data['company']) ."',
          firstname = '". $this->system->database->input($this->data['firstname']) ."',
          lastname = '". $this->system->database->input($this->data['lastname']) ."',
          address1 = '". $this->system->database->input($this->data['address1']) ."',
          address2 = '". $this->system->database->input($this->data['address2']) ."',
          postcode = '". $this->system->database->input($this->data['postcode']) ."',
          city = '". $this->system->database->input($this->data['city']) ."',
          country_code = '". $this->system->database->input($this->data['country_code']) ."',
          zone_code = '". $this->system->database->input($this->data['zone_code']) ."',
          phone = '". $this->system->database->input($this->data['phone']) ."',
          mobile = '". $this->system->database->input($this->data['mobile']) ."',
          different_shipping_address = '". (int)$this->data['different_shipping_address'] ."',
          shipping_company = '". $this->system->database->input($this->data['shipping_address']['company']) ."',
          shipping_firstname = '". $this->system->database->input($this->data['shipping_address']['firstname']) ."',
          shipping_lastname = '". $this->system->database->input($this->data['shipping_address']['lastname']) ."',
          shipping_address1 = '". $this->system->database->input($this->data['shipping_address']['address1']) ."',
          shipping_address2 = '". $this->system->database->input($this->data['shipping_address']['address2']) ."',
          shipping_postcode = '". $this->system->database->input($this->data['shipping_address']['postcode']) ."',
          shipping_city = '". $this->system->database->input($this->data['shipping_address']['city']) ."',
          shipping_country_code = '". $this->system->database->input($this->data['shipping_address']['country_code']) ."',
          shipping_zone_code = '". $this->system->database->input($this->data['shipping_address']['zone_code']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->system->cache->set_breakpoint();
    }
    
    public function set_password($password) {
      
      if (empty($this->data['email'])) trigger_error('Cannot set password without an e-mail address', E_USER_ERROR);
      
      if (empty($this->data['id'])) {
        $this->save();
      }
      
      $password_hash = $this->system->functions->password_hash($this->data['email'], $password, PASSWORD_SALT);
      
      $this->system->database->query(
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
    
      $this->system->database->query(
        "delete from ". DB_TABLE_CUSTOMERS ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->data['id'] = null;
      
      $this->system->cache->set_breakpoint();
    }
  }

?>