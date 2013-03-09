<?php

  class ctrl_tax_class {
    public $data = array();
    
    public function __construct($tax_class_id=null) {
      global $system;
      
      $this->system = &$system;
      
      if ($tax_class_id !== null) $this->load($tax_class_id);
    }
    
    public function load($tax_class_id) {
      $tax_class_query = $this->system->database->query(
        "select * from ". DB_TABLE_TAX_CLASSES ."
        where id = '". (int)$tax_class_id ."'
        limit 1;"
      );
      $this->data = $this->system->database->fetch($tax_class_query);
      if (empty($this->data)) trigger_error('Could not find tax class ('. $tax_class_id .') in database.', E_USER_ERROR);
    }
    
    public function save() {
      if (empty($this->data['id'])) {
        $this->system->database->query(
          "insert into ". DB_TABLE_TAX_CLASSES ."
          (date_created)
          values ('". $this->system->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $this->system->database->insert_id();
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_TAX_CLASSES ."
        set
          name = '". $this->system->database->input($this->data['name']) ."',
          description = '". $this->system->database->input($this->data['description']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->system->cache->set_breakpoint();
    }
    
    public function delete() {
    
      $this->system->database->query(
        "delete from ". DB_TABLE_TAX_CLASSES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->data['id'] = null;
      
      $this->system->cache->set_breakpoint();
    }
  }

?>