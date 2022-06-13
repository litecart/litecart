<?php

  class ent_brand {
    public $data;
    public $previous;

    public function __construct($brand_id='') {

      if (!empty($brand_id)) {
        $this->load($brand_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $brand_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."brands;"
      );

      while ($field = database::fetch($brand_query)) {
        $this->data[$field['Field']] = database::create_variable($field['Type']);
      }

      $brand_info_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."brands_info;"
      );
      while ($field = database::fetch($brand_info_query)) {
        if (in_array($field['Field'], ['id', 'brand_id', 'language_code'])) continue;

        $this->data[$field['Field']] = [];
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = database::create_variable($field['Type']);
        }
      }

      $this->previous = $this->data;
    }

    public function load($brand_id) {

      if (!preg_match('#^[0-9]+$#', $brand_id)) throw new Exception('Invalid brand (ID: '. $brand_id .')');

      $this->reset();

      $brand = database::fetch(database::query(
        "select * from ". DB_TABLE_PREFIX ."brands
        where id = ". (int)$brand_id ."
        limit 1;"
      ));

      if ($brand) {
        $this->data = array_replace($this->data, array_intersect_key($brand, $this->data));
      } else {
        throw new Exception('Could not find brand (ID: '. (int)$brand_id .') in database.');
      }

      $brands_info_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."brands_info
        where brand_id = ". (int)$brand_id .";"
      );

      while ($brand_info = database::fetch($brands_info_query)) {
        foreach ($brand_info as $key => $value) {
          if (in_array($key, ['id', 'brand_id', 'language_code'])) continue;
          $this->data[$key][$brand_info['language_code']] = $value;
        }
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."brands
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      $this->data['keywords'] = preg_split('#\s*,\s*#', $this->data['keywords'], -1, PREG_SPLIT_NO_EMPTY);
      $this->data['keywords'] = array_unique($this->data['keywords']);
      $this->data['keywords'] = implode(',', $this->data['keywords']);

      database::query(
        "update ". DB_TABLE_PREFIX ."brands
        set status = ". (int)$this->data['status'] .",
          featured = ". (int)$this->data['featured'] .",
          code = '". database::input($this->data['code']) ."',
          name = '". database::input($this->data['name']) ."',
          image = '". database::input($this->data['image']) ."',
          keywords = '". database::input($this->data['keywords']) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $brands_info_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."brands_info
          where brand_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );

        if (!$brand_info = database::fetch($brands_info_query)) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."brands_info
            (brand_id, language_code)
            values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
          );
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."brands_info
          set short_description = '". database::input($this->data['short_description'][$language_code]) ."',
            description = '". database::input($this->data['description'][$language_code], true) ."',
            head_title = '". database::input($this->data['head_title'][$language_code]) ."',
            h1_title = '". database::input($this->data['h1_title'][$language_code]) ."',
            meta_description = '". database::input($this->data['meta_description'][$language_code]) ."',
            link = '". database::input($this->data['link'][$language_code]) ."'
          where brand_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }

      $this->previous = $this->data;

      cache::clear_cache('brands');
    }

    public function save_image($file) {

      if (empty($file)) return;

      if (empty($this->data['id'])) {
        $this->save();
      }

      if (!is_dir('storage://images/brands/')) mkdir('storage://images/brands/', 0777);

      $image = new ent_image($file);

    // 456-12345_Fancy-title.jpg
      $filename = 'brands/' . $this->data['id'] .'-'. functions::format_path_friendly($this->data['name'], settings::get('site_language_code')) .'.'. $image->type();

      if (is_file('storage://images/' . $this->data['image'])) unlink('storage://images/' . $this->data['image']);

      functions::image_delete_cache('storage://images/' . $filename);

      if (settings::get('image_downsample_size')) {
        list($width, $height) = preg_split('#\s*,\s*#', settings::get('image_downsample_size'), -1, PREG_SPLIT_NO_EMPTY);
        $image->resample($width, $height, 'FIT_ONLY_BIGGER');
      }

      $image->write('storage://images/' . $filename, 90);

      database::query(
        "update ". DB_TABLE_PREFIX ."brands
        set image = '". database::input($filename) ."'
        where id = ". (int)$this->data['id'] .";"
      );

      $this->previous['image'] = $this->data['image'] = $filename;
    }

    public function delete_image() {

      if (empty($this->data['id'])) return;

      if (is_file('storage://images/' . $this->data['image'])) unlink('storage://images/' . $this->data['image']);

      functions::image_delete_cache('storage://images/' . $this->data['image']);

      database::query(
        "update ". DB_TABLE_PREFIX ."brands
        set image = ''
        where id = ". (int)$this->data['id'] .";"
      );

      $this->previous['image'] = $this->data['image'] = '';
    }

    public function delete() {

      if (empty($this->data['id'])) return;

      $products_query = database::query(
        "select id from ". DB_TABLE_PREFIX ."products
        where brand_id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      if (database::num_rows($products_query) > 0) {
        notices::add('errors', language::translate('error_delete_brand_not_empty_products', 'The brand could not be deleted because there are products linked to it.'));
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }

      if (!empty($this->data['image']) && is_file('storage://images/brands/' . $this->data['image'])) {
        unlink('storage://images/brands/' . $this->data['image']);
      }

      database::query(
        "delete b, bi
        from ". DB_TABLE_PREFIX ."brands b
        left join ". DB_TABLE_PREFIX ."brands_info bi on (bi.brand_id = b.id)
        where b.id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('brands');
    }
  }
