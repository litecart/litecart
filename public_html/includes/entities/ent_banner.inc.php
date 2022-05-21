<?php

  class ent_banner {
    public $data = [];

    public function __construct($banner_id=null) {

      if (!empty($banner_id)) {
        $this->load((int)$banner_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $categories_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."banners;"
      );

      while ($field = database::fetch($categories_query)) {
        $this->data[$field['Field']] = database::create_variable($field['Type']);
      }
    }

    public function load($banner_id) {

      $this->reset();

      $banner = database::fetch(database::query(
        "select * from ". DB_TABLE_PREFIX ."banners
        where id = ". (int)$banner_id ."
        limit 1;"
      ));

      if ($banner) {
        $this->data = array_replace($this->data, array_intersect_key($banner, $this->data));
      } else {
        trigger_error('Could not find banner (ID: '. (int)$banner_id .') in database.', E_USER_ERROR);
      }

      $this->data['languages'] = preg_split('#\s*,\s*#', $this->data['keywords'], -1, PREG_SPLIT_NO_EMPTY);
    }

    public function save() {

      if (!empty($this->data['id'])) {
        $banners_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."banners
          where id = '". (int)$this->data['id'] ."'
          limit 1;"
        );
        $banner = database::fetch($banners_query);
      }

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."banners
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."banners
        set
          status = '". (int)$this->data['status'] ."',
          name = '". database::input($this->data['name']) ."',
          languages = '". database::input(implode(',', database::input($this->data['languages']))) ."',
          link = '". database::input($this->data['link']) ."',
          ". (!empty($this->data['image']) ? "image = '" . database::input($this->data['image']) . "'," : '') ."
          html = '". database::input($this->data['html'], true) ."',
          keywords = '". database::input(implode(',', preg_split('#\s*,\s*#', $this->data['keywords'], -1, PREG_SPLIT_NO_EMPTY))) ."',
          date_valid_from = '". database::input($this->data['date_valid_from']) ."',
          date_valid_to = '". database::input($this->data['date_valid_to']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      cache::clear_cache('banners');
    }

    public function save_image($file) {

      if (!empty($this->data['image'])) {
        if (is_file(FS_DIR_STORAGE . 'images/' . basename($this->data['image']))) {
          unlink(FS_DIR_STORAGE . 'images/' . basename($this->data['image']));
        }
        $this->data['image'] = '';
      }

      if (empty($this->data['id'])) {
        $this->save();
      }

      $image = new ent_image($file);

      $filename = 'banners/' . functions::general_path_friendly($this->data['id'] .'-'. $this->data['name']) .'.'. $image->type();

      if (!file_exists(FS_DIR_STORAGE . 'images/banners/')) mkdir(FS_DIR_STORAGE . 'images/banners/', 0777);
      if (file_exists(FS_DIR_STORAGE . 'images/' . $filename)) unlink(FS_DIR_STORAGE . 'images/' . $filename);

      $image->write(FS_DIR_STORAGE . 'images/' . $filename, $image->type());

      $this->data['image'] = $filename;
      $this->save();
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_PREFIX ."banners
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      if (file_exists(FS_DIR_STORAGE . 'images/' . $this->data['image'])) unlink(FS_DIR_STORAGE . 'images/' . $this->data['image']);

      $this->data['id'] = 0;

      cache::clear_cache('banners');
    }
  }
