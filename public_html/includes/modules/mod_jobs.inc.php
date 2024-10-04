<?php

  class mod_jobs extends abs_module {

    public function __construct() {
      $this->load();
    }

    public function process($modules=null, $force=false) {

      if (empty($this->modules)) return;

      if (empty($modules)) $modules = array_keys($this->modules);
      if (!is_array($modules)) $modules = [$modules];

      $output = '';

      foreach ($modules as $module_id) {

        if (!in_array($module_id, array_keys($this->modules))) {
          trigger_error($module_id .' is not a valid module id', E_USER_WARNING);
          continue;
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."modules set
          date_pushed = '". date('Y-m-d H:i:s') ."'
          where module_id = '". database::input($module_id) ."'
          limit 1;"
        );

				if (!$last_run = $this->modules[$module_id]->date_processed) {
					$last_run = 0; // As null will throw a Deprecated warning
				}

				ob_start();
				$timestamp = microtime(true);
				$this->modules[$module_id]->process($force, $last_run);
				$log = ob_get_clean();

        if (!empty($log)) {

          $log = str_repeat('#', 72) . PHP_EOL
               . '#'. str_pad(" $module_id executed at ". date('Y-m-d H:i:s') .' ', 71, '#', STR_PAD_RIGHT) . PHP_EOL
               . str_repeat('#', 72) . PHP_EOL . PHP_EOL
               . $log . PHP_EOL . PHP_EOL
               . str_repeat('#', 72) . PHP_EOL
               . '#'. str_pad(' Completed in '. round(microtime(true) - $timestamp, 3).' s ', 71, '#', STR_PAD_RIGHT) . PHP_EOL
               . str_repeat('#', 72) . PHP_EOL;

          $output .= $log;

          database::query(
            "update ". DB_TABLE_PREFIX ."modules set
            last_log = '". database::input($log) ."',
            date_processed = '". date('Y-m-d H:i:s') ."'
            where module_id = '". database::input($module_id) ."'
            limit 1;"
          );
        }
      }

      return $output;
    }

    public function run($method_name, $module_id) {
      if (method_exists($this->modules[$module_id], $method_name)) {
        return call_user_func_array([$this->modules[$module_id], $method_name], array_slice(func_get_args(), 2));
      }
    }
  }
