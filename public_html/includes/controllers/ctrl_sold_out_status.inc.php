<?php

  class ctrl_sold_out_status {
    public $data;

    public function __construct($sold_out_status_id=null) {

      if ($sold_out_status_id !== null) {
        $this->load((int)$sold_out_status_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $sold_out_status_query = database::query(
        "show fields from ". DB_TABLE_SOLD_OUT_STATUSES .";"
      );
      while ($field = database::fetch($sold_out_status_query)) {
        $this->data[$field['Field']] = null;
      }

      $sold_out_status_info_query = database::query(
        "show fields from ". DB_TABLE_SOLD_OUT_STATUSES_INFO .";"
      );

      while ($field = database::fetch($sold_out_status_info_query)) {
        if (in_array($field['Field'], array('id', 'sold_out_status_id', 'language_code'))) continue;

        $this->data[$field['Field']] = array();
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = null;
        }
      }
    }

    public function load($sold_out_status_id) {

      $this->reset();

      $sold_out_status_query = database::query(
        "select * from ". DB_TABLE_SOLD_OUT_STATUSES ."
        where id = '". (int)$sold_out_status_id ."'
        limit 1;"
      );

      if ($sold_out_status = database::fetch($sold_out_status_query)) {
        $this->data = array_replace($this->data, array_intersect_key($sold_out_status, $this->data));
      } else {
        trigger_error('Could not find sold out status (ID: '. (int)$sold_out_status_id .') in database.', E_USER_ERROR);
      }

      $sold_out_status_info_query = database::query(
        "select * from ". DB_TABLE_SOLD_OUT_STATUSES_INFO ."
        where sold_out_status_id = '". (int)$this->data['id'] ."';"
      );

      while ($sold_out_status_info = database::fetch($sold_out_status_info_query)) {
        foreach ($sold_out_status_info as $key => $value) {
          if (in_array($key, array('id', 'sold_out_status_id', 'language_code'))) continue;
          $this->data[$key][$sold_out_status_info['language_code']] = $value;
        }
      }
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_SOLD_OUT_STATUSES ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_SOLD_OUT_STATUSES ."
        set orderable = '". (empty($this->data['orderable']) ? 0 : 1) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $sold_out_status_info_query = database::query(
          "select * from ". DB_TABLE_SOLD_OUT_STATUSES_INFO ."
          where sold_out_status_id = '". (int)$this->data['id'] ."'
          and language_code = '". $language_code ."'
          limit 1;"
        );
        $sold_out_status_info = database::fetch($sold_out_status_info_query);

        if (empty($sold_out_status_info['id'])) {
          database::query(
            "insert into ". DB_TABLE_SOLD_OUT_STATUSES_INFO ."
            (sold_out_status_id, language_code)
            values ('". (int)$this->data['id'] ."', '". $language_code ."');"
          );
          $sold_out_status_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_SOLD_OUT_STATUSES_INFO ."
          set
            name = '". database::input($this->data['name'][$language_code]) ."',
            description = '". database::input($this->data['description'][$language_code]) ."'
          where id = '". (int)$sold_out_status_info['id'] ."'
          and sold_out_status_id = '". (int)$this->data['id'] ."'
          and language_code = '". $language_code ."'
          limit 1;"
        );
      }

      cache::clear_cache('sold_out_statuses');
    }

    public function delete() {

      if (database::num_rows(database::query("select id from ". DB_TABLE_PRODUCTS ." where sold_out_status_id = '". (int)$this->data['id'] ."' limit 1;"))) {
        trigger_error('Cannot delete the sold out status because there are products using it', E_USER_ERROR);
        return;
      }

      database::query(
        "delete from ". DB_TABLE_SOLD_OUT_STATUSES_INFO ."
        where sold_out_status_id = '". (int)$this->data['id'] ."';"
      );

      database::query(
        "delete from ". DB_TABLE_SOLD_OUT_STATUSES ."
        where id = '". (int)$this->data['id'] ."';"
      );

      $this->data['id'] = null;

      cache::clear_cache('sold_out_statuses');
    }
  }
