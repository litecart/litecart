<?php

  abstract class abs_module {
    public $modules;

    public function reset() {
      $this->modules = array();
    }

    public function load($type, $filter=null) {

      $this->reset();

      if (!empty($filter) && !is_array($filter)) $filter = array($filter);

      switch($type) {
        case 'job':
          $directory = FS_DIR_APP . 'includes/modules/jobs/';
          break;
        default:
          $directory = FS_DIR_APP . 'includes/modules/' . $type . '/';
          break;
      }

      $modules_query = database::query(
        "select * from ". DB_TABLE_MODULES ."
        where type = '". database::input($type) ."'
        ". (!empty($filter) ? "and module_id in ('". implode("', '", database::input($filter)) ."')" : "") .";"
      );

      while ($module = database::fetch($modules_query)) {

        if (!is_file(vmod::check($directory . $module['module_id'] .'.inc.php'))) {

        // Uninstall orphan modules
          //database::query(
          //  "delete from ". DB_TABLE_MODULES ."
          //  where module_id = '". database::input($module['id']) ."'
          //  limit 1;"
          //);

          continue;
        }

      // Create object
        $object = new $module['module_id'];

      // Decode settings
        $settings = json_decode($module['settings'], true);

      // Set settings to object
        $object->settings = array();
        foreach ($object->settings() as $setting) {
          $object->settings[$setting['key']] = isset($settings[$setting['key']]) ? $settings[$setting['key']] : $setting['default_value'];
        }
        $object->status = (isset($object->settings['status']) && in_array(strtolower($object->settings['status']), array('1', 'active', 'enabled', 'on', 'true', 'yes'))) ? 1 : 0;
        $object->priority = isset($object->settings['priority']) ? (int)$object->settings['priority'] : 0;

        if ($type == 'job') {
          $object->date_pushed = $module['date_pushed'];
          $object->date_processed = $module['date_processed'];
        }

      // Add module to list
        $this->modules[$object->id] = $object;
      }

    // Sort modules by priority
      if (!empty($this->modules)) {
        uasort($this->modules, function($a, $b) {
          if ((int)$a->priority == (int)$b->priority) {
            return ($a->name < $b->name) ? 1 : -1;
          } else if ((int)$a->priority > (int)$b->priority) {
            return 1;
          } else {
            return -1;
          }
        });
      }
    }
  }
