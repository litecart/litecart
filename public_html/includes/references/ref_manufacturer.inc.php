<?php

  class ref_manufacturer {

    private $_id;
    private $_cache_id;
    private $_language_codes;
    private $_data = array();

    function __construct($manufacturer_id, $language_code=null) {

      $this->_id = (int)$manufacturer_id;
      $this->_cache_id = cache::cache_id('manufacturer_'.(int)$manufacturer_id, array('language'));
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
      trigger_error('Setting data is prohibited', E_USER_WARNING);
    }

    private function _load($field='') {

      switch($field) {

        case 'description':
        case 'short_description':
        case 'head_title':
        case 'meta_description':
        case 'h1_title':
        case 'link':

          $this->_data['info'] = array();

          $query = database::query(
            "select * from ". DB_TABLE_MANUFACTURERS_INFO ."
            where manufacturer_id = '". (int)$this->_id ."'
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              if (in_array($key, array('id', 'manufacturer_id', 'language_code'))) continue;
              $this->_data[$key] = $value;
            }
          }

          break;

        default:

          $query = database::query(
            "select * from ". DB_TABLE_MANUFACTURERS ."
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
