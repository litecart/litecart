<?php

  class ent_supplier {
    public $data;
    public $previous;

    public function __construct($supplier_id=null) {

      if (!empty($supplier_id)) {
        $this->load($supplier_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."suppliers;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = '';
      }

      $this->previous = $this->data;
    }

    public function load($supplier_id) {

      if (!preg_match('#^[0-9]+$#', $supplier_id)) throw new Exception('Invalid supplier (ID: '. $supplier_id .')');

      $this->reset();

      $supplier_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."suppliers
        where id=". (int)$supplier_id ."
        limit 1;"
      );

      if ($supplier = database::fetch($supplier_query)) {
        $this->data = array_replace($this->data, array_intersect_key($supplier, $this->data));
      } else {
        throw new Exception('Could not find supplier (ID: '. (int)$supplier_id .') in database.');
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."suppliers
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."suppliers set
        code = '". database::input($this->data['code']) ."',
        name = '". database::input($this->data['name']) ."',
        description = '". database::input($this->data['description'], true) ."',
        email = '". database::input($this->data['email']) ."',
        phone = '". database::input($this->data['phone']) ."',
        link = '". database::input($this->data['link']) ."',
        date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->previous = $this->data;

      cache::clear_cache('suppliers');
    }

    public function delete() {

      if (empty($this->data['id'])) return;

      $products_query = database::query(
        "select id from ". DB_TABLE_PREFIX ."products
        where supplier_id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      if (database::num_rows($products_query) > 0) {
        notices::add('errors', language::translate('error_delete_supplier_not_empty_products', 'The supplier could not be deleted because there are products linked to it.'));
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }

      database::query(
        "delete from ". DB_TABLE_PREFIX ."suppliers
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->reset();

      cache::clear_cache('suppliers');
    }
  }
