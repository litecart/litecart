<?php

  class ctrl_page {
    public $data = array();
    
    public function __construct($page_id=null) {
      global $system;
      
      $this->system = &$system;
      
      if ($page_id !== null) $this->load($page_id);
    }
    
    public function load($page_id) {
      $page_query = $this->system->database->query(
        "select * from ". DB_TABLE_PAGES ."
        where id = '". (int)$page_id ."'
        limit 1;"
      );
      $this->data = $this->system->database->fetch($page_query);
      if (empty($this->data)) trigger_error('Could not find pages ('. $page_id .') in database.', E_USER_ERROR);
      
      $page_info_query = $this->system->database->query(
        "select title, content, head_title, meta_description, meta_keywords, language_code from ". DB_TABLE_PAGES_INFO ."
        where page_id = '". (int)$this->data['id'] ."';"
      );
      while ($page_info = $this->system->database->fetch($page_info_query)) {
        foreach ($page_info as $key => $value) {
          $this->data[$key][$page_info['language_code']] = $value;
        }
      }
    }
    
    public function save() {
    
      if (empty($this->data['id'])) {
        $this->system->database->query(
          "insert into ". DB_TABLE_PAGES ."
          (date_created)
          values ('". $this->system->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $this->system->database->insert_id();
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_PAGES ."
        set dock_menu = '". ((!empty($this->data['dock_menu'])) ? 1 : 0) ."',
          status = '". ((!empty($this->data['status'])) ? 1 : 0) ."',
          dock_support = '". ((!empty($this->data['dock_support'])) ? 1 : 0) ."',
          priority = '". (int)$this->data['priority'] ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      foreach (array_keys($this->system->language->languages) as $language_code) {
        
        $page_info_query = $this->system->database->query(
          "select * from ". DB_TABLE_PAGES_INFO ."
          where page_id = '". (int)$this->data['id'] ."'
          and language_code = '". $language_code ."'
          limit 1;"
        );
        $page_info = $this->system->database->fetch($page_info_query);
        
        if (empty($page_info['id'])) {
          $this->system->database->query(
            "insert into ". DB_TABLE_PAGES_INFO ."
            (page_id, language_code)
            values ('". (int)$this->data['id'] ."', '". $language_code ."');"
          );
          $page_info['id'] = $this->system->database->insert_id();
        }
        
        $this->system->database->query(
          "update ". DB_TABLE_PAGES_INFO ."
          set
            title = '". $this->system->database->input($this->data['title'][$language_code]) ."',
            content = '". $this->system->database->input($this->data['content'][$language_code], true) ."',
            head_title = '". $this->system->database->input($this->data['head_title'][$language_code]) ."',
            meta_description = '". $this->system->database->input($this->data['meta_description'][$language_code]) ."',
            meta_keywords = '". $this->system->database->input($this->data['meta_keywords'][$language_code]) ."'
          where id = '". (int)$page_info['id'] ."'
          and page_id = '". (int)$this->data['id'] ."'
          and language_code = '". $language_code ."'
          limit 1;"
        );
      }
      
      $this->system->cache->set_breakpoint();
    }
    
    public function delete() {
      
      $this->system->database->query(
        "delete from ". DB_TABLE_PAGES_INFO ."
        where page_id = '". (int)$this->data['id'] ."';"
      );
      
      $this->system->database->query(
        "delete from ". DB_TABLE_PAGES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->data['id'] = null;
      
      $this->system->cache->set_breakpoint();
    }
  }

?>