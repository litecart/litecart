<?php

  class ctrl_product_group {
    public $data = array();
    
    public function __construct($group_id=null) {
      global $system;
      
      $this->system = &$system;
      
      $this->reset();
      
      if ($group_id !== null) $this->load($group_id);
    }
    
    public function reset() {
      $this->data = array(
        'id' => '',
        'name' => array(),
        'values' => array(),
      );
    }
    
    public function load($group_id) {
      
      $group_query = $this->system->database->query(
        "select * from ". DB_TABLE_PRODUCT_GROUPS ."
        where id = '". (int)$group_id ."'
        limit 1;"
      );
      $this->data = $this->system->database->fetch($group_query);
      if (empty($this->data)) trigger_error('Could not find product_group ('. $group_id .') in database.', E_USER_ERROR);
      
      $group_info_query = $this->system->database->query(
        "select name, language_code from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
        where product_group_id = '". (int)$group_id ."';"
      );
      while ($group = $this->system->database->fetch($group_info_query)) {
        $this->data['name'][$group['language_code']] = $group['name'];
      }
      
      $values_query = $this->system->database->query(
        "select * from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
        where product_group_id = '". (int)$group_id ."';"
      );
      while ($value = $this->system->database->fetch($values_query)) {
      
        $this->data['values'][$value['id']] = $value;
        
        $values_info_query = $this->system->database->query(
          "select name, language_code from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
          where product_group_value_id = '". (int)$value['id'] ."';"
        );
        while ($value_info = $this->system->database->fetch($values_info_query)) {
          $this->data['values'][$value['id']]['name'][$value_info['language_code']] = $value_info['name'];
        }
      }
    }
    
    public function save() {
      
    // Group
      if (empty($this->data['id'])) {
        $this->system->database->query(
          "insert into ". DB_TABLE_PRODUCT_GROUPS ."
          (date_created)
          values ('". $this->system->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $this->system->database->insert_id();
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_PRODUCT_GROUPS ."
        set date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
    // Group info
      foreach (array_keys($this->system->language->languages) as $language_code) {
        
        $group_info_query = $this->system->database->query(
          "select id from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
          where product_group_id = '". (int)$this->data['id'] ."'
          and language_code = '". $this->system->database->input($language_code) ."'
          limit 1;"
        );
        $group_info = $this->system->database->fetch($group_info_query);
        
        if (empty($group_info)) {
          $this->system->database->query(
            "insert into ". DB_TABLE_PRODUCT_GROUPS_INFO ."
            (product_group_id, language_code)
            values ('". (int)$this->data['id'] ."', '". $this->system->database->input($language_code) ."');"
          );
          $group_info['id'] = $this->system->database->insert_id();
        }
        
        $this->system->database->query(
          "update ". DB_TABLE_PRODUCT_GROUPS_INFO ."
          set name = '". $this->system->database->input($this->data['name'][$language_code]) ."'
          where id = '". (int)$group_info['id'] ."'
          and product_group_id = '". (int)$this->data['id'] ."'
          and language_code = '". $this->system->database->input($language_code) ."'
          limit 1;"
        );
      }
      
    // Delete values
      $values_query = $this->system->database->query(
        "select id from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
        where product_group_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['values'])) ."');"
      );
      
      while ($value = $this->system->database->fetch($values_query)) {
        
        $products_query = $this->system->database->query(
          "select id from ". DB_TABLE_PRODUCTS ."
          where product_groups like '%". (int)$this->data['id'] ."-". (int)$value['id'] ."%';"
        );
        if ($this->system->database->num_rows($products_query) > 0) trigger_error('Cannot delete value linked to products.', E_USER_ERROR);
      
        $this->system->database->query(
          "delete from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
          where product_group_id = '". (int)$this->data['id'] ."'
          and id = '". (int)$value['id'] ."'
          limit 1;"
        );
        $this->system->database->query(
          "delete from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
          where product_group_value_id = '". (int)$value['id'] ."';"
        );
      }
      
    // Update/Insert values
      foreach ($this->data['values'] as $value) {
        
        if (empty($value['id'])) {
          $this->system->database->query(
            "insert into ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
            (product_group_id, date_created)
            values ('". $this->data['id'] ."', '". date('Y-m-d H:i:s') ."');"
          );
          $value['id'] = $this->system->database->insert_id();
        }
        
        $this->system->database->query(
          "update ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
          set date_updated = '". date('Y-m-d H:i:s') ."'
          where id = '". (int)$value['id'] ."'
          limit 1;"
        );
        
        foreach (array_keys($this->system->language->languages) as $language_code) {
          
          $value_info_query = $this->system->database->query(
            "select id from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
            where product_group_value_id = '". (int)$value['id'] ."'
            and language_code = '". $this->system->database->input($language_code) ."'
            limit 1;"
          );
          $value_info = $this->system->database->fetch($value_info_query);
          
          if (empty($value_info)) {
            $this->system->database->query(
              "insert into ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
              (product_group_value_id, language_code)
              values ('". $value['id'] ."', '". $this->system->database->input($language_code) ."');"
            );
            $value_info['id'] = $this->system->database->insert_id();
          }
          
          $this->system->database->query(
            "update ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
            set name = '". $this->system->database->input($value['name'][$language_code]) ."'
            where id = '". (int)$value_info['id'] ."'
            and product_group_value_id = '". (int)$value['id'] ."'
            and language_code = '". $this->system->database->input($language_code) ."'
            limit 1;"
          );
        }
      }
      
      $this->system->cache->set_breakpoint();
    }
    
    public function delete() {
    
      if (empty($this->data['id'])) return;
    
    // Check products for product group
      $products_query = $this->system->database->query(
        "select id from ". DB_TABLE_PRODUCTS ."
        where product_groups like '%". (int)$this->data['id'] ."-%';"
      );
      if ($this->system->database->num_rows($products_query) > 0) trigger_error('Cannot delete group linked to products.', E_USER_ERROR);
    
    // Check products for product group values
      $values_query = $this->system->database->query(
        "select id from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
        where product_group_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['values'])) ."');"
      );
      
      while ($value = $this->system->database->fetch($values_query)) {
        
        $products_query = $this->system->database->query(
          "select id from ". DB_TABLE_PRODUCTS ."
          where product_groups like '%". (int)$this->data['id'] ."-". (int)$value['id'] ."%';"
        );
        if ($this->system->database->num_rows($products_query) > 0) trigger_error('Cannot delete product group value linked to products.', E_USER_ERROR);
        
      // Delete product group values
        $this->system->database->query(
          "delete from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
          where product_group_id = '". (int)$this->data['id'] ."'
          and id = '". (int)$value['id'] ."'
          limit 1;"
        );
        $this->system->database->query(
          "delete from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
          where product_group_value_id = '". (int)$value['id'] ."';"
        );
      }
      
    // Delete product group
      $this->system->database->query(
        "delete from ". DB_TABLE_PRODUCT_GROUPS ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->system->database->query(
        "delete from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
        where product_group_id = '". (int)$this->data['id'] ."';"
      );
      
      $this->data['id'] = null;
      
      $this->system->cache->set_breakpoint();
    }
  }

?>