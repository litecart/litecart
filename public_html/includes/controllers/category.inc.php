<?php

  class ctrl_category {
    public $data = array();
    
    public function __construct($category_id='') {
      global $system;
      
      $this->system = &$system;
      
      if (!empty($category_id)) {
        $this->load($category_id);
      } else {
        $this->reset();
      }
    }
    
    public function reset() {
      
      $this->data = array();
      
      $categories_query = $this->system->database->query(
        "show fields from ". DB_TABLE_CATEGORIES .";"
      );
      while ($field = $this->system->database->fetch($categories_query)) {
        $this->data[$field['Field']] = '';
      }
      
      $categories_info_query = $this->system->database->query(
        "show fields from ". DB_TABLE_CATEGORIES_INFO .";"
      );
      
      while ($field = $this->system->database->fetch($categories_info_query)) {
        if (in_array($field['Field'], array('id', 'category_id', 'language_code'))) continue;
        $this->data[$field['Field']] = array();
        foreach (array_keys($this->system->language->languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = '';
        }
      }
    }
    
    public function load($category_id) {
      
      $this->reset();
      
      $categories_query = $this->system->database->query(
        "select * from ". DB_TABLE_CATEGORIES ."
        where id='". (int)$category_id ."'
        limit 1;"
      );
      $this->data = $this->system->database->fetch($categories_query);
      
      $categories_info_query = $this->system->database->query(
        "select name, short_description, description, head_title, h1_title, meta_description, meta_keywords, language_code from ". DB_TABLE_CATEGORIES_INFO ."
        where category_id = '". (int)$category_id ."';"
      );
      while ($category_info = $this->system->database->fetch($categories_info_query)) {
        foreach ($category_info as $key => $value) {
          $this->data[$key][$category_info['language_code']] = $value;
        }
      }
    }
    
    public function save() {
    
      if (empty($this->data['id'])) {
        $this->system->database->query(
          "insert into ". DB_TABLE_CATEGORIES ."
          (date_created)
          values ('". $this->system->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $this->system->database->insert_id();
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_CATEGORIES ."
        set parent_id = '". (int)$this->data['parent_id'] ."',
          status = '". (int)$this->data['status'] ."',
          code = '". $this->system->database->input($this->data['code']) ."',
          keywords = '". $this->system->database->input($this->data['keywords']) ."',
          priority = '". (int)$this->data['priority'] ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      foreach (array_keys($this->system->language->languages) as $language_code) {
        
        $categories_info_query = $this->system->database->query(
          "select * from ". DB_TABLE_CATEGORIES_INFO ."
          where category_id = '". (int)$this->data['id'] ."'
          and language_code = '". $this->system->database->input($language_code) ."'
          limit 1;"
        );
        $category_info = $this->system->database->fetch($categories_info_query);
        
        if (empty($category_info)) {
          $this->system->database->query(
            "insert into ". DB_TABLE_CATEGORIES_INFO ."
            (category_id, language_code)
            values ('". (int)$this->data['id'] ."', '". $language_code ."');"
          );
        }
        
        $this->system->database->query(
          "update ". DB_TABLE_CATEGORIES_INFO ." set
          name = '". $this->system->database->input($this->data['name'][$language_code]) ."',
          short_description = '". $this->system->database->input($this->data['short_description'][$language_code]) ."',
          description = '". $this->system->database->input($this->data['description'][$language_code], true) ."',
          head_title = '". $this->system->database->input($this->data['head_title'][$language_code]) ."',
          h1_title = '". $this->system->database->input($this->data['h1_title'][$language_code]) ."',
          meta_description = '". $this->system->database->input($this->data['meta_description'][$language_code]) ."',
          meta_keywords = '". $this->system->database->input($this->data['meta_keywords'][$language_code]) ."'
          where category_id = '". (int)$this->data['id'] ."'
          and language_code = '". $this->system->database->input($language_code) ."'
          limit 1;"
        );
      }
      
      $this->system->cache->set_breakpoint();
    }
    
    public function delete() {
    
      if (empty($this->data['id'])) return;
      
      $products_query = $this->system->database->query(
        "select id from ". DB_TABLE_PRODUCTS ."
        where find_in_set('". (int)$this->data['id'] ."', categories)
        limit 1;"
      );
      
      if ($this->system->database->num_rows($products_query) > 0) {
        $this->system->notices->add('errors', $this->system->language->translate('error_delete_category_not_empty_products', 'The category could not be deleted because there are products linked to it.'));
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }
      
      $subcategories_query = $this->system->database->query(
        "select id from ". DB_TABLE_CATEGORIES ."
        where parent_id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      if ($this->system->database->num_rows($subcategories_query) > 0) {
        $this->system->notices->add('errors', $this->system->language->translate('error_delete_category_not_empty_subcategories', 'The category could not be deleted because there are subcategories linked to it.'));
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }
    
      if (!empty($this->data['image']) && is_file(FS_DIR_HTTP_ROOT . WS_DIR_DATA_IMAGES . 'categories/' . $this->data['image'])) {
        unlink(FS_DIR_HTTP_ROOT . WS_DIR_DATA_IMAGES . 'categories/' . $this->data['image']);
      }
      
      $this->system->database->query(
        "delete from ". DB_TABLE_CATEGORIES ."
        where id = '". $this->data['id'] ."'
        limit 1;"
      );
      
      $this->system->database->query(
        "delete from ". DB_TABLE_CATEGORIES_INFO ."
        where category_id = '". $this->data['id'] ."'
        limit 1;"
      );
      
      $this->data['id'] = null;
      
      $this->system->cache->set_breakpoint();
    }
    
    public function save_image($file) {
      
      if (empty($file)) return;
      
      if (empty($this->data['id'])) {
        $this->save();
      }
      
      if (!is_dir(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'categories/')) mkdir(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'categories/', 0777);
      
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . 'image.inc.php');
      $image = new ctrl_image($file);
      
    // 456-12345_Fancy-title.jpg
      $filename = 'categories/' . $this->data['id'] .'-'. $this->system->functions->general_url_friendly($this->data['name'][$this->system->settings->get('store_language_code')]) .'.'. $image->type();
      
      if (is_file(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['image'])) unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['image']);
      
      $this->system->functions->image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename);
      
      $image->resample(1024, 1024, 'FIT_ONLY_BIGGER');
      
      $image->write(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename, '', 90);
      
      $this->system->database->query(
        "update ". DB_TABLE_CATEGORIES ."
        set image = '". $this->system->database->input($filename) ."'
        where id = '". (int)$this->data['id'] ."';"
      );
      
      $this->data['image'] = $filename;
    }
  }

?>