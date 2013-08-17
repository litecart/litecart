<?php

  class ctrl_supplier {
    public $data = array();
    
    public function __construct($supplier_id='') {
      
      if (!empty($supplier_id)) $this->load($supplier_id);
    }
    
    public function load($supplier_id) {
      $suppliers_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_SUPPLIERS ."
        where id='". (int)$supplier_id ."'
        limit 1;"
      );
      $this->data = $GLOBALS['system']->database->fetch($suppliers_query);
    }
    
    public function save() {
    
      if (empty($this->data['id'])) {
        $GLOBALS['system']->database->query(
          "insert into ". DB_TABLE_SUPPLIERS ."
          (date_created)
          values ('". $GLOBALS['system']->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $GLOBALS['system']->database->insert_id();
      }
      
      $GLOBALS['system']->database->query(
        "update ". DB_TABLE_SUPPLIERS ." set
        name = '". $GLOBALS['system']->database->input($this->data['name']) ."',
        description = '". $GLOBALS['system']->database->input($this->data['description'], true) ."',
        email = '". $GLOBALS['system']->database->input($this->data['email']) ."',
        phone = '". $GLOBALS['system']->database->input($this->data['phone']) ."',
        link = '". $GLOBALS['system']->database->input($this->data['link']) ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $GLOBALS['system']->cache->set_breakpoint();
    }
    
    public function delete() {
    
      if (empty($this->data['id'])) return;
      
      $products_query = $GLOBALS['system']->database->query(
        "select id from ". DB_TABLE_PRODUCTS ."
        where supplier_id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      if ($GLOBALS['system']->database->num_rows($products_query) > 0) {
        $GLOBALS['system']->notices->add('errors', $GLOBALS['system']->language->translate('error_delete_supplier_not_empty_products', 'The supplier could not be deleted because there are products linked to it.'));
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }
      
      $GLOBALS['system']->database->query(
        "delete from ". DB_TABLE_SUPPLIERS ."
        where id = '". $this->data['id'] ."'
        limit 1;"
      );
      
      $this->data['id'] = null;
      
      $GLOBALS['system']->cache->set_breakpoint();
    }
  }

?>