<?php

  abstract class abs_modules {
    public $modules;

    public function reset() {
      $this->modules = [];
    }

    public function load($filter=[]) {

      $this->reset();

      $type = preg_replace('#mod_(.*)$#', '$1', get_called_class());

      if (!empty($filter) && !is_array($filter)) {
        $filter = [$filter];
      }

      $modules_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."modules
        where type = '". database::input(strtr($type, ['jobs' => 'job'])) ."'
        ". (!empty($filter) ? "and module_id in ('". implode("', '", database::input($filter)) ."')" : "") .";"
      );

      while ($module = database::fetch($modules_query)) {

        if (!is_file('app://includes/modules/'.$type.'/'.$module['module_id'].'.inc.php')) {

        // Remove deleted modules
          database::query(
            "delete from ". DB_TABLE_PREFIX ."modules
            where module_id = '". database::input($module['id']) ."'
            limit 1;"
          );

          continue;
        }

      // Create object
        $object = new $module['module_id'];
        $object->id = $module['module_id']; // Set ID

      // Decode settings
        $settings = json_decode($module['settings'], true);

      // Set settings to object
        $object->settings = [];
        foreach ($object->settings() as $setting) {
          $setting['key'] = rtrim($setting['key'], '[]');
          $object->settings[$setting['key']] = isset($settings[$setting['key']]) ? $settings[$setting['key']] : $setting['default_value'];
        }

        $object->status = (isset($object->settings['status']) && preg_match('#^(1|active|enabled|on|true|yes)$#i', $object->settings['status'])) ? 1 : 0;
        $object->priority = isset($object->settings['priority']) ? (int)$object->settings['priority'] : 0;

        if ($type == 'jobs') {
          $object->date_pushed = $module['date_pushed'];
          $object->date_processed = $module['date_processed'];
        }

      // Add module to list
        $this->modules[$object->id] = $object;
      }

    // Sort modules by priority
      uasort($this->modules, function($a, $b) {
        if ($a->priority == $b->priority) {
          return ($a->name < $b->name) ? -1 : 1;
        }
        return ($a->priority < $b->priority) ? -1 : 1;
      });
    }

    public function run($method_name, $module_id=null) {

      if (empty($module_id) && !empty($this->selected['id'])) {
        list($module_id, $option_id) = explode(':', $this->selected['id']);
      }

      if (empty($this->modules[$module_id]) || !method_exists($this->modules[$module_id], $method_name)) return false;

      return call_user_func_array([$this->modules[$module_id], $method_name], array_slice(func_get_args(), 2));
    }

  // Last destination method handler for prevent throwing errors if called methods are not defined
    public function call($name, $arguments) {}
  }
