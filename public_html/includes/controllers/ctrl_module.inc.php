<?php

  class ctrl_module {
    private $_type;
    private $_module;
    public $name;
    public $description;
    public $settings;
    
    public function __construct($file) {
      global $system;
      
      $this->system = &$system;
      
      preg_match('/.*'. str_replace('/', '\/', ltrim(WS_DIR_MODULES, '/')) .'(.*)\/(.*).inc.php/', $file, $matches);
      $this->type = $matches[1];
      $module_id = $matches[2];
      
      $this->_module = new $module_id;
      
      $this->name = $this->_module->name;
      $this->description = $this->_module->description;
      $this->settings = array();
      
    // Get settings from database
      $settings = unserialize($this->system->settings->get($this->type.'_module_'.$module_id, ''));
      
    // Set settings to module
      foreach ($this->_module->settings() as $setting) {
        $this->settings[$setting['key']] = array(
          'key' => $setting['key'],
          'value' => isset($settings[$setting['key']]) ? $settings[$setting['key']] : $setting['default_value'],
          'default_value' => $setting['default_value'],
          'title' => $setting['title'],
          'description' => $setting['description'],
          'function' => $setting['function'],
        );
      }
    }
    
    public function save($values) {
      global $system;
      
      if (empty($this->_module->id)) return false;
      
      if (!in_array($this->_module->id, explode(';', $this->system->settings->get($this->type.'_modules')))) {
        $this->install();
      }
      
      $save_array = array();
      foreach ($this->_module->settings() as $setting) {
        $save_array[$setting['key']] = $values[$setting['key']];
      }
      
      if (!$this->system->settings->get($this->type.'_module_'.$this->_module->id, '')) {
        $this->system->database->query(
          "insert into ". DB_TABLE_SETTINGS ."
          (`key`, date_created)
          values ('". $this->type.'_module_'. $this->_module->id ."', '". date('Y-m-d H:i:s') ."');"
        );
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". $this->system->database->input(serialize($save_array)) ."'
        where `key` = '". $this->system->database->input($this->type.'_module_'. $this->_module->id) ."'
        limit 1;"
      );
      
      $this->system->cache->set_breakpoint();
    }
    
    public function install() {
      global $system;
      
      $this->uninstall();
      
      $this->_module->install();
      
      $installed_modules = explode(';', $this->system->settings->get($this->type.'_modules'));
      $installed_modules[] = $this->_module->id;
      $installed_modules = array_unique($installed_modules);
      
      $this->system->database->query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". $this->system->database->input(implode(';', $installed_modules)) ."'
        where `key` = '". $this->type."_modules'
        limit 1;"
      );
      
      if (method_exists($this->_module, 'after_install')) {
        $this->_module->after_install();
      }
      
      $this->system->cache->set_breakpoint();
    }
    
    public function uninstall() {
      global $system;
      
      if (method_exists($this->_module, 'before_uninstall')) {
        $this->_module->before_uninstall();
      }
      
      $installed_modules = explode(';', $this->system->settings->get($this->type.'_modules'));
      $key = array_search($this->_module->id, $installed_modules);
      if ($key !== false) unset($installed_modules[$key]);
      
      $this->system->database->query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". $this->system->database->input(implode(';', $installed_modules)) ."'
        where `key` = '". $this->type ."_modules'
        limit 1;"
      );
      
      $this->system->database->query(
        "delete from ". DB_TABLE_SETTINGS ."
        where `key` = '". $this->system->database->input($this->type.'_module_'. $this->_module->id) ."';"
      );
      
      $this->system->cache->set_breakpoint();
    }
  }

?>