<?php

  class ctrl_currency {
    public $data = array();
    
    public function __construct($currency_code=null) {
      if ($currency_code !== null) $this->load($currency_code);
    }
    
    public function load($currency_code) {
      $currency_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_CURRENCIES ."
        where code='". $GLOBALS['system']->database->input($currency_code) ."'
        limit 1;"
      );
      $this->data = $GLOBALS['system']->database->fetch($currency_query);
      if (empty($this->data)) trigger_error('Could not find currency ('. $currency_code .') in database.', E_USER_ERROR);
    }
    
    public function save() {
    
      if (empty($this->data['status']) && $this->data['code'] == $GLOBALS['system']->settings->get('store_currency_code')) {
        trigger_error('You cannot disable the store currency.', E_USER_ERROR);
        return;
      }
    
      if (empty($this->data['status']) && $this->data['code'] == $GLOBALS['system']->settings->get('default_currency_code')) {
        trigger_error('You cannot disable the default currency.', E_USER_ERROR);
        return;
      }
    
      if (!empty($this->data['id'])) {
        $currencies_query = $GLOBALS['system']->database->query(
          "select * from ". DB_TABLE_CURRENCIES ."
          where id = '". (int)$this->data['id'] ."'
          limit 1;"
        );
        $currency = $GLOBALS['system']->database->fetch($currencies_query);
        if ($this->data['code'] != $currency['code']) {
          if ($currency['code'] == $GLOBALS['system']->settings->get('store_currency_code')) {
            trigger_error('Cannot rename the store system currency.', E_USER_ERROR);
          } else {
            $GLOBALS['system']->database->query(
              "alter table ". DB_TABLE_PRODUCTS_PRICES ."
              change `". $GLOBALS['system']->database->input($currency['code']) ."` `". $GLOBALS['system']->database->input($this->data['code']) ."` decimal(11, 4) not null;"
            );
            $GLOBALS['system']->database->query(
              "alter table ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
              change `". $GLOBALS['system']->database->input($currency['code']) ."` `". $GLOBALS['system']->database->input($this->data['code']) ."` decimal(11, 4) not null;"
            );
            $GLOBALS['system']->database->query(
              "alter table ". DB_TABLE_PRODUCTS_OPTIONS ."
              change `". $GLOBALS['system']->database->input($currency['code']) ."` `". $GLOBALS['system']->database->input($this->data['code']) ."` decimal(11, 4) not null;"
            );
          }
        }
      }
      
      if (empty($this->data['id'])) {
        $GLOBALS['system']->database->query(
          "insert into ". DB_TABLE_CURRENCIES ."
          (date_created)
          values ('". $GLOBALS['system']->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $GLOBALS['system']->database->insert_id();
      }
      
      $products_prices_query = $GLOBALS['system']->database->query(
        "show fields from ". DB_TABLE_PRODUCTS_PRICES ."
        where `Field` = '". $GLOBALS['system']->database->input($this->data['code']) ."';"
      );
      if ($GLOBALS['system']->database->num_rows($products_prices_query) == 0) {
        $GLOBALS['system']->database->query(
          "alter table ". DB_TABLE_PRODUCTS_PRICES ."
          add `". $GLOBALS['system']->database->input($this->data['code']) ."` decimal(11, 4) not null;"
        );
      }
      
      $products_campaigns_query = $GLOBALS['system']->database->query(
        "show fields from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
        where `Field` = '". $GLOBALS['system']->database->input($this->data['code']) ."';"
      );
      if ($GLOBALS['system']->database->num_rows($products_campaigns_query) == 0) {
        $GLOBALS['system']->database->query(
          "alter table ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
          add `". $GLOBALS['system']->database->input($this->data['code']) ."` decimal(11, 4) not null;"
        );
      }
      
      $products_options_query = $GLOBALS['system']->database->query(
        "show fields from ". DB_TABLE_PRODUCTS_OPTIONS ."
        where `Field` = '". $GLOBALS['system']->database->input($this->data['code']) ."';"
      );
      if ($GLOBALS['system']->database->num_rows($products_options_query) == 0) {
        $GLOBALS['system']->database->query(
          "alter table ". DB_TABLE_PRODUCTS_OPTIONS ."
          add `". $GLOBALS['system']->database->input($this->data['code']) ."` decimal(11, 4) not null after `price_operator`;"
        );
      }
      
      $GLOBALS['system']->database->query(
        "update ". DB_TABLE_CURRENCIES ."
        set
          status = '". (int)$this->data['status'] ."',
          code = '". $GLOBALS['system']->database->input($this->data['code']) ."',
          name = '". $GLOBALS['system']->database->input($this->data['name']) ."',
          value = '". $GLOBALS['system']->database->input($this->data['value']) ."',
          prefix = '". $GLOBALS['system']->database->input($this->data['prefix']) ."',
          suffix = '". $GLOBALS['system']->database->input($this->data['suffix']) ."',
          decimals = '". (int)$this->data['decimals'] ."',
          priority = '". (int)$this->data['priority'] ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $GLOBALS['system']->cache->set_breakpoint();
    }
    
    public function delete() {
    
      if ($this->data['code'] == $GLOBALS['system']->settings->get('store_currency_code')) {
        trigger_error('Cannot delete the store system currency', E_USER_ERROR);
        return;
      }
      
      if ($this->data['code'] == $GLOBALS['system']->settings->get('default_currency_code')) {
        trigger_error('Cannot delete the default currency', E_USER_ERROR);
        return;
      }
      
      $GLOBALS['system']->database->query(
        "delete from ". DB_TABLE_CURRENCIES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->data['id'] = null;
      
      $GLOBALS['system']->cache->set_breakpoint();
    }
  }

?>