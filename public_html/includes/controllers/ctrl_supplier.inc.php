<?php

  class ctrl_supplier {
    public $data = array();

    public function __construct($supplier_id=null) {
      if (!empty($supplier_id)) {
        $this->load((int)$supplier_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_SUPPLIERS .";"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }
    }

    public function load($supplier_id) {
      $suppliers_query = database::query(
        "select * from ". DB_TABLE_SUPPLIERS ."
        where id='". (int)$supplier_id ."'
        limit 1;"
      );
      $this->data = database::fetch($suppliers_query);
      if (empty($this->data)) trigger_error('Could not find supplier (ID: '. (int)$supplier_id .') in database.', E_USER_ERROR);
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
        link = '". database::input($this->data['link']) ."',
        date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      cache::clear_cache('suppliers');
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

      cache::clear_cache('suppliers');
    }
  }

?>