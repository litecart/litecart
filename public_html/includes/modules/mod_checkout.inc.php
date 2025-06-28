<?php

	class mod_checkout extends abs_modules {
		private $_selected;

		public function __construct() {
			$this->load();
		}

		public function select($module_id) {

			if (!in_array($module_id, array_column($this->options, 'module_id'))) {
				$this->_selected = null;
				throw new Exception(t('error_unknown_checkout_method', 'Unknown checkout method'));
			}

			$this->_selected = $module_id;
		}

		public function options($items, $customer) {

			$results = [];

			foreach ($this->modules as $module) {
				if ($options = $module->option($items, $customer)) {
					$results[] = [
						'module_id' => $module_id,
						'label' => $row['label'],
					];
				}
			}

			return $results;
		}

		public function process($order) {

			if (empty($this->_selected)) {
				throw new Exception(t('error_no_checkout_method_selected', 'No checkout method selected'));
			}

			return $this->_modules[$this->_selected]->process($order);
		}

		public function verify($order) {

			if (empty($this->_selected)) {
				throw new Exception(t('error_no_checkout_method_selected', 'No checkout method selected'));
			}

			return $this->_modules[$this->_selected]->process($order);
		}
	}
