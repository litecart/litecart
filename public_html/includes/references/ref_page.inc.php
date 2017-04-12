<?php

  class ref_page {

    private $_id;
    private $_data = array();

    function __construct($page_id) {

      $this->_id = (int)$page_id;
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
      trigger_error('Setting data ('. $name .') is prohibited', E_USER_ERROR);
    }

    private function _load($field='') {

      switch($field) {

        case 'title':
        case 'content':
        case 'head_title':
        case 'meta_description':

          $query = database::query(
            "select language_code, title, content, meta_description from ". DB_TABLE_PAGES_INFO ."
            where page_id = '". (int)$this->_id ."'
            and language_code in ('". implode("', '", array_keys(language::$languages)) ."');"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) $this->_data[$key][$row['language_code']] = $value;
          }

        // Fix missing translations
            foreach (array_keys(language::$languages) as $language_code) {
            if (empty($this->_data['title'][$language_code])) {
              if (!empty($this->_data['title'][settings::get('default_language_code')])) {
                $this->_data['title'][$language_code] = $this->_data['title'][settings::get('default_language_code')];
              } else {
                $this->_data['title'][$language_code] = '[untitled]';
              }
            }
            foreach (array('content', 'head_title', 'meta_description') as $key) {
              if (empty($this->_data[$key][$language_code])) {
                if (!empty($this->_data[$key][settings::get('default_language_code')])) {
                  $this->_data[$key][$language_code] = $this->_data[$key][settings::get('default_language_code')];
                } else {
                  $this->_data[$key][$language_code] = '';
                }
              }
            }
          }

          break;

        default:

          $query = database::query(
            "select * from ". DB_TABLE_PAGES ."
            where id = '". (int)$this->_id ."'
            limit 1;"
          );
          $row = database::fetch($query);

          if (database::num_rows($query) == 0) return;

          foreach ($row as $key => $value) $this->_data[$key] = $value;

          $this->_data['dock'] = explode(',', $this->_data['dock']);

          break;
      }
    }
  }
