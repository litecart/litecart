<?php

  class ctrl_designer {
    public $data = array();
    
    public function __construct($designer_id='') {
      global $system;
      
      $this->system = &$system;
      
      if (!empty($designer_id)) {
        $this->load($designer_id);
      } else {
        $this->reset();
      }
    }
    
    public function reset() {
      
      $this->data = array();
      
      $designer_query = $this->system->database->query(
        "show fields from ". DB_TABLE_DESIGNERS .";"
      );
      while ($field = $this->system->database->fetch($designer_query)) {
        $this->data[$field['Field']] = '';
      }
      
      $designer_info_query = $this->system->database->query(
        "show fields from ". DB_TABLE_DESIGNERS_INFO .";"
      );
      while ($field = $this->system->database->fetch($designer_info_query)) {
        if (in_array($field['Field'], array('id', 'designer_id', 'language_code'))) continue;
        $this->data[$field['Field']] = array();
        foreach (array_keys($this->system->language->languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = '';
        }
      }
      
      $this->data['status'] = '1';
    }
    
    public function load($designer_id) {
      $designers_query = $this->system->database->query(
        "select * from ". DB_TABLE_DESIGNERS ."
        where id='". (int)$designer_id ."'
        limit 1;"
      );
      $this->data = $this->system->database->fetch($designers_query);
      
      $designers_info_query = $this->system->database->query(
        "select * from ". DB_TABLE_DESIGNERS_INFO ."
        where designer_id = '". (int)$designer_id ."';"
      );
      while ($designer_info = $this->system->database->fetch($designers_info_query)) {
        foreach ($designer_info as $key => $value) {
          if (in_array($key, array('id', 'designer_id', 'language_code'))) continue;
          $this->data[$key][$designer_info['language_code']] = $value;
        }
      }
    }
    
    public function save() {
    
      if (empty($this->data['id'])) {
        $this->system->database->query(
          "insert into ". DB_TABLE_DESIGNERS ."
          (date_created)
          values ('". $this->system->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $this->system->database->insert_id();
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_DESIGNERS ." set
        status = '". (int)$this->data['status'] ."',
        code = '". $this->system->database->input($this->data['code']) ."',
        name = '". $this->system->database->input($this->data['name']) ."',
        keywords = '". $this->system->database->input($this->data['keywords']) ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      foreach (array_keys($this->system->language->languages) as $language_code) {
        
        $designers_info_query = $this->system->database->query(
          "select * from ". DB_TABLE_DESIGNERS_INFO ."
          where designer_id = '". (int)$this->data['id'] ."'
          and language_code = '". $this->system->database->input($language_code) ."'
          limit 1;"
        );
        $designer_info = $this->system->database->fetch($designers_info_query);
        
        if (empty($designer_info)) {
          $this->system->database->query(
            "insert into ". DB_TABLE_DESIGNERS_INFO ."
            (designer_id, language_code)
            values ('". (int)$this->data['id'] ."', '". $language_code ."');"
          );
        }
        
        $this->system->database->query(
          "update ". DB_TABLE_DESIGNERS_INFO ." set
          short_description = '". $this->system->database->input($this->data['short_description'][$language_code]) ."',
          description = '". $this->system->database->input($this->data['description'][$language_code], true) ."',
          head_title = '". $this->system->database->input($this->data['head_title'][$language_code]) ."',
          h1_title = '". $this->system->database->input($this->data['h1_title'][$language_code]) ."',
          meta_description = '". $this->system->database->input($this->data['meta_description'][$language_code]) ."',
          meta_keywords = '". $this->system->database->input($this->data['meta_keywords'][$language_code]) ."',
          link = '". $this->system->database->input($this->data['link'][$language_code]) ."'
          where designer_id = '". (int)$this->data['id'] ."'
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
        where designer_id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      if ($this->system->database->num_rows($products_query) > 0) {
        $this->system->notices->add('errors', $this->system->language->translate('error_delete_designer_not_empty_products', 'The designer could not be deleted because there are products linked to it.'));
        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
      }
    
      if (!empty($this->data['image']) && is_file(FS_DIR_HTTP_ROOT . WS_DIR_DATA_IMAGES . 'designers/' . $this->data['image'])) {
        unlink(FS_DIR_HTTP_ROOT . WS_DIR_DATA_IMAGES . 'designers/' . $this->data['image']);
      }
      
      $this->system->database->query(
        "delete from ". DB_TABLE_DESIGNERS ."
        where id = '". $this->data['id'] ."'
        limit 1;"
      );
      
      $this->system->database->query(
        "delete from ". DB_TABLE_DESIGNERS_INFO ."
        where designer_id = '". $this->data['id'] ."'
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
      
      if (!is_dir(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'designers/')) mkdir(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'designers/', 0777);
      
      $image = new ctrl_image($file);
      
    // 456-12345_Fancy-title.jpg
      $filename = 'designers/' . $this->data['id'] .'-'. $this->system->functions->general_url_friendly($this->data['name']) .'.'. $image->type();
      
      if (is_file(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['image'])) unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['image']);
      
      $this->system->functions->image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename);
      
      $image->resample(1024, 1024, 'FIT_ONLY_BIGGER');
      
      $image->write(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename, '', 90);
      
      $this->system->database->query(
        "update ". DB_TABLE_DESIGNERS ."
        set image = '". $this->system->database->input($filename) ."'
        where id = '". (int)$this->data['id'] ."';"
      );
      
      $this->data['image'] = $filename;
    }
  }

?>