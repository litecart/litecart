<?php

  class ctrl_product_group {
    public $data = array();
    
    public function __construct($group_id=null) {
      
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
      
      $group_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_PRODUCT_GROUPS ."
        where id = '". (int)$group_id ."'
        limit 1;"
      );
      $this->data = $GLOBALS['system']->database->fetch($group_query);
      if (empty($this->data)) trigger_error('Could not find product_group ('. $group_id .') in database.', E_USER_ERROR);
      
      $group_info_query = $GLOBALS['system']->database->query(
        "select name, language_code from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
        where product_group_id = '". (int)$group_id ."';"
      );
      while ($group = $GLOBALS['system']->database->fetch($group_info_query)) {
        $this->data['name'][$group['language_code']] = $group['name'];
      }
      
      $values_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
        where product_group_id = '". (int)$group_id ."';"
      );
      while ($value = $GLOBALS['system']->database->fetch($values_query)) {
      
        $this->data['values'][$value['id']] = $value;
        
        $values_info_query = $GLOBALS['system']->database->query(
          "select name, language_code from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
          where product_group_value_id = '". (int)$value['id'] ."';"
        );
        while ($value_info = $GLOBALS['system']->database->fetch($values_info_query)) {
          $this->data['values'][$value['id']]['name'][$value_info['language_code']] = $value_info['name'];
        }
      }
    }
    
    public function save() {
      
    // Group
      if (empty($this->data['id'])) {
        $GLOBALS['system']->database->query(
          "insert into ". DB_TABLE_PRODUCT_GROUPS ."
          (date_created)
          values ('". $GLOBALS['system']->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $GLOBALS['system']->database->insert_id();
      }
      
      $GLOBALS['system']->database->query(
        "update ". DB_TABLE_PRODUCT_GROUPS ."
        set date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
    // Group info
      foreach (array_keys($GLOBALS['system']->language->languages) as $language_code) {
        
        $group_info_query = $GLOBALS['system']->database->query(
          "select id from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
          where product_group_id = '". (int)$this->data['id'] ."'
          and language_code = '". $GLOBALS['system']->database->input($language_code) ."'
          limit 1;"
        );
        $group_info = $GLOBALS['system']->database->fetch($group_info_query);
        
        if (empty($group_info)) {
          $GLOBALS['system']->database->query(
            "insert into ". DB_TABLE_PRODUCT_GROUPS_INFO ."
            (product_group_id, language_code)
            values ('". (int)$this->data['id'] ."', '". $GLOBALS['system']->database->input($language_code) ."');"
          );
          $group_info['id'] = $GLOBALS['system']->database->insert_id();
        }
        
        $GLOBALS['system']->database->query(
          "update ". DB_TABLE_PRODUCT_GROUPS_INFO ."
          set name = '". $GLOBALS['system']->database->input($this->data['name'][$language_code]) ."'
          where id = '". (int)$group_info['id'] ."'
          and product_group_id = '". (int)$this->data['id'] ."'
          and language_code = '". $GLOBALS['system']->database->input($language_code) ."'
          limit 1;"
        );
      }
      
    // Delete values
      $values_query = $GLOBALS['system']->database->query(
        "select id from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
        where product_group_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['values'])) ."');"
      );
      
      while ($value = $GLOBALS['system']->database->fetch($values_query)) {
        
        $products_query = $GLOBALS['system']->database->query(
          "select id from ". DB_TABLE_PRODUCTS ."
          where product_groups like '%". (int)$this->data['id'] ."-". (int)$value['id'] ."%';"
        );
        if ($GLOBALS['system']->database->num_rows($products_query) > 0) trigger_error('Cannot delete value linked to products.', E_USER_ERROR);
      
        $GLOBALS['system']->database->query(
          "delete from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
          where product_group_id = '". (int)$this->data['id'] ."'
          and id = '". (int)$value['id'] ."'
          limit 1;"
        );
        $GLOBALS['system']->database->query(
          "delete from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
          where product_group_value_id = '". (int)$value['id'] ."';"
        );
      }
      
    // Update/Insert values
      foreach ($this->data['values'] as $value) {
        
        if (empty($value['id'])) {
          $GLOBALS['system']->database->query(
            "insert into ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
            (product_group_id, date_created)
            values ('". $this->data['id'] ."', '". date('Y-m-d H:i:s') ."');"
          );
          $value['id'] = $GLOBALS['system']->database->insert_id();
        }
        
        $GLOBALS['system']->database->query(
          "update ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
          set date_updated = '". date('Y-m-d H:i:s') ."'
          where id = '". (int)$value['id'] ."'
          limit 1;"
        );
        
        foreach (array_keys($GLOBALS['system']->language->languages) as $language_code) {
          
          $value_info_query = $GLOBALS['system']->database->query(
            "select id from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
            where product_group_value_id = '". (int)$value['id'] ."'
            and language_code = '". $GLOBALS['system']->database->input($language_code) ."'
            limit 1;"
          );
          $value_info = $GLOBALS['system']->database->fetch($value_info_query);
          
          if (empty($value_info)) {
            $GLOBALS['system']->database->query(
              "insert into ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
              (product_group_value_id, language_code)
              values ('". $value['id'] ."', '". $GLOBALS['system']->database->input($language_code) ."');"
            );
            $value_info['id'] = $GLOBALS['system']->database->insert_id();
          }
          
          $GLOBALS['system']->database->query(
            "update ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
            set name = '". $GLOBALS['system']->database->input($value['name'][$language_code]) ."'
            where id = '". (int)$value_info['id'] ."'
            and product_group_value_id = '". (int)$value['id'] ."'
            and language_code = '". $GLOBALS['system']->database->input($language_code) ."'
            limit 1;"
          );
        }
      }
      
      $GLOBALS['system']->cache->set_breakpoint();
    }
    
    public function delete() {
    
      if (empty($this->data['id'])) return;
    
    // Check products for product group
      $products_query = $GLOBALS['system']->database->query(
        "select id from ". DB_TABLE_PRODUCTS ."
        where product_groups like '%". (int)$this->data['id'] ."-%';"
      );
      if ($GLOBALS['system']->database->num_rows($products_query) > 0) trigger_error('Cannot delete group linked to products.', E_USER_ERROR);
    
    // Check products for product group values
      $values_query = $GLOBALS['system']->database->query(
        "select id from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
        where product_group_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['values'])) ."');"
      );
      
      while ($value = $GLOBALS['system']->database->fetch($values_query)) {
        
        $products_query = $GLOBALS['system']->database->query(
          "select id from ". DB_TABLE_PRODUCTS ."
          where product_groups like '%". (int)$this->data['id'] ."-". (int)$value['id'] ."%';"
        );
        if ($GLOBALS['system']->database->num_rows($products_query) > 0) trigger_error('Cannot delete product group value linked to products.', E_USER_ERROR);
        
      // Delete product group values
        $GLOBALS['system']->database->query(
          "delete from ". DB_TABLE_PRODUCT_GROUPS_VALUES ."
          where product_group_id = '". (int)$this->data['id'] ."'
          and id = '". (int)$value['id'] ."'
          limit 1;"
        );
        $GLOBALS['system']->database->query(
          "delete from ". DB_TABLE_PRODUCT_GROUPS_VALUES_INFO ."
          where product_group_value_id = '". (int)$value['id'] ."';"
        );
      }
      
    // Delete product group
      $GLOBALS['system']->database->query(
        "delete from ". DB_TABLE_PRODUCT_GROUPS ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $GLOBALS['system']->database->query(
        "delete from ". DB_TABLE_PRODUCT_GROUPS_INFO ."
        where product_group_id = '". (int)$this->data['id'] ."';"
      );
      
      $this->data['id'] = null;
      
      $GLOBALS['system']->cache->set_breakpoint();
    }
  }

?>