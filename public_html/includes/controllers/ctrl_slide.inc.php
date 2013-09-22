<?php

  class ctrl_slide {
    public $data = array();
    
    public function __construct($slide_id=null) {
      
      if ($slide_id !== null) $this->load($slide_id);
    }
    
    public function load($slide_id) {
      $slide_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_SLIDES ."
        where id = '". (int)$slide_id ."'
        limit 1;"
      );
      $this->data = $GLOBALS['system']->database->fetch($slide_query);
      if (empty($this->data)) trigger_error('Could not find slide ('. $slide_id .') in database.', E_USER_ERROR);
    }
    
    public function save() {
    
      if (!empty($this->data['id'])) {
        $slides_query = $GLOBALS['system']->database->query(
          "select * from ". DB_TABLE_SLIDES ."
          where id = '". (int)$this->data['id'] ."'
          limit 1;"
        );
        $slide = $GLOBALS['system']->database->fetch($slides_query);
      }
      
      if (empty($this->data['id'])) {
        $GLOBALS['system']->database->query(
          "insert into ". DB_TABLE_SLIDES ."
          (date_created)
          values ('". $GLOBALS['system']->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $GLOBALS['system']->database->insert_id();
      }
      
      $GLOBALS['system']->database->query(
        "update ". DB_TABLE_SLIDES ."
        set
          status = '". (int)$this->data['status'] ."',
          language_code = '". $GLOBALS['system']->database->input($this->data['language_code']) ."',
          name = '". $GLOBALS['system']->database->input($this->data['name']) ."',
          caption = '". $GLOBALS['system']->database->input($this->data['caption'], true) ."',
          link = '". $GLOBALS['system']->database->input($this->data['link']) ."',
          ". (!empty($this->data['image']) ? "image = '" . $GLOBALS['system']->database->input($this->data['image']) . "'," : '') ."
          priority = '". (int)$this->data['priority'] ."',
          date_valid_from = '". $GLOBALS['system']->database->input($this->data['date_valid_from']) ."',
          date_valid_to = '". $GLOBALS['system']->database->input($this->data['date_valid_to']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $GLOBALS['system']->cache->set_breakpoint();
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
      
      $filename = 'slides/' . $GLOBALS['system']->functions->general_path_friendly($this->data['id'] .'-'. $this->data['name']) .'.'. $image->type();
      
      if (!file_exists(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'slides/')) mkdir(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES .'slides/', 0777);
      if (file_exists(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename)) unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename);
      
      $image->write(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename);
      
      $this->data['image'] = $filename;
      $this->save();
    }
    
    public function delete() {
      
      $GLOBALS['system']->database->query(
        "delete from ". DB_TABLE_SLIDES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      if (!empty($this->data['image']) && file_exists(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['image'])) unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['image']);
      
      $this->data['id'] = null;
      
      $GLOBALS['system']->cache->set_breakpoint();
    }
  }

?>