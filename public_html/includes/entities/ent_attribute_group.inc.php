<?php

  class ent_attribute_group {
    public $data;
    public $previous;

    public function __construct($group_id=null) {

      if (!empty($group_id)) {
        $this->load($group_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."attribute_groups;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field);
      }

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."attribute_groups_info;"
      );

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], ['id', 'group_id', 'language_code'])) continue;

        $this->data[$field['Field']] = [];
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = database::create_variable($field);
        }
      }

      $this->data['values'] = [];

      $this->previous = $this->data;
    }

    public function load($group_id) {

      if (!preg_match('#^[0-9]+$#', $group_id)) throw new Exception('Invalid attribute (ID: '. $group_id .')');

      $this->reset();

      $group = database::query(
        "select * from ". DB_TABLE_PREFIX ."attribute_groups
        where id = ". (int)$group_id ."
        limit 1;"
      )->fetch();

      if ($group) {
        $this->data = array_replace($this->data, array_intersect_key($group, $this->data));
      } else {
        throw new Exception('Could not find attribute (ID: '. (int)$group_id .') in database.');
      }

      $group_info_query = database::query(
        "select name, language_code from ". DB_TABLE_PREFIX ."attribute_groups_info
        where group_id = ". (int)$group_id .";"
      );

      while ($group = database::fetch($group_info_query)) {
        $this->data['name'][$group['language_code']] = $group['name'];
      }

      $values_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."attribute_values
        where group_id = ". (int)$group_id ."
        order by priority;"
      );

      while ($value = database::fetch($values_query)) {

        $values_info_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."attribute_values_info
          where value_id = ". (int)$value['id'] .";"
        );

        while ($value_info = database::fetch($values_info_query)) {
          foreach (array_keys($value_info) as $key) {
            if (in_array($key, ['id', 'value_id', 'language_code'])) continue;
            $value[$key][$value_info['language_code']] = $value_info[$key];
          }
        }

        $value['in_use'] = database::query(
          "select id from ". DB_TABLE_PREFIX ."products_attributes
          where value_id = ". (int)$value['id'] ."
          limit 1;"
        )->num_rows ? true : false;

        $this->data['values'][] = $value;
      }

      $this->previous = $this->data;
    }

    public function save() {

    // Group
      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."attribute_groups
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."attribute_groups
        set code = '". database::input($this->data['code']) ."',
          sort = '". database::input($this->data['sort']) ."',
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

    // Group info
      foreach (array_keys(language::$languages) as $language_code) {

        $group_info_query = database::query(
          "select id from ". DB_TABLE_PREFIX ."attribute_groups_info
          where group_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );

        if (!$group_info = database::fetch($group_info_query)) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."attribute_groups_info
            (group_id, language_code)
            values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
          );
          $group_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."attribute_groups_info
          set name = '". database::input($this->data['name'][$language_code]) ."'
          where id = ". (int)$group_info['id'] ."
          and group_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }

    // Delete values
      $values_query = database::query(
        "select id from ". DB_TABLE_PREFIX ."attribute_values
        where group_id = ". (int)$this->data['id'] ."
        and id not in ('". implode("', '", array_column($this->data['values'], 'id')) ."');"
      );

      while ($value = database::fetch($values_query)) {

        $products_attributes_query = database::query(
          "select id from ". DB_TABLE_PREFIX ."products_attributes
          where value_id = ". (int)$value['id'] ."
          limit 1;"
        );

        if (database::num_rows($products_attributes_query)) {
          throw new Exception('Cannot delete value linked to product attributes');
        }

        database::query(
          "delete from ". DB_TABLE_PREFIX ."attribute_values
          where group_id = ". (int)$this->data['id'] ."
          and id = ". (int)$value['id'] ."
          limit 1;"
        );

        database::query(
          "delete from ". DB_TABLE_PREFIX ."attribute_values_info
          where value_id = ". (int)$value['id'] .";"
        );
      }

    // Update/Insert values
      $i = 0;
      foreach ($this->data['values'] as $key => $value) {

        if (empty($value['id'])) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."attribute_values
            (group_id, date_created)
            values (". (int)$this->data['id'] .", '". ($this->data['values'][$key]['date_created'] = date('Y-m-d H:i:s')) ."');"
          );
          $value['id'] = $this->data['values'][$key]['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."attribute_values
          set priority = ". (int)$i++ .",
            date_updated = '". ($this->data['values'][$key]['date_updated'] = date('Y-m-d H:i:s')) ."'
          where id = ". (int)$value['id'] ."
          limit 1;"
        );

        foreach (array_keys(language::$languages) as $language_code) {

          $value_info_query = database::query(
            "select id from ". DB_TABLE_PREFIX ."attribute_values_info
            where value_id = ". (int)$value['id'] ."
            and language_code = '". database::input($language_code) ."'
            limit 1;"
          );

          if (!$value_info = database::fetch($value_info_query)) {
            database::query(
              "insert into ". DB_TABLE_PREFIX ."attribute_values_info
              (value_id, language_code)
              values ('". $value['id'] ."', '". database::input($language_code) ."');"
            );
            $value_info['id'] = database::insert_id();
          }

          database::query(
            "update ". DB_TABLE_PREFIX ."attribute_values_info
            set name = '". (isset($value['name'][$language_code]) ? database::input($value['name'][$language_code]) : '') ."'
            where id = ". (int)$value_info['id'] ."
            and value_id = ". (int)$value['id'] ."
            and language_code = '". database::input($language_code) ."'
            limit 1;"
          );
        }
      }

      $this->previous = $this->data;

      cache::clear_cache('attributes');
    }

    public function delete() {

      if (empty($this->data['id'])) return;

    // Check category filters for attribute
      $category_filters_query = database::query(
        "select id from ". DB_TABLE_PREFIX ."categories_filters
        where group_id = ". (int)$this->data['id'] .";"
      );

      if (database::num_rows($category_filters_query)) {
        throw new Exception('Cannot delete group linked to products');
      }

    // Check products for attribute
      $product_attributes_query = database::query(
        "select id from ". DB_TABLE_PREFIX ."products_attributes
        where group_id = ". (int)$this->data['id'] .";"
      );

      if (database::num_rows($product_attributes_query)) {
        throw new Exception('Cannot delete group linked to products');
      }

      $this->data['values'] = [];
      $this->save();

    // Delete attribute
      database::query(
        "delete ag, agi, av, avi
        from ". DB_TABLE_PREFIX ."attribute_groups ag
        left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = ag.id)
        left join ". DB_TABLE_PREFIX ."attribute_values av on (av.group_id = ag.id)
        left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = av.id)
        where ag.id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('attributes');
    }
  }
