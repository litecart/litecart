<?php

  class ent_page {
    public $data;
    public $previous;

    public function __construct($page_id=null) {

      if ($page_id !== null) {
        $this->load($page_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."pages;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = '';
      }

      $this->data['dock'] = [];

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."pages_info;"
      );

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], ['id', 'page_id', 'language_code'])) continue;

        $this->data[$field['Field']] = [];
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = '';
        }
      }

      $this->previous = $this->data;
    }

    public function load($page_id) {

      if (!preg_match('#^[0-9]+$#', $page_id)) throw new Exception('Invalid page (ID: '. $page_id .')');

      $this->reset();

      $page_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."pages
        where id = ". (int)$page_id ."
        limit 1;"
      );

      if ($page = database::fetch($page_query)) {
        $this->data = array_replace($this->data, array_intersect_key($page, $this->data));
      } else {
        throw new Exception('Could not find page (ID: '. (int)$page_id .') in database.');
      }

      $this->data['dock'] = explode(',', $this->data['dock']);

      $page_info_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."pages_info
        where page_id = ". (int)$this->data['id'] .";"
      );

      while ($page_info = database::fetch($page_info_query)) {
        foreach ($page_info as $key => $value) {
          if (in_array($key, ['id', 'page_id', 'language_code'])) continue;
          $this->data[$key][$page_info['language_code']] = $value;
        }
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (!empty($this->data['parent_id']) && $this->data['parent_id'] == $this->data['id']) {
        throw new Exception(language::translate('error_cannot_attach_page_to_itself', 'You cannot attach a page to itself'));
      }

      if (!empty($this->data['id']) && !empty($this->data['parent_id']) && in_array($this->data['parent_id'], array_keys(reference::page($this->data['id'])->descendants))) {
        throw new Exception(language::translate('error_cannot_attach_page_to_descendant', 'You cannot attach a page to a descendant'));
      }

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."pages
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."pages
        set status = ". (int)$this->data['status'] .",
          parent_id = ". (int)$this->data['parent_id'] .",
          dock = '". (!empty($this->data['dock']) ? implode(',', database::input($this->data['dock'])) : '') ."',
          priority = ". (int)$this->data['priority'] .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $page_info_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."pages_info
          where page_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );

        if (!$page_info = database::fetch($page_info_query)) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."pages_info
            (page_id, language_code)
            values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
          );
          $page_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."pages_info
          set
            title = '". database::input($this->data['title'][$language_code]) ."',
            content = '". database::input($this->data['content'][$language_code], true) ."',
            head_title = '". database::input($this->data['head_title'][$language_code]) ."',
            meta_description = '". database::input($this->data['meta_description'][$language_code]) ."'
          where id = ". (int)$page_info['id'] ."
          and page_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }

      $this->previous = $this->data;

      cache::clear_cache('pages');
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_PREFIX ."pages_info
        where page_id = ". (int)$this->data['id'] .";"
      );

      database::query(
        "delete from ". DB_TABLE_PREFIX ."pages
        where id = ". (int)$this->data['id'] .";"
      );

      database::query(
        "update ". DB_TABLE_PREFIX ."pages
        set parent_id = ". (int)$this->data['parent_id'] ."
        where parent_id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('pages');
    }
  }
