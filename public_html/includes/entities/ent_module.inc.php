<?php

  class ent_module {
    private $_module;
    public $data;
    public $previous;

    public function __construct($module_id) {

      if (empty($module_id)) throw new Exception('First argument module_id cannot be empty');

      preg_match('#^([^_]+)#', $module_id, $matches);

      switch ($matches[1]) {
        case 'cm':
          $type = 'customer';
          break;
        case 'sm':
          $type = 'shipping';
          break;
        case 'pm':
          $type = 'payment';
          break;
        case 'om':
          $type = 'order';
          break;
        case 'ot':
          $type = 'order_total';
          break;
        case 'job':
          $type = 'job';
          break;
        default:
          throw new Exception('Unknown module type for module '. $module_id);
      }

      $this->load($module_id, $type);

      $this->previous = $this->data;
    }

    private function _encode_settings($data) {
      return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    private function _decode_settings($data) {
      if (empty($data)) return;
      return json_decode($data, true);
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."modules;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field['Type']);
      }

      $this->data['settings'] = [];
    }

    public function load($module_id, $type) {

      if (!preg_match('#^[a-z0-9_]+$#', $module_id)) throw new Exception('Invalid module (ID: '. $module_id .')');

      $this->reset();

      $module = database::query(
        "select * from ". DB_TABLE_PREFIX ."modules
        where type = '". database::input($type) ."'
        and module_id = '". database::input($module_id) ."'
        limit 1;"
      )->fetch();

      if ($module) {
        $this->data = array_replace($this->data, array_intersect_key($module, $this->data));
      }

      $this->_module = new $module_id;
      $this->_module->settings = &$this->data['settings'];

      $this->data['module_id'] = $module_id;
      $this->data['type'] = $type;

      $this->data['settings'] = $this->_decode_settings($this->data['settings']);

      foreach ($this->_module->settings() as $structure) {

        if (substr($structure['function'], 0, 8) == 'regional') {
          foreach (array_keys(language::$languages) as $language_code) {
            if (!isset($this->data['settings'][$structure['key']][$language_code])) $this->data['settings'][$structure['key']][$language_code] = $structure['default_value'];
          }

        } else {
          if (!isset($this->data['settings'][$structure['key']])) $this->data['settings'][$structure['key']] = $structure['default_value'];
        }
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {

        database::query(
          "insert into ". DB_TABLE_PREFIX ."modules
          (module_id, type, date_created)
          values ('". database::input($this->data['module_id']) ."', '". database::input($this->data['type']) ."', '". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );

        $this->data['id'] = database::insert_id();

        if (method_exists($this->_module, 'install')) {
          $this->_module->install();
        }

      } else {

        if (method_exists($this->_module, 'update')) {
          $this->_module->update();
        }
      }

      if (isset($this->data['settings']['status']) && preg_match('#^(1|active|enabled|on|true|yes)$#i', $this->data['settings']['status'])) {
        $this->data['status'] = 1;
      } else {
        $this->data['status'] = 0;
      }

      $this->data['priority'] = (int)$this->data['settings']['priority'];

      database::query(
        "update ". DB_TABLE_PREFIX ."modules
        set module_id = '". database::input($this->data['module_id']) ."',
          type = '". database::input($this->data['type']) ."',
          status = ". (int)$this->data['status'] .",
          priority = ". (int)$this->data['priority'] .",
          settings = '". database::input($this->_encode_settings($this->data['settings'])) ."',
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)database::input($this->data['id']) ."
        limit 1;"
      );

      $this->previous = $this->data;

      cache::clear_cache('modules');
    }

    public function delete() {

      if (method_exists($this->_module, 'uninstall')) {
        $this->_module->uninstall();
      }

      database::query(
        "delete from ". DB_TABLE_PREFIX ."modules
        where module_id = '". database::input($this->data['module_id']) ."'
        limit 1;"
      );

      $this->reset();

      cache::clear_cache('modules');
    }
  }
