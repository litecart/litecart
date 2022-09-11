<?php

  class ref_category {

    private $_language_codes;
    private $_data = [];

    function __construct($category_id, $language_code=null) {

      if (empty($language_code)) $language_code = language::$selected['code'];

      $this->_data['id'] = (int)$category_id;
      $this->_language_codes = array_unique([
        $language_code,
        settings::get('default_language_code'),
        settings::get('store_language_code'),
      ]);
    }

    public function &__get($name) {

      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }

      $this->_data[$name] = null;
      $this->_load($name);

      return $this->_data[$name];
    }

    public function &__isset($name) {
      return $this->__get($name);
    }

    public function __set($name, $value) {
      trigger_error('Setting data is prohibited ('.$name.')', E_USER_WARNING);
    }

    private function _load($field) {

      switch($field) {

        case 'name':
        case 'description':
        case 'short_description':
        case 'head_title':
        case 'meta_description':
        case 'h1_title':

          $this->_data['info'] = [];

          $query = database::query(
            "select * from ". DB_TABLE_PREFIX ."categories_info
            where category_id = ". (int)$this->_data['id'] ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              if (in_array($key, ['id', 'category_id', 'language_code'])) continue;
              if (empty($this->_data[$key])) $this->_data[$key] = $row[$key];
            }
          }

          break;

        case 'parent':

          $this->_data['parent'] = false;

          if (empty($this->parent_id)) return;

          $this->_data['parent'] = reference::category($this->parent_id, $this->_language_codes[0]);

          break;

        case 'path':

          $this->_data['path'] = [$this->id => $this];

          $current = $this;
          while ($current->parent_id) {
            $this->_data['path'] = [$current->parent_id => $current->parent] + $this->_data['path'];
            $current = $current->parent;
          }

          break;

        case 'products':

          $this->_data['products'] = [];

          $query = database::query(
            "select id from ". DB_TABLE_PREFIX ."products
            where status
            and id in (
              select product_id from ". DB_TABLE_PREFIX ."products_to_categories
              where category_id = ". (int)$this->_data['id'] ."
            )
            and (quantity > 0 or sold_out_status_id in (
              select id from ". DB_TABLE_PREFIX ."sold_out_statuses
              where (hidden is null or hidden = 0)
            ))
            and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
            and (date_valid_to is null or year(date_valid_to) < '1971' or date_valid_to >= '". date('Y-m-d H:i:s') ."');"
          );

          while ($row = database::fetch($query)) {
            $this->_data['products'][$row['id']] = reference::product($row['id'], $this->_language_codes[0]);
          }

          break;

        case 'num_subcategories':

          if (!empty($this->_data['subcategories'])) {
            $this->_data['num_subcategories'] = count($this->_data['subcategories']);
            break;
          }

          $query = database::query(
            "select count(id) as num_subcategories from ". DB_PREFIX ."categories
            where status
            and parent_id ". (int)$this->_data['id'] .";"
          );

          $this->_data['num_subcategories'] = (int)database::fetch($query, 'num_subcategories');

          break;

        case 'num_products':

          if (!empty($this->_data['products'])) {
            $this->_data['num_products'] = count($this->_data['products']);
            break;
          }

          $query = database::query(
            "select count(id) as num_products from ". DB_TABLE_PREFIX ."products
            where status
            and id in (
              select product_id from ". DB_TABLE_PREFIX ."products_to_categories
              where category_id = ". (int)$this->_data['id'] ."
              ". ($this->descendants ? "or category_id in (". implode(", ", array_keys($this->descendants)) .")" : "") ."
            )
            and (quantity > 0 or sold_out_status_id in (
              select id from ". DB_TABLE_PREFIX ."sold_out_statuses
              where (hidden is null or hidden = 0)
            ))
            and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
            and (date_valid_to is null or year(date_valid_to) < '1971' or date_valid_to >= '". date('Y-m-d H:i:s') ."');"
          );

          $this->_data['num_products'] = (int)database::fetch($query, 'num_products');

          break;

        case 'siblings':

          $this->_data['siblings'] = [];

          if (empty($this->parent_id)) return;

          $query = database::query(
            "select id from ". DB_TABLE_PREFIX ."categories
            where status
            and parent_id = ". (int)$this->parent_id ."
            and id != ". (int)$this->_data['id'] ."
            order by priority;"
          );

          while ($row = database::fetch($query)) {
            $this->_data['siblings'][$row['id']] = reference::category($row['id'], $this->_language_codes[0]);
          }

          break;

        case 'descendants':

          $this->_data['descendants'] = [];

          $categories_query = database::query(
            "select id, parent_id from ". DB_TABLE_PREFIX ."categories
            join (select @parent_id := ". $this->_data['id'] .") tmp
            where find_in_set(parent_id, @parent_id)
            and length(@parent_id := concat(@parent_id, ',', id));"
          );

          while ($row = database::fetch($categories_query)) {
            $this->_data['descendants'][$row['id']] = reference::category($row['id']);
          }

          break;

        case 'subcategories': // To be deprecated
        case 'children':

          $this->_data['subcategories'] = [];

          $query = database::query(
            "select id from ". DB_TABLE_PREFIX ."categories
            where status
            and parent_id = ". (int)$this->_data['id'] ."
            order by priority;"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              $this->_data['subcategories'][$row['id']] = reference::category($row['id'], $this->_language_codes[0]);
            }
          }

          break;

        default:

          $query = database::query(
            "select * from ". DB_TABLE_PREFIX ."categories
            where id = ". (int)$this->_data['id'] ."
            limit 1;"
          );

          if (!$row = database::fetch($query)) return;

          foreach ($row as $key => $value) $this->_data[$key] = $value;

          $this->_data['keywords'] = preg_split('#\s*,\s*#', $this->_data['keywords'], -1, PREG_SPLIT_NO_EMPTY);

          break;
      }
    }
  }
