<?php

  abstract class abs_module {
    public $modules;

    public function reset() {
      $this->modules = [];
    }

    public function load($filter=null) {

      $this->reset();

      $type = preg_replace('#mod_(.*)$#', '$1', get_called_class());

      $directory = FS_DIR_APP . 'includes/modules/' . $type . '/';

      if (!empty($filter) && !is_array($filter)) $filter = [$filter];

      $modules_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."modules
        where type = '". database::input(strtr($type, ['jobs' => 'job'])) ."'
        ". (!empty($filter) ? "and module_id in ('". implode("', '", database::input($filter)) ."')" : "") .";"
      );

      while ($module = database::fetch($modules_query)) {

        if (!is_file(vmod::check($directory . $module['module_id'] .'.inc.php'))) {

        // Remove deleted modules
          database::query(
            "delete from ". DB_TABLE_PREFIX ."modules
            where module_id = '". database::input($module['id']) ."'
            limit 1;"
          );

          continue;
        }

      // Patch modules for PHP 8.2 Compatibility
        if (version_compare(PHP_VERSION, 8.2, '>=')) {
          if (is_file($file = $directory . $module['module_id'] .'.inc.php')) {
            $source = file_get_contents($file);
            if (!preg_match('#\#\[AllowDynamicProperties\]#', $source)) {
              $source = preg_replace('#( *)class [a-zA-Z0-9_-]+ *\{(\n|\r\n?)#', '$1#[AllowDynamicProperties]$2$0', $source);
              file_put_contents($file, $source);
            }
          }
        }

      // Create object
        $object = new $module['module_id'];

      // Decode settings
        $settings = json_decode($module['settings'], true);

      // Set settings to object
        $object->settings = [];
        foreach ($object->settings() as $setting) {
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
  }
