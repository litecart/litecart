<?php

  class ctrl_category {
    public $data = array();

    public function __construct($category_id=null) {

      if (!empty($category_id)) {
        $this->load((int)$category_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $categories_query = database::query(
        "show fields from ". DB_TABLE_CATEGORIES .";"
      );
      while ($field = database::fetch($categories_query)) {
        $this->data[$field['Field']] = '';
      }

      $categories_info_query = database::query(
        "show fields from ". DB_TABLE_CATEGORIES_INFO .";"
      );

      while ($field = database::fetch($categories_info_query)) {
        if (in_array($field['Field'], array('id', 'category_id', 'language_code'))) continue;
        $this->data[$field['Field']] = array();
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = '';
        }
      }
    }

    public function load($category_id) {

      $this->reset();

      $categories_query = database::query(
        "select * from ". DB_TABLE_CATEGORIES ."
        where id='". (int)$category_id ."'
        limit 1;"
      );
      $this->data = database::fetch($categories_query);
      if (empty($this->data)) trigger_error('Could not find category (ID: '. (int)$category_id .') in database.', E_USER_ERROR);

      $categories_info_query = database::query(
        "select name, short_description, description, head_title, h1_title, meta_description, language_code from ". DB_TABLE_CATEGORIES_INFO ."
        where category_id = '". (int)$category_id ."';"
      );
      while ($category_info = database::fetch($categories_info_query)) {
        foreach ($category_info as $key => $value) {
          $this->data[$key][$category_info['language_code']] = $value;
        }
      }
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_CATEGORIES ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_CATEGORIES ."
        set parent_id = '". (int)$this->data['parent_id'] ."',
          status = '". (int)$this->data['status'] ."',
          code = '". database::input($this->data['code']) ."',
          google_taxonomy_id = '". (int)$this->data['google_taxonomy_id'] ."',
          dock = '". database::input(@implode(',', $this->data['dock'])) ."',
          list_style = '". database::input($this->data['list_style']) ."',
          keywords = '". database::input($this->data['keywords']) ."',
          priority = '". (int)$this->data['priority'] ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $categories_info_query = database::query(
          "select * from ". DB_TABLE_CATEGORIES_INFO ."
          where category_id = '". (int)$this->data['id'] ."'
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
        $category_info = database::fetch($categories_info_query);

        if (empty($category_info)) {
          database::query(
            "insert into ". DB_TABLE_CATEGORIES_INFO ."
            (category_id, language_code)
            values ('". (int)$this->data['id'] ."', '". $language_code ."');"
          );
        }

        database::query(
          "update ". DB_TABLE_CATEGORIES_INFO ." set
          name = '". (!empty($this->data['name'][$language_code]) ? database::input($this->data['name'][$language_code]) : '') ."',
          short_description = '". (!empty($this->data['name'][$language_code]) ? database::input($this->data['short_description'][$language_code]) : '') ."',
          description = '". (!empty($this->data['name'][$language_code]) ? database::input($this->data['description'][$language_code], true) : '') ."',
          head_title = '". (!empty($this->data['name'][$language_code]) ? database::input($this->data['head_title'][$language_code]) : '') ."',
          h1_title = '". (!empty($this->data['name'][$language_code]) ? database::input($this->data['h1_title'][$language_code]) : '') ."',
          meta_description = '". (!empty($this->data['name'][$language_code]) ? database::input($this->data['meta_description'][$language_code]) : '') ."'
          where category_id = '". (!empty($this->data['name'][$language_code]) ? (int)$this->data['id'] : '') ."'
          and language_code = '". (!empty($this->data['name'][$language_code]) ? database::input($language_code) : '') ."'
          limit 1;"
        );
      }

      cache::clear_cache('category_tree');
      cache::clear_cache('categories');
      cache::clear_cache('category_'. (int)$this->data['id']);
    }

    public function delete() {

      if (empty($this->data['id'])) return;

      $products_query = database::query(
        "select product_id from ". DB_TABLE_PRODUCTS_TO_CATEGORIES ."
        where category_id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      if (database::num_rows($products_query) > 0) {
        notices::add('errors', language::translate('error_delete_category_not_empty_products', 'The category could not be deleted because there are products linked to it.'));
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }

      $subcategories_query = database::query(
        "select id from ". DB_TABLE_CATEGORIES ."
        where parent_id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      if (database::num_rows($subcategories_query) > 0) {
        notices::add('errors', language::translate('error_delete_category_not_empty_subcategories', 'The category could not be deleted because there are subcategories linked to it.'));
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }

      if (!empty($this->data['image']) && is_file(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'categories/' . $this->data['image'])) {
        unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'categories/' . $this->data['image']);
      }

      database::query(
        "delete from ". DB_TABLE_CATEGORIES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      database::query(
        "delete from ". DB_TABLE_CATEGORIES_INFO ."
        where category_id = '". (int)$this->data['id'] ."';"
      );

      cache::clear_cache('category_tree');
      cache::clear_cache('categories');
      cache::clear_cache('category_'. (int)$this->data['id']);

      $this->data['id'] = null;
    }

    public function save_image($file) {

      if (empty($file)) return;

      if (empty($this->data['id'])) {
        $this->save();
      }

      if (!is_dir(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'categories/')) mkdir(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'categories/', 0777);

      $image = new ctrl_image($file);

    // 456-12345_Fancy-title.jpg
      $filename = 'categories/' . $this->data['id'] .'-'. functions::general_path_friendly($this->data['name'][settings::get('store_language_code')], settings::get('store_language_code')) .'.'. $image->type();

      if (is_file(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['image'])) unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['image']);

      functions::image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename);

      if (settings::get('image_downsample_size')) {
        list($width, $height) = explode(',', settings::get('image_downsample_size'));
        $image->resample($width, $height, 'FIT_ONLY_BIGGER');
      }

      $image->write(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename, '', 90);

      database::query(
        "update ". DB_TABLE_CATEGORIES ."
        set image = '". database::input($filename) ."'
        where id = '". (int)$this->data['id'] ."';"
      );

      $this->data['image'] = $filename;
    }
  }

?>