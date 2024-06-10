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

      database::query(
        "show fields from ". DB_TABLE_PREFIX ."brands;"
      )->each(function($field){
        $this->data[$field['Field']] = database::create_variable($field);
      });

      database::query(
        "show fields from ". DB_TABLE_PREFIX ."brands_info;"
      )->each(function($field) {
        if (in_array($field['Field'], ['id', 'brand_id', 'language_code'])) return;
        $this->data[$field['Field']] = array_fill_keys(array_keys(language::$languages), database::create_variable($field));
      });

      $this->previous = $this->data;
    }

    public function load($brand_id) {

      if (!preg_match('#^[0-9]+$#', $brand_id)) {
        throw new Exception('Invalid brand (ID: '. $brand_id .')');
      }

      $this->reset();

      $brand = database::query(
        "select * from ". DB_TABLE_PREFIX ."brands
        where id = ". (int)$brand_id ."
        limit 1;"
      )->fetch();

      if ($brand) {
        $this->data = array_replace($this->data, array_intersect_key($brand, $this->data));
      } else {
        throw new Exception('Could not find brand (ID: '. (int)$brand_id .') in database.');
      }

      database::query(
        "select * from ". DB_TABLE_PREFIX ."brands_info
        where brand_id = ". (int)$brand_id .";"
      )->each(function($info){
        foreach ($info as $key => $value) {
          if (in_array($key, ['id', 'brand_id', 'language_code'])) continue;
          $this->data[$key][$info['language_code']] = $value;
        }
      });

      $this->previous = $this->data;
    }

    public function save() {

      if (!$this->data['id']) {
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

      database::prepare(
        "update ". DB_TABLE_PREFIX ."brands
        set status = :status,
          featured = :featured,
          code = :code,
          name = :name,
          image = :image,
          keywords = :keywords
        where id = :id
        limit 1;"
      )->bind($this->data)->execute();

      foreach (array_keys(language::$languages) as $language_code) {

        $brand_info = database::query(
          "select * from ". DB_TABLE_PREFIX ."brands_info
          where brand_id = :id
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        , $this->data)->fetch_all();

        if (!$brand_info) {
          database::prepare(
            "insert into ". DB_TABLE_PREFIX ."brands_info
            (brand_id, language_code)
            values (:id, '". database::input($language_code) ."');"
          )->bind($this->data)->execute();
        }

        database::prepare(
          "update ". DB_TABLE_PREFIX ."brands_info
          set short_description = :short_description[$language_code],
            description = :description[$language_code],
            head_title = :head_title[$language_code],
            h1_title = :h1_title[$language_code],
            meta_description = :meta_description[$language_code],
            link = :link[$language_code]
          where brand_id = :id
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        )->bind($this->data, ['language_code' => $language_code])->execute();
      }

      $this->previous = $this->data;

      cache::clear_cache('brands');
    }

    public function save_image($file) {

      if (empty($file)) return;

      if (!$this->data['id']) {
        $this->save();
      }

      if (!is_dir('storage://images/brands/')) {
        mkdir('storage://images/brands/', 0777);
      }

      $image = new ent_image($file);

    // 456-12345_Fancy-title.jpg
      $filename = 'brands/' . $this->data['id'] .'-'. functions::format_path_friendly($this->data['name'], settings::get('store_language_code')) .'.'. $image->type;

      if (is_file('storage://images/' . $this->data['image'])) {
        unlink('storage://images/' . $this->data['image']);
      }

      functions::image_delete_cache('storage://images/' . $filename);

      if (settings::get('image_downsample_size')) {
        list($width, $height) = preg_split('#\s*,\s*#', settings::get('image_downsample_size'), -1, PREG_SPLIT_NO_EMPTY);
        $image->resample($width, $height, 'FIT_ONLY_BIGGER');
      }

      $image->write('storage://images/' . $filename, 90);

      database::prepare(
        "update ". DB_TABLE_PREFIX ."brands
        set image = '". database::input($filename) ."'
        where id = :id;"
      )->bind($this->data)->execute();

      $this->previous['image'] = $this->data['image'] = $filename;
    }

    public function delete_image() {

      if (!$this->data['id']) return;

      if (is_file('storage://images/' . $this->data['image'])) {
        unlink('storage://images/' . $this->data['image']);
      }

      functions::image_delete_cache('storage://images/' . $this->data['image']);

      database::prepare(
        "update ". DB_TABLE_PREFIX ."brands
        set image = ''
        where id = :id;"
      )->bind($this->data)->execute();

      $this->previous['image'] = $this->data['image'] = '';
    }

    public function delete() {

      if (!$this->data['id']) return;

      $products_query = database::prepare(
        "select id from ". DB_TABLE_PREFIX ."products
        where brand_id = :id
        limit 1;"
      )->bind($this->data)->execute();

      if (database::num_rows($products_query)) {
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
        where b.id = :id;"
      , $this->data);
      //)->bind($this->data)->execute();

      $this->reset();

      cache::clear_cache('brands');
    }
  }
