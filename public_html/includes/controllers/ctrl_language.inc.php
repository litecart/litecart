<?php

  class ctrl_language {
    public $data = array();
    
    public function __construct($language_code=null) {
      
      if ($language_code !== null) $this->load($language_code);
    }
    
    public function load($language_code) {
      $language_query = $GLOBALS['system']->database->query(
        "select * from ". DB_TABLE_LANGUAGES ."
        where code='". $GLOBALS['system']->database->input($language_code) ."'
        limit 1;"
      );
      $this->data = $GLOBALS['system']->database->fetch($language_query);
      if (empty($this->data)) trigger_error('Could not find language ('. $language_code .') in database.', E_USER_ERROR);
    }
    
    public function save() {
      
      if (empty($this->data['status']) && $this->data['code'] == $GLOBALS['system']->settings->get('default_language_code')) {
        trigger_error('You cannot disable the default language.', E_USER_ERROR);
        return;
      }
      
      if (!empty($this->data['id'])) {
        $language_query = $GLOBALS['system']->database->query(
          "select * from ". DB_TABLE_LANGUAGES ."
          where id = '". (int)$this->data['id'] ."'
          limit 1;"
        );
        $language = $GLOBALS['system']->database->fetch($language_query);
        if ($this->data['code'] != $language['code']) {
          if ($language['code'] == 'en') {
            trigger_error('You may not rename the english language because it is used for the PHP framework.', E_USER_ERROR);
          } else {
            $GLOBALS['system']->database->query(
              "alter table ". DB_TABLE_TRANSLATIONS ."
              change `text_". $GLOBALS['system']->database->input($language['code']) ."` `text_". $GLOBALS['system']->database->input($this->data['code']) ."` text not null;"
            );
          }
        }
      }
      
      if (empty($this->data['id'])) {
        $GLOBALS['system']->database->query(
          "insert into ". DB_TABLE_LANGUAGES ."
          (date_created)
          values ('". $GLOBALS['system']->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $GLOBALS['system']->database->insert_id();
      }
      
      $translations_query = $GLOBALS['system']->database->query(
        "show fields from ". DB_TABLE_TRANSLATIONS ."
        where `Field` = 'text_". $GLOBALS['system']->database->input($this->data['code']) ."';"
      );
      if ($GLOBALS['system']->database->num_rows($translations_query) == 0) {
        $GLOBALS['system']->database->query(
          "alter table ". DB_TABLE_TRANSLATIONS ."
          add `text_". $GLOBALS['system']->database->input($this->data['code']) ."` text not null after text_en;"
        );
      }
      
      $GLOBALS['system']->database->query(
        "update ". DB_TABLE_LANGUAGES ."
        set
          status = '". (int)$this->data['status'] ."',
          code = '". $GLOBALS['system']->database->input($this->data['code']) ."',
          name = '". $GLOBALS['system']->database->input($this->data['name']) ."',
          charset = '". $GLOBALS['system']->database->input($this->data['charset']) ."',
          locale = '". $GLOBALS['system']->database->input($this->data['locale']) ."',
          raw_date = '". $GLOBALS['system']->database->input($this->data['raw_date']) ."',
          raw_time = '". $GLOBALS['system']->database->input($this->data['raw_time']) ."',
          raw_datetime = '". $GLOBALS['system']->database->input($this->data['raw_datetime']) ."',
          format_date = '". $GLOBALS['system']->database->input($this->data['format_date']) ."',
          format_time = '". $GLOBALS['system']->database->input($this->data['format_time']) ."',
          format_datetime = '". $GLOBALS['system']->database->input($this->data['format_datetime']) ."',
          decimal_point = '". $GLOBALS['system']->database->input($this->data['decimal_point']) ."',
          thousands_sep = '". $GLOBALS['system']->database->input($this->data['thousands_sep']) ."',
          currency_code = '". $GLOBALS['system']->database->input($this->data['currency_code']) ."',
          priority = '". (int)$this->data['priority'] ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $GLOBALS['system']->cache->set_breakpoint();
    }
    
    public function delete() {
      
      if ($this->data['code'] == 'en') {
        trigger_error('English is the PHP framework language and must not be deleted, but it can be disabled.', E_USER_ERROR);
        return;
      }
      
      if ($this->data['code'] == $GLOBALS['system']->settings->get('default_language_code')) {
        trigger_error('Cannot delete the store default language', E_USER_ERROR);
        return;
      }
      
      $GLOBALS['system']->database->query(
        "delete from ". DB_TABLE_LANGUAGES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $translations_query = $GLOBALS['system']->database->query(
        "show fields from ". DB_TABLE_TRANSLATIONS ."
        where `Field` = 'text_". $GLOBALS['system']->database->input($this->data['code']) ."';"
      );
      if ($GLOBALS['system']->database->num_rows($translations_query) == 1) {
        $GLOBALS['system']->database->query(
          "alter table ". DB_TABLE_TRANSLATIONS ."
          drop `text_". $GLOBALS['system']->database->input($this->data['code']) ."`;"
        );
      }
      
      $this->data['id'] = null;
      
      $GLOBALS['system']->cache->set_breakpoint();
    }
  }

?>