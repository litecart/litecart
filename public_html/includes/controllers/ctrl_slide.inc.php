<?php

  class ctrl_slide {
    public $data;

    public function __construct($slide_id=null) {

      if ($slide_id !== null) {
        $this->load((int)$slide_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_SLIDES .";"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_SLIDES_INFO .";"
      );

      $this->data['languages'] = array();

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], array('id', 'slide_id', 'language_code'))) continue;

        $this->data[$field['Field']] = array();
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = null;
        }
      }
    }

    public function load($slide_id) {

      $this->reset();

      $slide_query = database::query(
        "select * from ". DB_TABLE_SLIDES ."
        where id = '". (int)$slide_id ."'
        limit 1;"
      );

      if ($slide = database::fetch($slide_query)) {
        $this->data = array_replace($this->data, array_intersect_key($slide, $this->data));
      } else {
        trigger_error('Could not find slide (ID: '. (int)$slide_id .') in database.', E_USER_ERROR);
      }

      $this->data['languages'] = explode(',', $this->data['languages']);

      $slide_info_query = database::query(
        "select * from ". DB_TABLE_SLIDES_INFO ."
        where slide_id = '". (int)$this->data['id'] ."';"
        );

      while ($slide_info = database::fetch($slide_info_query)) {
        foreach ($slide_info as $key => $value) {
          if (in_array($key, array('id', 'slide_id', 'language_code'))) continue;
          $this->data[$key][$slide_info['language_code']] = $value;
        }
      }
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_SLIDES ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_SLIDES ."
        set
          status = '". (int)$this->data['status'] ."',
          languages = '". database::input(implode(',', database::input($this->data['languages']))) ."',
          name = '". database::input($this->data['name']) ."',
          image = '" . database::input($this->data['image']) . "',
          priority = '". (int)$this->data['priority'] ."',
          date_valid_from = '". database::input($this->data['date_valid_from']) ."',
          date_valid_to = '". database::input($this->data['date_valid_to']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $slide_info_query = database::query(
          "select * from ". DB_TABLE_SLIDES_INFO ."
          where slide_id = '". (int)$this->data['id'] ."'
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
        $slide_info = database::fetch($slide_info_query);

        if (empty($slide_info['id'])) {
          database::query(
            "insert into ". DB_TABLE_SLIDES_INFO ."
            (slide_id, language_code)
            values ('". (int)$this->data['id'] ."', '". $language_code ."');"
          );
          $slide_info['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_SLIDES_INFO ."
          set
            caption = '". database::input($this->data['caption'][$language_code], true) ."',
            link = '". database::input($this->data['link'][$language_code]) ."'
          where id = '". (int)$slide_info['id'] ."'
          and slide_id = '". (int)$this->data['id'] ."'
          and language_code = '". $language_code ."'
          limit 1;"
        );
      }

      cache::clear_cache('slides');
    }

    public function save_image($file) {

      if (!empty($this->data['image'])) {
        if (is_file(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . basename($this->data['image']))) {
          unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . basename($this->data['image']));
        }
        $this->data['image'] = '';
      }

      if (empty($this->data['id'])) {
        $this->save();
      }

      $image = new ctrl_image($file);

      $filename = 'slides/' . functions::general_path_friendly($this->data['id'] .'-'. $this->data['name'], settings::get('store_language_code')) .'.'. $image->type();

      if (!file_exists(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'slides/')) mkdir(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'slides/', 0777);
      if (file_exists(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename)) unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename);

      $image->write(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename);

      $this->data['image'] = $filename;
      $this->save();
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_SLIDES_INFO ."
        where slide_id = '". (int)$this->data['id'] ."';"
      );

      database::query(
        "delete from ". DB_TABLE_SLIDES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      if (!empty($this->data['image']) && file_exists(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['image'])) unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['image']);

      cache::clear_cache('slides');

      $this->data['id'] = null;
    }
  }
