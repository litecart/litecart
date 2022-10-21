<?php

  class ent_banner {
    public $data;
    public $previous;

    public function __construct($banner_id=null) {

      if (!empty($banner_id)) {
        $this->load((int)$banner_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $banners_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."banners;"
      );

      while ($field = database::fetch($banners_query)) {
        $this->data[$field['Field']] = database::create_variable($field);
      }

      $this->previous = $this->data;
    }

    public function load($banner_id) {

      if (!preg_match('#^[0-9]+$#', $banner_id)) {
        throw new Exception('Invalid banner (ID: '. $banner_id .')');
      }

      $this->reset();

      $banner = database::query(
        "select * from ". DB_TABLE_PREFIX ."banners
        where id = ". (int)$banner_id ."
        limit 1;"
      )->fetch();

      if ($banner) {
        $this->data = array_replace($this->data, array_intersect_key($banner, $this->data));
      } else {
        throw new Exception('Could not find banner (ID: '. (int)$banner_id .') in database.');
      }

      $this->data['languages'] = preg_split('#\s*,\s*#', $this->data['keywords'], -1, PREG_SPLIT_NO_EMPTY);
      $this->data['keywords'] = preg_split('#\s*,\s*#', $this->data['keywords'], -1, PREG_SPLIT_NO_EMPTY);
    }

    public function save() {

      $this->data['keywords'] = preg_split('#\s*,\s*#', $this->data['keywords'], -1, PREG_SPLIT_NO_EMPTY);
      $this->data['keywords'] = array_map('trim', $this->data['keywords']);
      $this->data['keywords'] = array_filter($this->data['keywords']);
      $this->data['keywords'] = array_unique($this->data['keywords']);
      $this->data['keywords'] = implode(',', $this->data['keywords']);

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
          languages = '". implode(',', database::input($this->data['languages'])) ."',
          link = '". database::input($this->data['link']) ."',
          ". (!empty($this->data['image']) ? "image = '" . database::input($this->data['image']) . "'," : '') ."
          html = '". database::input($this->data['html'], true) ."',
          keywords = '". database::input($this->data['keywords']) ."',
          date_valid_from = ". (!empty($this->data['date_valid_from']) ? "'". database::input($this->data['date_valid_from']) ."'" : "null") .",
          date_valid_to = ". (!empty($this->data['date_valid_to']) ? "'". database::input($this->data['date_valid_to']) ."'" : "null") .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      $this->previous = $this->data;

      cache::clear_cache('banners');
    }

    public function save_image($file) {

      if (!empty($this->data['image'])) {
        if (is_file('storage://images/' . basename($this->data['image']))) {
          unlink('storage://images/' . basename($this->data['image']));
        }
        $this->data['image'] = '';
      }

      if (empty($this->data['id'])) {
        $this->save();
      }

      $image = new ent_image($file);

      $filename = 'banners/' . functions::format_path_friendly($this->data['id'] .'-'. $this->data['name']) .'.'. $image->type();

      if (!file_exists('storage://images/banners/')) mkdir('storage://images/banners/', 0777);
      if (file_exists('storage://images/' . $filename)) unlink('storage://images/' . $filename);

      $image->write('storage://images/' . $filename, $image->type());

      $this->data['image'] = $filename;
      $this->save();
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_PREFIX ."banners
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      if (file_exists('storage://images/' . $this->data['image'])) unlink('storage://images/' . $this->data['image']);

      $this->reset();

      cache::clear_cache('banners');
    }
  }
