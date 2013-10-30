<?php

  class ctrl_supplier {
    public $data = array();
    
    public function __construct($supplier_id='') {
      
      if (!empty($supplier_id)) $this->load($supplier_id);
    }
    
    public function load($supplier_id) {
      $suppliers_query = database::query(
        "select * from ". DB_TABLE_SUPPLIERS ."
        where id='". (int)$supplier_id ."'
        limit 1;"
      );
      $this->data = database::fetch($suppliers_query);
    }
    
    public function save() {
    
      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_SUPPLIERS ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }
      
      database::query(
        "update ". DB_TABLE_SUPPLIERS ." set
        name = '". database::input($this->data['name']) ."',
        description = '". database::input($this->data['description'], true) ."',
        email = '". database::input($this->data['email']) ."',
        phone = '". database::input($this->data['phone']) ."',
        link = '". database::input($this->data['link']) ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      cache::set_breakpoint();
    }
    
    public function delete() {
    
      if (empty($this->data['id'])) return;
      
      $products_query = database::query(
        "select id from ". DB_TABLE_PRODUCTS ."
        where supplier_id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      if (database::num_rows($products_query) > 0) {
        notices::add('errors', language::translate('error_delete_supplier_not_empty_products', 'The supplier could not be deleted because there are products linked to it.'));
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }
      
      database::query(
        "delete from ". DB_TABLE_SUPPLIERS ."
        where id = '". $this->data['id'] ."'
        limit 1;"
      );
      
      $this->data['id'] = null;
      
      cache::set_breakpoint();
    }
  }

?>