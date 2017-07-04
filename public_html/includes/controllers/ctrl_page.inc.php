<?php

  class ctrl_page {
    public $data;

    public function __construct($page_id=null) {

      if ($page_id !== null) {
        $this->load((int)$page_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PAGES .";"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $this->data['dock'] = array();

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_PAGES_INFO .";"
      );

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], array('id', 'page_id', 'language_code'))) continue;

        $this->data[$field['Field']] = array();
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = null;
        }
      }
    }

    public function load($page_id) {

      $this->reset();

      $page_query = database::query(
        "select * from ". DB_TABLE_PAGES ."
        where id = '". (int)$page_id ."'
        limit 1;"
      );

      if ($page = database::fetch($page_query)) {
        $this->data = array_replace($this->data, array_intersect_key($page, $this->data));
      } else {
        trigger_error('Could not find page (ID: '. (int)$page_id .') in database.', E_USER_ERROR);
      }

      $this->data['dock'] = explode(',', $this->data['dock']);

      $page_info_query = database::query(
        "select * from ". DB_TABLE_PAGES_INFO ."
        where page_id = '". (int)$this->data['id'] ."';"
      );

      while ($page_info = database::fetch($page_info_query)) {
        foreach ($page_info as $key => $value) {
          if (in_array($key, array('id', 'page_id', 'language_code'))) continue;
          $this->data[$key][$page_info['language_code']] = $value;
        }
      }
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PAGES ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PAGES ."
        set status = '". ((!empty($this->data['status'])) ? 1 : 0) ."',
          dock = '". ((!empty($this->data['dock'])) ? implode(',', $this->data['dock']) : '') ."',
          priority = '". (int)$this->data['priority'] ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $page_info_query = database::query(
          "select * from ". DB_TABLE_PAGES_INFO ."
          where page_id = '". (int)$this->data['id'] ."'
          and language_code = '". $language_code ."'
          limit 1;"
        );
        $page_info = database::fetch($page_info_query);

        if (empty($page_info['id'])) {
          database::query(
            "insert into ". DB_TABLE_PAGES_INFO ."
            (page_id, language_code)
            values ('". (int)$this->data['id'] ."', '". $language_code ."');"
          );
          $page_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PAGES_INFO ."
          set
            title = '". database::input($this->data['title'][$language_code]) ."',
            content = '". database::input($this->data['content'][$language_code], true) ."',
            head_title = '". database::input($this->data['head_title'][$language_code]) ."',
            meta_description = '". database::input($this->data['meta_description'][$language_code]) ."'
          where id = '". (int)$page_info['id'] ."'
          and page_id = '". (int)$this->data['id'] ."'
          and language_code = '". $language_code ."'
          limit 1;"
        );
      }

      cache::clear_cache('pages');
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_PAGES_INFO ."
        where page_id = '". (int)$this->data['id'] ."';"
      );

      database::query(
        "delete from ". DB_TABLE_PAGES ."
        where id = '". (int)$this->data['id'] ."';"
      );

      $this->data['id'] = null;

      cache::clear_cache('pages');
    }
  }
