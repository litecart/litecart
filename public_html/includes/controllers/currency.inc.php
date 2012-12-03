<?php

  class ctrl_currency {
    public $data = array();
    
    public function __construct($currency_code=null) {
      global $system;
      
      $this->system = &$system;
      
      if ($currency_code !== null) $this->load($currency_code);
    }
    
    public function load($currency_code) {
      $currency_query = $this->system->database->query(
        "select * from ". DB_TABLE_CURRENCIES ."
        where code='". $this->system->database->input($currency_code) ."'
        limit 1;"
      );
      $this->data = $this->system->database->fetch($currency_query);
      if (empty($this->data)) trigger_error('Could not find currency ('. $currency_code .') in database.', E_USER_ERROR);
    }
    
    public function save() {
    
      if (!empty($this->data['id'])) {
        $currencies_query = $this->system->database->query(
          "select * from ". DB_TABLE_CURRENCIES ."
          where id = '". (int)$this->data['id'] ."'
          limit 1;"
        );
        $currency = $this->system->database->fetch($currencies_query);
        if ($this->data['code'] != $currency['code']) {
          if ($currency['code'] == $this->system->settings->get('store_currency_code')) {
            trigger_error('Cannot rename the store system currency.', E_USER_ERROR);
          } else {
            $this->system->database->query(
              "alter table ". DB_TABLE_PRODUCTS_PRICES ."
              change `". $this->system->database->input($currency['code']) ."` `". $this->system->database->input($this->data['code']) ."` decimal(11, 4) not null;"
            );
            $this->system->database->query(
              "alter table ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
              change `". $this->system->database->input($currency['code']) ."` `". $this->system->database->input($this->data['code']) ."` decimal(11, 4) not null;"
            );
            $this->system->database->query(
              "alter table ". DB_TABLE_PRODUCTS_OPTIONS ."
              change `". $this->system->database->input($currency['code']) ."` `". $this->system->database->input($this->data['code']) ."` decimal(11, 4) not null;"
            );
          }
        }
      }
      
      if (empty($this->data['id'])) {
        $this->system->database->query(
          "insert into ". DB_TABLE_CURRENCIES ."
          (date_created)
          values ('". $this->system->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $this->system->database->insert_id();
      }
      
      $products_prices_query = $this->system->database->query(
        "show fields from ". DB_TABLE_PRODUCTS_PRICES ."
        where `Field` = '". $this->system->database->input($this->data['code']) ."';"
      );
      if ($this->system->database->num_rows($products_prices_query) == 0) {
        $this->system->database->query(
          "alter table ". DB_TABLE_PRODUCTS_PRICES ."
          add `". $this->system->database->input($this->data['code']) ."` decimal(11, 4) not null;"
        );
      }
      
      $products_campaigns_query = $this->system->database->query(
        "show fields from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
        where `Field` = '". $this->system->database->input($this->data['code']) ."';"
      );
      if ($this->system->database->num_rows($products_campaigns_query) == 0) {
        $this->system->database->query(
          "alter table ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
          add `". $this->system->database->input($this->data['code']) ."` decimal(11, 4) not null;"
        );
      }
      
      $products_options_query = $this->system->database->query(
        "show fields from ". DB_TABLE_PRODUCTS_OPTIONS ."
        where `Field` = '". $this->system->database->input($this->data['code']) ."';"
      );
      if ($this->system->database->num_rows($products_options_query) == 0) {
        $this->system->database->query(
          "alter table ". DB_TABLE_PRODUCTS_OPTIONS ."
          add `". $this->system->database->input($this->data['code']) ."` decimal(11, 4) not null after `price_operator`;"
        );
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_CURRENCIES ."
        set
          status = '". (int)$this->data['status'] ."',
          code = '". $this->system->database->input($this->data['code']) ."',
          name = '". $this->system->database->input($this->data['name']) ."',
          value = '". $this->system->database->input($this->data['value']) ."',
          prefix = '". $this->system->database->input($this->data['prefix']) ."',
          suffix = '". $this->system->database->input($this->data['suffix']) ."',
          decimals = '". (int)$this->data['decimals'] ."',
          priority = '". (int)$this->data['priority'] ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->system->cache->set_breakpoint();
    }
    
    public function delete() {
    
      if ($this->data['code'] == $this->system->settings->get('store_currency_code')) {
        trigger_error('Cannot delete the store system currency', E_USER_ERROR);
        return;
      }
      
      if ($this->data['code'] == $this->system->settings->get('default_currency_code')) {
        trigger_error('Cannot delete the store default currency', E_USER_ERROR);
        return;
      }
      
      $this->system->database->query(
        "delete from ". DB_TABLE_CURRENCIES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->data['id'] = null;
      
      $this->system->cache->set_breakpoint();
    }
  }

?>