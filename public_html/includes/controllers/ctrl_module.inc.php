<?php

  class ctrl_module {
    private $_type;
    private $_module;
    public $name;
    public $description;
    public $settings;

    public function __construct($file) {

      preg_match('/.*'. str_replace('/', '\/', ltrim(WS_DIR_MODULES, '/')) .'(.*)\/(.*).inc.php/', $file, $matches);
      $this->type = $matches[1];
      $module_id = $matches[2];

      $this->_module = new $module_id;

      $this->name = $this->_module->name;
      $this->description = $this->_module->description;
      $this->author = $this->_module->author;
      $this->website = $this->_module->website;
      $this->settings = array();

    // Get settings from database
      $settings = unserialize(settings::get($module_id, ''));

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

      if (empty($this->_module->id)) return false;

      if (!in_array($this->_module->id, explode(';', settings::get($this->type.'_modules')))) {
        $this->install();
      } else {
        $this->update();
      }

      $settings = array();
      foreach ($this->_module->settings() as $setting) {
        $settings[$setting['key']] = $values[$setting['key']];
      }

      if (!settings::get($this->_module->id, '')) {
        database::query(
          "insert into ". DB_TABLE_SETTINGS ."
          (`key`, date_created)
          values ('". database::input($this->_module->id) ."', '". date('Y-m-d H:i:s') ."');"
        );
      }

      database::query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". database::input(serialize($settings), true) ."'
        where `key` = '". database::input($this->_module->id) ."'
        limit 1;"
      );

      cache::clear_cache('modules');
    }

    public function install() {

      if (method_exists($this->_module, 'uninstall')) {
        $this->_module->uninstall();
      }

      $installed_modules = explode(';', settings::get($this->type.'_modules'));
      $installed_modules[] = $this->_module->id;
      $installed_modules = array_unique($installed_modules);

      database::query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". database::input(implode(';', $installed_modules)) ."'
        where `key` = '". database::input($this->type.'_modules') ."'
        limit 1;"
      );

      if (method_exists($this->_module, 'install')) {
        $this->_module->install();
      }

      cache::clear_cache('modules');
    }

    public function update() {

      if (method_exists($this->_module, 'update')) {
        $this->_module->update();
      }

      cache::clear_cache('modules');
    }

    public function uninstall() {

      if (method_exists($this->_module, 'uninstall')) {
        $this->_module->uninstall();
      }

      $installed_modules = explode(';', settings::get($this->type.'_modules'));
      $key = array_search($this->_module->id, $installed_modules);
      if ($key !== false) unset($installed_modules[$key]);

      database::query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". database::input(implode(';', $installed_modules)) ."'
        where `key` = '". database::input($this->type.'_modules') ."'
        limit 1;"
      );

      database::query(
        "delete from ". DB_TABLE_SETTINGS ."
        where `key` = '". database::input($this->_module->id) ."';"
      );

      cache::clear_cache('modules');
    }
  }

?>