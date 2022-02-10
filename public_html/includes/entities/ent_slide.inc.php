<?php

  class ent_slide {
    public $data;
    public $previous;

    public function __construct($slide_id=null) {

      if ($slide_id !== null) {
        $this->load($slide_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."slides;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = '';
      }

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."slides_info;"
      );

      $this->data['languages'] = [];

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], ['id', 'slide_id', 'language_code'])) continue;

        $this->data[$field['Field']] = [];
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = '';
        }
      }

      $this->previous = $this->data;
    }

    public function load($slide_id) {

      if (!preg_match('#^[0-9]+$#', $slide_id)) throw new Exception('Invalid slide (ID: '. $slide_id .')');

      $this->reset();

      $slide_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."slides
        where id = ". (int)$slide_id ."
        limit 1;"
      );

      if ($slide = database::fetch($slide_query)) {
        $this->data = array_replace($this->data, array_intersect_key($slide, $this->data));
      } else {
        throw new Exception('Could not find slide (ID: '. (int)$slide_id .') in database.');
      }

      $this->data['languages'] = explode(',', $this->data['languages']);

      $slide_info_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."slides_info
        where slide_id = ". (int)$this->data['id'] .";"
        );

      while ($slide_info = database::fetch($slide_info_query)) {
        foreach ($slide_info as $key => $value) {
          if (in_array($key, ['id', 'slide_id', 'language_code'])) continue;
          $this->data[$key][$slide_info['language_code']] = $value;
        }
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."slides
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."slides
        set
          status = ". (int)$this->data['status'] .",
          languages = '". database::input(implode(',', database::input($this->data['languages']))) ."',
          name = '". database::input($this->data['name']) ."',
          image = '" . database::input($this->data['image']) . "',
          priority = ". (int)$this->data['priority'] .",
          date_valid_from = ". (empty($this->data['date_valid_from']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_from'])) ."'") .",
          date_valid_to = ". (empty($this->data['date_valid_to']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_to'])) ."'") .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $slide_info_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."slides_info
          where slide_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );

        if (!$slide_info = database::fetch($slide_info_query)) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."slides_info
            (slide_id, language_code)
            values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
          );
          $slide_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."slides_info
          set
            caption = '". database::input($this->data['caption'][$language_code], true) ."',
            link = '". database::input($this->data['link'][$language_code]) ."'
          where id = ". (int)$slide_info['id'] ."
          and slide_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }

      $this->previous = $this->data;

      cache::clear_cache('slides');
    }

    public function save_image($file) {

      if (empty($this->data['id'])) {
        $this->save();
      }

      if (!empty($this->data['image'])) {
        if (is_file(FS_DIR_APP . 'images/' . basename($this->data['image']))) {
          unlink(FS_DIR_APP . 'images/' . basename($this->data['image']));
        }
        $this->data['image'] = '';
      }

    // SVG
      if (preg_match('#^<svg#m', file_get_contents($file))) {
        $filename = 'slides/' . functions::general_path_friendly($this->data['id'] .'-'. $this->data['name'], settings::get('store_language_code')) .'.svg';

        if (!file_exists(FS_DIR_APP . 'images/slides/')) mkdir(FS_DIR_APP . 'images/slides/', 0777);
        if (file_exists(FS_DIR_APP . 'images/' . $filename)) unlink(FS_DIR_APP . 'images/' . $filename);

        copy($file, FS_DIR_APP . 'images/' . $filename);

    // Image
      } else {
        $image = new ent_image($file);

        $filename = 'slides/' . functions::general_path_friendly($this->data['id'] .'-'. $this->data['name'], settings::get('store_language_code')) .'.'. $image->type();

        if (!file_exists(FS_DIR_APP . 'images/slides/')) mkdir(FS_DIR_APP . 'images/slides/', 0777);
        if (file_exists(FS_DIR_APP . 'images/' . $filename)) unlink(FS_DIR_APP . 'images/' . $filename);

        $image->write(FS_DIR_APP . 'images/' . $filename);
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."slides
        set image = '" . database::input($filename) . "'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->previous['image'] = $this->data['image'] = $filename;
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_PREFIX ."slides_info
        where slide_id = ". (int)$this->data['id'] .";"
      );

      database::query(
        "delete from ". DB_TABLE_PREFIX ."slides
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      if (!empty($this->data['image']) && file_exists(FS_DIR_APP . 'images/' . $this->data['image'])) {
        unlink(FS_DIR_APP . 'images/' . $this->data['image']);
      }

      $this->reset();

      cache::clear_cache('slides');
    }
  }
