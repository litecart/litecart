<?php

  class ref_category {

    private $_language_codes;
    private $_data = array();

    function __construct($category_id, $language_code=null) {

      if (empty($language_code)) $language_code = language::$selected['code'];

      $this->_data['id'] = (int)$category_id;
      $this->_language_codes = array_unique(array(
        $language_code,
        settings::get('default_language_code'),
        settings::get('store_language_code'),
      ));
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

          $this->_data['info'] = array();

          $query = database::query(
            "select * from ". DB_TABLE_CATEGORIES_INFO ."
            where category_id = ". (int)$this->_data['id'] ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              if (in_array($key, array('id', 'category_id', 'language_code'))) continue;
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

          $this->_data['path'] = array($this->id => $this);

          $current = $this;
          while ($current->parent_id) {

            $this->_data['path'][$current->parent_id] = $current->parent;
            $current = $current->parent;
          }

          $this->_data['path'] = array_reverse($this->_data['path'], true);

          break;

        case 'products':

          $this->_data['products'] = array();

          $query = database::query(
            "select id from ". DB_TABLE_PRODUCTS ."
            where status
            and id in (
              select product_id from ". DB_TABLE_PRODUCTS_TO_CATEGORIES ."
              where category_id = ". (int)$this->_data['id'] ."
            )
            and (quantity > 0 or sold_out_status_id in (
              select id from ". DB_TABLE_SOLD_OUT_STATUSES ."
              where (hidden is null or hidden = 0)
            ))
            and (date_valid_from <= '". date('Y-m-d H:i:s') ."')
            and (year(date_valid_to) < '1971' or date_valid_to >= '". date('Y-m-d H:i:s') ."');"
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
            "select count(id) as num_products from ". DB_TABLE_PRODUCTS ."
            where status
            and id in (
              select product_id from ". DB_TABLE_PRODUCTS_TO_CATEGORIES ."
              where category_id = ". (int)$this->_data['id'] ."
            )
            and (quantity > 0 or sold_out_status_id in (
              select id from ". DB_TABLE_SOLD_OUT_STATUSES ."
              where (hidden is null or hidden = 0)
            ))
            and (date_valid_from <= '". date('Y-m-d H:i:s') ."')
            and (year(date_valid_to) < '1971' or date_valid_to >= '". date('Y-m-d H:i:s') ."');"
          );

          $this->_data['num_products'] = (int)database::fetch($query, 'num_products');

          break;

        case 'siblings':

          $this->_data['siblings'] = array();

          if (empty($this->parent_id)) return;

          $query = database::query(
            "select id from ". DB_TABLE_CATEGORIES ."
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

          $this->_data['descendants'] = array();

          $categories_query = database::query(
            "select @pv:=id as id, parent_id from ". DB_TABLE_CATEGORIES ."
            join (select @pv := ". (int)$this->_data['id'] .") tmp
            where status
            and parent_id = @pv;"
          );

          while ($row = database::fetch($categories_query)) {
            $this->_data['descendants'][$row['id']] = reference::category($row['id']);
          }

          break;

        case 'subcategories': // To be deprecated
        case 'children':

          $this->_data['subcategories'] = array();

          $query = database::query(
            "select id from ". DB_TABLE_CATEGORIES ."
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
            "select * from ". DB_TABLE_CATEGORIES ."
            where id = ". (int)$this->_data['id'] ."
            limit 1;"
          );

          if (!$row = database::fetch($query)) return;

          foreach ($row as $key => $value) {
            switch($key) {
              case 'keywords':
                $this->_data[$key] = explode(',', $row[$key]);
                break;

              default:
                $this->_data[$key] = $value;
                break;
            }
          }

          break;
      }
    }
  }
