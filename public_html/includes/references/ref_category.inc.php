<?php

  class ref_category {

    private $_id;
    private $_cache_id;
    private $_language_codes;
    private $_data = array();

    function __construct($category_id, $language_code=null) {

      $this->_id = (int)$category_id;
      $this->_cache_id = cache::cache_id('category_'.(int)$category_id);
      $this->_language_codes = array_unique(array(
        !empty($language_code) ? $language_code : language::$selected['code'],
        settings::get('default_language_code'),
        settings::get('store_language_code'),
      ));

      if ($cache = cache::get($this->_cache_id, 'file')) {
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

    private function _load($field='') {

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
            where category_id = '". (int)$this->_id ."'
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

          $this->_data['parent'] = reference::category($this->parent_id);

          break;

        case 'products':

          $this->_data['products'] = array();

          $query = database::query(
            "select id from ". DB_TABLE_PRODUCTS ."
            where status
            and find_in_set ('". database::input($this->_id) ."', categories);"
          );

          while ($row = database::fetch($query)) {
            $this->_data['products'][$row['id']] = reference::product($row['id']);
          }

          break;

        case 'siblings':

          $this->_data['siblings'] = array();

          if (empty($this->parent_id)) return;

          $query = database::query(
            "select id from ". DB_TABLE_CATEGORIES ."
            where status
            and parent_id = '". (int)$this->parent_id ."'
            and id != '". database::input($this->_id) ."';"
          );

          while($row = database::fetch($query)) {
            $this->_data['siblings'][$row['id']] = reference::category($row['id']);
          }

          break;

        case 'descendants':
        case 'subcategories':

          $this->_data['subcategories'] = array();

          $query = database::query(
            "select id from ". DB_TABLE_CATEGORIES ."
            where parent_id = '". (int)$this->_id ."';"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              $this->_data['subcategories'][$row['id']] = reference::category($row['id']);
            }
          }

          break;

        default:

          $query = database::query(
            "select * from ". DB_TABLE_CATEGORIES ."
            where id = '". (int)$this->_id ."'
            limit 1;"
          );

          $row = database::fetch($query);

          if (database::num_rows($query) == 0) return;

          foreach ($row as $key => $value) {
            switch($key) {
              case 'keywords':
                $row[$key] = explode(',', $row[$key]);
                break;

              default:
                $this->_data[$key] = $value;
                break;
            }
          }

          break;
      }

      cache::set($this->_cache_id, 'file', $this->_data);
    }
  }
