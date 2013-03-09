<?php

  class ctrl_tax_rate {
    public $data = array();
    
    public function __construct($tax_rate_id=null) {
      global $system;
      
      $this->system = &$system;
      
      if ($tax_rate_id !== null) $this->load($tax_rate_id);
    }
    
    public function load($tax_rate_id) {
      $tax_rate_query = $this->system->database->query(
        "select * from ". DB_TABLE_TAX_RATES ."
        where id = '". (int)$tax_rate_id ."'
        limit 1;"
      );
      $this->data = $this->system->database->fetch($tax_rate_query);
      if (empty($this->data)) trigger_error('Could not find tax rate ('. $tax_rate_id .') in database.', E_USER_ERROR);
    }
    
    public function save() {
      if (empty($this->data['id'])) {
        $this->system->database->query(
          "insert into ". DB_TABLE_TAX_RATES ."
          (date_created)
          values ('". $this->system->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $this->system->database->insert_id();
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_TAX_RATES ."
        set
          tax_class_id = '". $this->system->database->input($this->data['tax_class_id']) ."',
          geo_zone_id = '". $this->system->database->input($this->data['geo_zone_id']) ."',
          name = '". $this->system->database->input($this->data['name']) ."',
          description = '". $this->system->database->input($this->data['description']) ."',
          type = '". $this->system->database->input($this->data['type']) ."',
          rate = '". $this->system->database->input($this->data['rate']) ."',
          customer_type = '". $this->system->database->input($this->data['customer_type']) ."',
          tax_id_rule = '". $this->system->database->input($this->data['tax_id_rule']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->system->cache->set_breakpoint();
    }
    
    public function delete() {
    
      $this->system->database->query(
        "delete from ". DB_TABLE_TAX_RATES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->data['id'] = null;
      
      $this->system->cache->set_breakpoint();
    }
  }

?>