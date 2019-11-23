<?php

  class ref_page {

    private $_id;
    private $_language_codes;
    private $_data = array();

    function __construct($page_id, $language_code=null) {

      if (empty($language_code)) $language_code = language::$selected['code'];

      $this->_id = (int)$page_id;
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
      trigger_error('Setting data ('. $name .') is prohibited', E_USER_ERROR);
    }

    private function _load($field) {

      switch($field) {

        case 'title':
        case 'content':
        case 'head_title':
        case 'meta_description':

          $query = database::query(
            "select * from ". DB_TABLE_PAGES_INFO ."
            where page_id = ". (int)$this->_id ."
            and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
            order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              if (in_array($key, array('id', 'page_id', 'language_code'))) continue;
              if (empty($this->_data[$key])) $this->_data[$key] = $row[$key];
            }
          }

          break;

        case 'parent':

          if (!empty($this->parent_id)) {
            $this->_data['parent'] = reference::page($page['parent_id'], $this->_language_codes[0]);
          }

          break;

        case 'path':

          $this->_data['path'] = array();
          $page_index_id = $this->id;

          $failsafe = 0;
          while (true) {
            $page_query = database::query(
              "select id, parent_id from ". DB_TABLE_PAGES ."
              where id = ". (int)$page_index_id ."
              limit 1;"
            );

            if ($page = database::fetch($page_query)) {
              $this->_data['path'][$page['id']] = reference::page($page['id'], $this->_language_codes[0]);
            }

            if (!empty($page['parent_id'])) {
              $page_index_id = $page['parent_id'];
            } else {
              break;
            }

            if (++$failsafe == 10) trigger_error('Endless loop while building page path', E_USER_ERROR);
          }

          $this->_data['path'] = array_reverse($this->_data['path'], true);

          break;

        case 'siblings':

          $this->_data['siblings'] = array();

          if (empty($this->parent_id)) return;

          $query = database::query(
            "select id from ". DB_TABLE_PAGES ."
            where status
            and parent_id = ". (int)$this->parent_id ."
            and id != ". (int)$this->_id .";"
          );

          while ($row = database::fetch($query)) {
            $this->_data['siblings'][$row['id']] = reference::page($row['id'], $this->_language_codes[0]);
          }

          break;

        case 'descendants':

          $this->_data['descendants'] = array();

          $iterator = function($parent_id, &$iterator) {

            $descendants = array();

            $pages_query = database::query(
              "select id from ". DB_TABLE_PAGES ."
              where parent_id = ". (int)$parent_id .";"
            );

            while ($page = database::fetch($pages_query)) {
              $descendants[$page['id']] = reference::page($page['id'], $this->_language_codes[0]);
              $descendants += $iterator($page['id'], $iterator);
            }

            return $descendants;
          };

          $this->_data['descendants'] = $iterator($this->_id, $iterator);

          break;

        case 'subpages': // To be deprecated
        case 'children':

          $this->_data['subpages'] = array();

            $page_query = database::query(
              "select id, parent_id from ". DB_TABLE_PAGES ."
              where parent_id = ". (int)$this->_id .";"
            );

            while ($page = database::fetch($page_query)) {
              $this->_data['subpages'][$page['id']] = reference::page($page['id'], $this->_language_codes[0]);
            }

          break;

        default:

          $query = database::query(
            "select * from ". DB_TABLE_PAGES ."
            where id = ". (int)$this->_id ."
            limit 1;"
          );

          if (!$row = database::fetch($query)) return;

          foreach ($row as $key => $value) $this->_data[$key] = $value;

          $this->_data['dock'] = explode(',', $this->_data['dock']);

          break;
      }
    }
  }
