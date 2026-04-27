<?php

	class mod_translation extends abs_modules {

		public function __construct() {
			$this->load();
		}

		public function translate($from_language_code, $to_language_code, $translations, $html = false) {

			if (!$this->modules) {
				trigger_error('No translation module are available to perform the translation', E_USER_WARNING);
			}

			foreach ($this->modules as $module) {
				if (method_exists($module, 'translate')) {
					$result = $module->translate($from_language_code, $to_language_code, $translations, $html);
					if (!empty($result)) {
						return $result;
					}
				}
			}

			return [];
		}

		public function run($method_name, $module_id=null) {

			if (empty($module_id)) {
				if (empty($this->data['selected']['id'])) return;
				list($module_id, $option_id) = explode(':', $this->data['selected']['id']);
			}

			if (!empty($this->modules[$module_id]) && method_exists($this->modules[$module_id], $method_name)) {
				return call_user_func_array([$this->modules[$module_id], $method_name], array_slice(func_get_args(), 2));
			}
		}
	}
