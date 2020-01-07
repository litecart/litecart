<?php

  class ref_category {

    private $_id;
    private $_cache_token;
    private $_language_codes;
    private $_data = array();

    function __construct($category_id, $language_code=null) {

      if (empty($language_code)) $language_code = language::$selected['code'];

      $this->_id = (int)$category_id;
      $this->_cache_token = cache::token('category_'.(int)$category_id, array($language_code), 'file');
      $this->_language_codes = array_unique(array(
        $language_code,
        settings::get('default_language_code'),
        settings::get('store_language_code'),
      ));

      if ($cache = cache::get($this->_cache_token)) {
        $this->_data = $cache;
      }
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
            where category_id = ". (int)$this->_id ."
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

          $this->_data['path'] = array($this->_id => $this);

          $current = $this;

          $failsafe = 0;
          while (!empty($current->parent_id)) {
            $this->_data['path'][$current->parent_id] = $current;
            $current = reference::category($current->parent_id, $current->_language_codes[0]);
            if (++$failsafe == 10) trigger_error('Endless loop while building category path', E_USER_ERROR);
          }

          $this->_data['path'] = array_reverse($this->_data['path'], true);

          break;

        case 'products':

          $this->_data['products'] = array();

          $query = database::query(
            "select id from ". DB_TABLE_PRODUCTS ."
            where status
            and find_in_set ('". database::input($this->_id) ."', categories);"
          );

          while ($row = database::fetch($query)) {
            $this->_data['products'][$row['id']] = reference::product($row['id'], $this->_language_codes[0]);
          }

          break;

        case 'siblings':

          $this->_data['siblings'] = array();

          if (empty($this->parent_id)) return;

          $query = database::query(
            "select id from ". DB_TABLE_CATEGORIES ."
            where status
            and parent_id = ". (int)$this->parent_id ."
            and id != ". (int)$this->_id .";"
          );

          while ($row = database::fetch($query)) {
            $this->_data['siblings'][$row['id']] = reference::category($row['id'], $this->_language_codes[0]);
          }

          break;

        case 'descendants':

          $this->_data['descendants'] = array();

          $query = database::query(
            "select id from ". DB_TABLE_CATEGORIES ."
            where parent_id = '". (int)$this->_id ."';"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              $this->_data['descendants'][$row['id']] = reference::category($row['id'], $this->_language_codes[0]);
              $this->_data['descendants'] += reference::category($row['id'], $this->_language_codes[0])->descendants;
            }
          }

          break;

        case 'subcategories': // To be deprecated
        case 'children':

          $this->_data['subcategories'] = array();

          $query = database::query(
            "select id from ". DB_TABLE_CATEGORIES ."
            where parent_id = ". (int)$this->_id .";"
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
            where id = ". (int)$this->_id ."
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

      cache::set($this->_cache_token, $this->_data);
    }
  }
