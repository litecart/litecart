<?php

  class ctrl_tax_rate {
    public $data = array();
    
    public function __construct($tax_rate_id=null) {
      
      if ($tax_rate_id !== null) $this->load($tax_rate_id);
    }
    
    public function load($tax_rate_id) {
      $tax_rate_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_TAX_RATES ."
        where id = '". (int)$tax_rate_id ."'
        limit 1;"
      );
      $this->data = $GLOBALS['system']->database->fetch($tax_rate_query);
      if (empty($this->data)) trigger_error('Could not find tax rate ('. $tax_rate_id .') in database.', E_USER_ERROR);
    }
    
    public function save() {
      if (empty($this->data['id'])) {
        $GLOBALS['system']->database->query(
          "insert into ". DB_TABLE_TAX_RATES ."
          (date_created)
          values ('". $GLOBALS['system']->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $GLOBALS['system']->database->insert_id();
      }
      
      $GLOBALS['system']->database->query(
        "update ". DB_TABLE_TAX_RATES ."
        set
          tax_class_id = '". $GLOBALS['system']->database->input($this->data['tax_class_id']) ."',
          geo_zone_id = '". $GLOBALS['system']->database->input($this->data['geo_zone_id']) ."',
          name = '". $GLOBALS['system']->database->input($this->data['name']) ."',
          description = '". $GLOBALS['system']->database->input($this->data['description']) ."',
          type = '". $GLOBALS['system']->database->input($this->data['type']) ."',
          rate = '". $GLOBALS['system']->database->input($this->data['rate']) ."',
          customer_type = '". $GLOBALS['system']->database->input($this->data['customer_type']) ."',
          tax_id_rule = '". $GLOBALS['system']->database->input($this->data['tax_id_rule']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $GLOBALS['system']->cache->set_breakpoint();
    }
    
    public function delete() {
    
      $GLOBALS['system']->database->query(
        "delete from ". DB_TABLE_TAX_RATES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->data['id'] = null;
      
      $GLOBALS['system']->cache->set_breakpoint();
    }
  }

?>