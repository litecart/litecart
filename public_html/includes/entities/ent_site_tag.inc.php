<?php

  class ent_site_tag {
    public $data;
    public $previous;

    public function __construct($site_tag_id=null) {

      if ($site_tag_id) {
        $this->load($site_tag_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."site_tags;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field['Type']);
      }

      $this->previous = $this->data;
    }

    public function load($site_tag_id) {

      if (!preg_match('#^[0-9]+$#', $site_tag_id)) {
        throw new Exception('Invalid site tag (ID: '. $site_tag_id .')');
      }

      $this->reset();

      $site_tag_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."site_tags
        where id = ". (int)$site_tag_id ."
        limit 1;"
      );

      if ($site_tag = database::fetch($site_tag_query)) {
        $this->data = array_replace($this->data, array_intersect_key($site_tag, $this->data));
      } else {
        throw new Exception('Could not find site tag (ID: '. (int)$site_tag_id .') in database.');
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."site_tags
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."site_tags set
        status = '". (empty($this->data['status']) ? 0 : 1) ."',
        position = '". database::input($this->data['position']) ."',
        description = '". database::input($this->data['description']) ."',
        content = '". database::input($this->data['content'], true) ."',
        require_consent = ". (!empty($this->data['require_consent']) ? "'". database::input($this->data['require_consent']) ."'" : "null") .",
        priority = ". (int)$this->data['priority'] .",
        date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->previous = $this->data;

      cache::clear_cache('site_tags');
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_PREFIX ."site_tags
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->reset();

      cache::clear_cache('site_tags');
    }
  }
