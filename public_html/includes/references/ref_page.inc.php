<?php

  class ref_page {

    private $_id;
    private $_language_codes;
    private $_data = array();

    function __construct($page_id, $language_code=null) {

      $this->_id = (int)$page_id;
      $this->_language_codes = array_unique(array(
        !empty($language_code) ? $language_code : language::$selected['code'],
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

            $page = database::fetch($page_query);

            if ($page) {
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

        case 'descendants':

          $this->data['descendants'] = array();

          if (!defined('custom_page_descendants')) {
            function custom_page_descendants($parent_id) {
              $descendants = array();
              $pages_query = database::query(
                "select id from ". DB_TABLE_PAGES ."
                where parent_id = ". (int)$parent_id .";"
              );
              while ($page = database::fetch($pages_query)) {
                $descendants[$page['id']] = reference::page($page['id'], $this->_language_codes[0]);
                $descendants += custom_page_descendants($page['id']);
              }
              return $descendants;
            }
          }

          $this->data['descendants'] = custom_page_descendants($this->_id);

          break;

        case 'subpages':

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
