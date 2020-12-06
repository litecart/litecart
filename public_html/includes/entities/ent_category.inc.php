<?php

  class ent_category {
    public $data;
    public $previous;

    public function __construct($category_id=null) {

      if (!empty($category_id)) {
        $this->load($category_id);
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
        $this->data[$field['Field']] = null;
      }

      $categories_info_query = database::query(
        "show fields from ". DB_TABLE_CATEGORIES_INFO .";"
      );

      while ($field = database::fetch($categories_info_query)) {
        if (in_array($field['Field'], array('id', 'category_id', 'language_code'))) continue;

        $this->data[$field['Field']] = array();
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = null;
        }
      }

      $this->data['filters'] = array();

      $this->previous = $this->data;
    }

    public function load($category_id) {

      if (!preg_match('#^[0-9]+$#', $category_id)) throw new Exception('Invalid category (ID: '. $category_id .')');

      $this->reset();

      $categories_query = database::query(
        "select * from ". DB_TABLE_CATEGORIES ."
        where id=". (int)$category_id ."
        limit 1;"
      );

      if ($category = database::fetch($categories_query)) {
        $this->data = array_replace($this->data, array_intersect_key($category, $this->data));
      } else {
        throw new Exception('Could not find category (ID: '. (int)$category_id .') in database.');
      }

      $categories_info_query = database::query(
        "select * from ". DB_TABLE_CATEGORIES_INFO ."
        where category_id = ". (int)$category_id .";"
      );

      while ($category_info = database::fetch($categories_info_query)) {
        foreach ($category_info as $key => $value) {
          if (in_array($key, array('id', 'category_id', 'language_code'))) continue;
          $this->data[$key][$category_info['language_code']] = $value;
        }
      }

    // Filters
      $category_filters_query = database::query(
        "select cf.*, agi.name as attribute_group_name from ". DB_TABLE_CATEGORIES_FILTERS ." cf
        left join ". DB_TABLE_ATTRIBUTE_GROUPS_INFO ." agi on (agi.group_id = cf.attribute_group_id and language_code = '". database::input(language::$selected['code']) ."')
        where category_id = ". (int)$this->data['id'] ."
        order by priority;"
      );

      $this->data['filters'] = array();
      while ($group = database::fetch($category_filters_query)) {
        $this->data['filters'][] = $group;
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (!empty($this->data['id']) && $this->data['parent_id'] == $this->data['id']) {
        throw new Exception(language::translate('error_cannot_attach_category_to_self', 'Cannot attach category to itself'));
      }

      if (!empty($this->data['id']) && !empty($this->data['parent_id']) && in_array($this->data['parent_id'], array_keys(reference::category($this->data['id'])->descendants))) {
        throw new Exception(language::translate('error_cannot_attach_category_to_descendant', 'You cannot attach a category to a descendant'));
      }

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_CATEGORIES ."
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      if ($this->data['parent_id'] == $this->data['id']) $this->data['parent_id'] = null;

      $this->data['keywords'] = explode(',', $this->data['keywords']);
      $this->data['keywords'] = array_map('trim', $this->data['keywords']);
      $this->data['keywords'] = array_unique($this->data['keywords']);
      $this->data['keywords'] = implode(',', $this->data['keywords']);

      database::query(
        "update ". DB_TABLE_CATEGORIES ."
        set parent_id = ". (int)$this->data['parent_id'] .",
          status = ". (int)$this->data['status'] .",
          code = '". database::input($this->data['code']) ."',
          google_taxonomy_id = ". (int)$this->data['google_taxonomy_id'] .",
          list_style = '". database::input($this->data['list_style']) ."',
          keywords = '". database::input($this->data['keywords']) ."',
          priority = ". (int)$this->data['priority'] .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        $categories_info_query = database::query(
          "select * from ". DB_TABLE_CATEGORIES_INFO ."
          where category_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );

        if (!$category_info = database::fetch($categories_info_query)) {
          database::query(
            "insert into ". DB_TABLE_CATEGORIES_INFO ."
            (category_id, language_code)
            values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
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

    // Delete filters
      database::query(
        "delete from ". DB_TABLE_CATEGORIES_FILTERS ."
        where category_id = ". (int)$this->data['id'] ."
        and id not in ('". implode("', '", array_column($this->data['filters'], 'id')) ."');"
      );

    // Update filters
      if (!empty($this->data['filters'])) {
        $filter_priority = 1;
        foreach (array_keys($this->data['filters']) as $key) {
          if (empty($this->data['filters'][$key]['id'])) {
            database::query(
              "insert into ". DB_TABLE_CATEGORIES_FILTERS ."
              (category_id, attribute_group_id)
              values (". (int)$this->data['id'] .", ". (int)$this->data['filters'][$key]['attribute_group_id'] .");"
            );
            $this->data['filters'][$key]['id'] = database::insert_id();
          }

          database::query(
            "update ". DB_TABLE_CATEGORIES_FILTERS ." set
              attribute_group_id = '". database::input($this->data['filters'][$key]['attribute_group_id']) ."',
              select_multiple = ". (!empty($this->data['filters'][$key]['select_multiple']) ? 1 : 0) .",
              priority = ". $filter_priority++ ."
            where category_id = ". (int)$this->data['id'] ."
            and id = ". (int)$this->data['filters'][$key]['id'] ."
            limit 1;"
          );
        }
      }

      $this->previous = $this->data;

      cache::clear_cache('category_tree');
      cache::clear_cache('categories');
    }

    public function save_image($file, $filename='') {

      if (empty($file)) return;

      if (!empty($filename)) $filename = 'categories/' . $filename;

      if (empty($this->data['id'])) {
        $this->save();
      }

      if (!is_dir(FS_DIR_APP . 'images/categories/')) mkdir(FS_DIR_APP . 'images/categories/', 0777);

      if (!$image = new ent_image($file)) return false;

    // 456-Fancy-category-title-N.jpg
      if (empty($filename)) {
        $filename = 'categories/' . $this->data['id'] .'-'. functions::general_path_friendly($this->data['name'][settings::get('store_language_code')], settings::get('store_language_code')) .'.'. $image->type();
      }

      if (is_file(FS_DIR_APP . 'images/' . $filename)) unlink(FS_DIR_APP . 'images/' . $filename);

      if (settings::get('image_downsample_size')) {
        list($width, $height) = explode(',', settings::get('image_downsample_size'));
        $image->resample($width, $height, 'FIT_ONLY_BIGGER');
      }

      if (!$image->write(FS_DIR_APP . 'images/' . $filename, 90)) return false;

      functions::image_delete_cache(FS_DIR_APP . 'images/' . $filename);

      database::query(
        "update ". DB_TABLE_CATEGORIES ."
        set image = '". database::input($filename) ."'
        where id = ". (int)$this->data['id'] .";"
      );

      $this->previous['image'] = $this->data['image'] = $filename;
    }

    public function delete_image() {

      if (empty($this->data['image'])) return;

      if (is_file(FS_DIR_APP . 'images/' . $this->data['image'])) unlink(FS_DIR_APP . 'images/' . $this->data['image']);

      functions::image_delete_cache(FS_DIR_APP . 'images/' . $this->data['image']);

      database::query(
        "update ". DB_TABLE_CATEGORIES ."
        set image = ''
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

     $this->previous['image'] = $this->data['image'] = '';
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
        where parent_id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      if (database::num_rows($subcategories_query) > 0) {
        notices::add('errors', language::translate('error_delete_category_not_empty_subcategories', 'The category could not be deleted because there are subcategories linked to it.'));
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }

      $this->data['filters'] = array();

      $this->save();

      database::query(
        "delete from ". DB_TABLE_CATEGORIES ."
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      database::query(
        "delete from ". DB_TABLE_CATEGORIES_INFO ."
        where category_id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('category_tree');
      cache::clear_cache('categories');
    }
  }
