<?php

  class ctrl_supplier {
    public $data = array();
    
    public function __construct($supplier_id='') {
      global $system;
      
      $this->system = &$system;
      
      if (!empty($supplier_id)) $this->load($supplier_id);
    }
    
    public function load($supplier_id) {
      $suppliers_query = $this->system->database->query(
        "select * from ". DB_TABLE_SUPPLIERS ."
        where id='". (int)$supplier_id ."'
        limit 1;"
      );
      $this->data = $this->system->database->fetch($suppliers_query);
    }
    
    public function save() {
    
      if (empty($this->data['id'])) {
        $this->system->database->query(
          "insert into ". DB_TABLE_SUPPLIERS ."
          (date_created)
          values ('". $this->system->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $this->system->database->insert_id();
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_SUPPLIERS ." set
        name = '". $this->system->database->input($this->data['name']) ."',
        description = '". $this->system->database->input($this->data['description'], true) ."',
        email = '". $this->system->database->input($this->data['email']) ."',
        phone = '". $this->system->database->input($this->data['phone']) ."',
        link = '". $this->system->database->input($this->data['link']) ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->system->cache->set_breakpoint();
    }
    
    public function delete() {
    
      if (empty($this->data['id'])) return;
      
      $products_query = $this->system->database->query(
        "select id from ". DB_TABLE_PRODUCTS ."
        where supplier_id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      if ($this->system->database->num_rows($products_query) > 0) {
        $this->system->notices->add('errors', $this->system->language->translate('error_delete_supplier_not_empty_products', 'The supplier could not be deleted because there are products linked to it.'));
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }
      
      $this->system->database->query(
        "delete from ". DB_TABLE_SUPPLIERS ."
        where id = '". $this->data['id'] ."'
        limit 1;"
      );
      
      $this->data['id'] = null;
      
      $this->system->cache->set_breakpoint();
    }
  }

?>