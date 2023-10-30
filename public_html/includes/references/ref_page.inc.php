<?php

  class ref_page {

    private $_language_codes;
    private $_data = [];

    function __construct($page_id, $language_code=null) {

      if (empty($language_code)) {
        $language_code = language::$selected['code'];
      }

      $this->_data['id'] = (int)$page_id;
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
      trigger_error('Setting data ('. $name .') is prohibited', E_USER_ERROR);
    }

    private function _load($field) {

      switch($field) {

        case 'title':
        case 'content':
        case 'head_title':
        case 'meta_description':

          $query = database::query(
            "select * from ". DB_TABLE_PREFIX ."pages_info
            where page_id = ". (int)$this->_data['id'] ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {

              if (in_array($key, ['id', 'page_id', 'language_code'])) continue;

              if (empty($this->_data[$key])) {
                $this->_data[$key] = $row[$key];
              }
            }
          }

          break;

        case 'parent':

          if (!empty($this->parent_id)) {
            $this->_data['parent'] = reference::page($this->parent_id, $this->_language_codes[0]);
          }

          break;

        case 'path':

          $this->_data['path'] = [$this->id => $this];

          $current = $this;
          while ($current->parent_id) {

            $this->_data['path'][$current->parent_id] = $current->parent;
            $current = $current->parent;
          }

          $this->_data['path'] = array_reverse($this->_data['path'], true);

          break;

        case 'siblings':

          $this->_data['siblings'] = [];

          if (empty($this->parent_id)) return;

          $query = database::query(
            "select id from ". DB_TABLE_PREFIX ."pages
            where status
            and parent_id = ". (int)$this->parent_id ."
            and id != ". (int)$this->_data['id'] .";"
          );

          while ($row = database::fetch($query)) {
            $this->_data['siblings'][$row['id']] = reference::page($row['id'], $this->_language_codes[0]);
          }

          break;

        case 'descendants':

          $this->_data['descendants'] = [];

          $iterator = function($parent_id) use (&$iterator) {

            $descendants = [];

            $pages_query = database::query(
              "select id from ". DB_TABLE_PREFIX ."pages
              where parent_id = ". (int)$parent_id .";"
            );

            while ($page = database::fetch($pages_query)) {
              $descendants[$page['id']] = reference::page($page['id'], $this->_language_codes[0]);
              $descendants += $iterator($page['id']);
            }

            return $descendants;
          };

          $this->_data['descendants'] = $iterator($this->_data['id']);

          break;

        case 'subpages': // To be deprecated
        case 'children':

          $this->_data['subpages'] = [];

            $page_query = database::query(
              "select id, parent_id from ". DB_TABLE_PREFIX ."pages
              where parent_id = ". (int)$this->_data['id'] .";"
            );

            while ($page = database::fetch($page_query)) {
              $this->_data['subpages'][$page['id']] = reference::page($page['id'], $this->_language_codes[0]);
            }

          break;

        default:

          $page = database::query(
            "select * from ". DB_TABLE_PREFIX ."pages
            where id = ". (int)$this->_data['id'] ."
            limit 1;"
          )->fetch();

          if (!$page) return;

          foreach ($page as $key => $value) {
            $this->_data[$key] = $value;
          }

          $this->_data['dock'] = preg_split('#\s*,\s*#', $this->_data['dock'], -1, PREG_SPLIT_NO_EMPTY);

          break;
      }
    }
  }
