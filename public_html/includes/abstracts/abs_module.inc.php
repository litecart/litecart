<?php

  abstract class abs_module {
    public $id = null;
    public $name = '';
    public $description = '';
    public $author = '';
    public $version = '';
    public $support_link = '';
    public $website = '';

    public $status = null;
    public $settings = [];
    public $priority = 0;
    public $date_pushed = null;
    public $date_processed = null;

    public function __construct() {

      $this->id = get_called_class();

      $module = database::query(
        "select * from ". DB_TABLE_PREFIX ."modules
        where module_id = '". get_called_class() ."'
        limit 1;"
      )->fetch();

      if ($module) {

        // Decode settings
        $settings = json_decode($module['settings'], true);

        // Set settings to object
        foreach ($this->settings() as $setting) {
          $setting['key'] = rtrim($setting['key'], '[]');
          $this->settings[$setting['key']] = isset($settings[$setting['key']]) ? $settings[$setting['key']] : $setting['default_value'];
        }

        $this->status = (isset($this->settings['status']) && in_array(strtolower($this->settings['status']), ['1', 'active', 'enabled', 'on', 'true', 'yes'])) ? 1 : 0;
        $this->priority = isset($this->settings['priority']) ? (int)$this->settings['priority'] : 0;

        if ($module['type'] == 'jobs') {
          $this->date_pushed = $module['date_pushed'];
          $this->date_processed = $module['date_processed'];
        }
      }
    }

    // Last destination method handler for prevent throwing errors if called methods are not defined
    public function __call($name, $arguments) {}
  }
