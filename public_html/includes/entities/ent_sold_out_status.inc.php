<?php

  class ent_sold_out_status {
    public $data;
    public $previous;

    public function __construct($sold_out_status_id=null) {

      if (!empty($sold_out_status_id)) {
        $this->load($sold_out_status_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $sold_out_status_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."sold_out_statuses;"
      );

      while ($field = database::fetch($sold_out_status_query)) {
        $this->data[$field['Field']] = database::create_variable($field['Type']);
      }

      $sold_out_status_info_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."sold_out_statuses_info;"
      );

      while ($field = database::fetch($sold_out_status_info_query)) {
        if (in_array($field['Field'], ['id', 'sold_out_status_id', 'language_code'])) continue;

        $this->data[$field['Field']] = [];
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = database::create_variable($field['Type']);
        }
      }

      $this->previous = $this->data;
    }

    public function load($sold_out_status_id) {

      if (!preg_match('#^[0-9]+$#', $sold_out_status_id)) throw new Exception('Invalid sold out status (ID: '. $sold_out_status_id .')');

      $this->reset();

      $sold_out_status_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."sold_out_statuses
        where id = ". (int)$sold_out_status_id ."
        limit 1;"
      );

      if ($sold_out_status = database::fetch($sold_out_status_query)) {
        $this->data = array_replace($this->data, array_intersect_key($sold_out_status, $this->data));
      } else {
        throw new Exception('Could not find sold out status (ID: '. (int)$sold_out_status_id .') in database.');
      }

      $sold_out_status_info_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."sold_out_statuses_info
        where sold_out_status_id = ". (int)$this->data['id'] .";"
      );

      while ($sold_out_status_info = database::fetch($sold_out_status_info_query)) {
        foreach ($sold_out_status_info as $key => $value) {
          if (in_array($key, ['id', 'sold_out_status_id', 'language_code'])) continue;
          $this->data[$key][$sold_out_status_info['language_code']] = $value;
        }
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."sold_out_statuses
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."sold_out_statuses
        set orderable = ". (int)$this->data['orderable'] .",
          hidden = ". (int)$this->data['hidden'] .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $sold_out_status_info_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."sold_out_statuses_info
          where sold_out_status_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );

        if (!$sold_out_status_info = database::fetch($sold_out_status_info_query)) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."sold_out_statuses_info
            (sold_out_status_id, language_code)
            values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
          );
          $sold_out_status_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."sold_out_statuses_info
          set name = '". database::input($this->data['name'][$language_code]) ."',
            description = '". database::input($this->data['description'][$language_code]) ."'
          where id = ". (int)$sold_out_status_info['id'] ."
          and sold_out_status_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }

      $this->previous = $this->data;

      cache::clear_cache('sold_out_statuses');
    }

    public function delete() {

      if (database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."products where sold_out_status_id = ". (int)$this->data['id'] ." limit 1;"))) {
        throw new Exception('Cannot delete the sold out status because there are products using it');
      }

      database::query(
        "delete ss, ssi
        from ". DB_TABLE_PREFIX ."sold_out_statuses ss
        left join ". DB_TABLE_PREFIX ."sold_out_statuses_info ssi on (ssi.sold_out_status_id = si.id)
        where ss.id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('sold_out_statuses');
    }
  }
