<?php

  class ctrl_product_group {
    public $data = array();

    public function __construct($group_id=null) {
      if ($group_id !== null) {
        $this->load((int)$group_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PRODUCT_GROUPS .";"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_PRODUCT_GROUPS_INFO .";"
      );

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], array('id', 'product_group_id', 'language_code'))) continue;
        $this->data[$field['Field']] = array();
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = '';
        }
      }

      $this->data['values'] = array();
    }

    public function load($group_id) {

      $group_query = database::query(
        "select * from ". DB_TABLE_PRODUCT_GROUPS ."
        where id = '". (int)$group_id ."'
        limit 1;"
      );
      $this->data = database::fetch($group_query);
      if (empty($this->data)) trigger_error('Could not find product group (ID: '. (int)$group_id .') in database.', E_USER_ERROR);

      $group_info_query = database::query(
        "select name, language_code from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
        where product_group_id = '". (int)$group_id ."';"
      );
      while ($group = database::fetch($group_info_query)) {
        $this->data['name'][$group['language_code']] = $group['name'];
      }

      $values_query = database::query(
        "select * from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
        where product_group_id = '". (int)$group_id ."';"
      );
      while ($value = database::fetch($values_query)) {

        $this->data['values'][$value['id']] = $value;

        $values_info_query = database::query(
          "select name, language_code from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
          where product_group_value_id = '". (int)$value['id'] ."';"
        );
        while ($value_info = database::fetch($values_info_query)) {
          $this->data['values'][$value['id']]['name'][$value_info['language_code']] = $value_info['name'];
        }
      }
    }

    public function save() {

    // Group
      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PRODUCT_GROUPS ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PRODUCT_GROUPS ."
        set date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

    // Group info
      foreach (array_keys(language::$languages) as $language_code) {

        $group_info_query = database::query(
          "select id from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
          where product_group_id = '". (int)$this->data['id'] ."'
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
        $group_info = database::fetch($group_info_query);

        if (empty($group_info)) {
          database::query(
            "insert into ". DB_TABLE_PRODUCT_GROUPS_INFO ."
            (product_group_id, language_code)
            values ('". (int)$this->data['id'] ."', '". database::input($language_code) ."');"
          );
          $group_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PRODUCT_GROUPS_INFO ."
          set name = '". database::input($this->data['name'][$language_code]) ."'
          where id = '". (int)$group_info['id'] ."'
          and product_group_id = '". (int)$this->data['id'] ."'
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }

    // Delete values
      $values_query = database::query(
        "select id from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
        where product_group_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", array_column($this->data['values'], 'id')) ."');"
      );

      while ($value = database::fetch($values_query)) {

        $products_query = database::query(
          "select id from ". DB_TABLE_PRODUCTS ."
          where product_groups like '%". (int)$this->data['id'] ."-". (int)$value['id'] ."%';"
        );
        if (database::num_rows($products_query) > 0) trigger_error('Cannot delete value linked to products.', E_USER_ERROR);

        database::query(
          "delete from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
          where product_group_id = '". (int)$this->data['id'] ."'
          and id = '". (int)$value['id'] ."'
          limit 1;"
        );
        database::query(
          "delete from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
          where product_group_value_id = '". (int)$value['id'] ."';"
        );
      }

    // Update/Insert values
      foreach ($this->data['values'] as $value) {

        if (empty($value['id'])) {
          database::query(
            "insert into ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
            (product_group_id, date_created)
            values ('". $this->data['id'] ."', '". date('Y-m-d H:i:s') ."');"
          );
          $value['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
          set date_updated = '". date('Y-m-d H:i:s') ."'
          where id = '". (int)$value['id'] ."'
          limit 1;"
        );

        foreach (array_keys(language::$languages) as $language_code) {

          $value_info_query = database::query(
            "select id from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
            where product_group_value_id = '". (int)$value['id'] ."'
            and language_code = '". database::input($language_code) ."'
            limit 1;"
          );
          $value_info = database::fetch($value_info_query);

          if (empty($value_info)) {
            database::query(
              "insert into ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
              (product_group_value_id, language_code)
              values ('". $value['id'] ."', '". database::input($language_code) ."');"
            );
            $value_info['id'] = database::insert_id();
          }

          database::query(
            "update ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
            set name = '". database::input($value['name'][$language_code]) ."'
            where id = '". (int)$value_info['id'] ."'
            and product_group_value_id = '". (int)$value['id'] ."'
            and language_code = '". database::input($language_code) ."'
            limit 1;"
          );
        }
      }

      cache::clear_cache('product_groups');
    }

    public function delete() {

      if (empty($this->data['id'])) return;

    // Check products for product group
      $products_query = database::query(
        "select id from ". DB_TABLE_PRODUCTS ."
        where product_groups like '%". (int)$this->data['id'] ."-%';"
      );
      if (database::num_rows($products_query) > 0) trigger_error('Cannot delete group linked to products.', E_USER_ERROR);

    // Check products for product group values
      $values_query = database::query(
        "select id from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
        where product_group_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", array_column($this->data['values'], 'id')) ."');"
      );

      while ($value = database::fetch($values_query)) {

        $products_query = database::query(
          "select id from ". DB_TABLE_PRODUCTS ."
          where product_groups like '%". (int)$this->data['id'] ."-". (int)$value['id'] ."%';"
        );
        if (database::num_rows($products_query) > 0) trigger_error('Cannot delete product group value linked to products.', E_USER_ERROR);

      // Delete product group values
        database::query(
          "delete from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
          where product_group_id = '". (int)$this->data['id'] ."'
          and id = '". (int)$value['id'] ."'
          limit 1;"
        );
        database::query(
          "delete from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
          where product_group_value_id = '". (int)$value['id'] ."';"
        );
      }

    // Delete product group
      database::query(
        "delete from ". DB_TABLE_PRODUCT_GROUPS ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      database::query(
        "delete from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
        where product_group_id = '". (int)$this->data['id'] ."';"
      );

      $this->data['id'] = null;

      cache::clear_cache('product_groups');
    }
  }

?>