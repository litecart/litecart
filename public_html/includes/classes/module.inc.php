<?php

  class module {
    public $type;
    public $modules;

    public function set_type($type) {
      $this->type = $type;
    }

    public function load($module_id='') {

      if (empty($module_id)) {
        $load_modules = explode(';', settings::get($this->type.'_modules'));
        if (empty($load_modules)) return;
      } else {
        $load_modules = array($module_id);
      }

      foreach ($load_modules as $module_id) {

      // Uninstall orphan modules
        if (!is_file(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . $this->type . '/' . $module_id .'.inc.php')) {

          $installed_modules = explode(';', settings::get($this->type.'_modules'));

          $key = array_search($module_id, $installed_modules);
          if ($key !== false) unset($installed_modules[$key]);

          database::query(
            "update ". DB_TABLE_SETTINGS ."
            set value = '". database::input(implode(';', $installed_modules)) ."'
            where `key` = '". $this->type ."_modules'
            limit 1;"
          );

          database::query(
            "delete from ". DB_TABLE_SETTINGS ."
            where `key` = '". database::input($this->type.'_module_'. $module_id) ."';"
          );

          continue;
        }

        $module = new $module_id;

      // Get settings from database
        $settings = array();
        if (settings::get($module_id)) {
          $settings = unserialize(settings::get($module_id));
        }

      // Set settings to module
        $module->settings = array();
        foreach ($module->settings() as $setting) {
          $module->settings[$setting['key']] = isset($settings[$setting['key']]) ? $settings[$setting['key']] : $setting['default_value'];
        }

        $module->status = isset($module->settings['status']) && in_array(strtolower($module->settings['status']), array('1', 'active', 'enabled', 'on', 'true', 'yes')) ? 1 : 0;
        $module->priority = isset($module->settings[$setting['key']]) ? (int)$module->settings[$setting['key']] : 0;

        $this->modules[$module->id] = $module;
      }

      if (!empty($this->modules)) $this->sort();
    }

    private function sort() {
      if (!function_exists('custom_sort_modules')) {
        function custom_sort_modules($a, $b) {
          if ((int)$a->priority == (int)$b->priority) {
            return ($a->name < $b->name) ? 1 : -1;
          } else if ((int)$a->priority > (int)$b->priority) {
            return 1;
          } else {
            return -1;
          }
        }
      }
      uasort($this->modules, 'custom_sort_modules');
    }
  }

?>