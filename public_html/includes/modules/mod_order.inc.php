<?php

	class mod_order extends abs_modules {

		public function __construct() {
			$this->load();
		}

		public function actions() {

			$actions = [];

			if (!$this->modules) {
				return;
			}

			foreach ($this->modules as $module) {

				if (!method_exists($module, 'actions')) {
					continue;
				}

				$result = $module->actions();

				if (empty($result)) continue;

				$actions[$module->id] = [
					'id' => $result['id'],
					'name' => $result['name'],
					'description' => @$result['description'],
					'actions' => [],
				];

				foreach ($result['actions'] as $action) {
					$actions[$module->id]['actions'][$action['id']] = [
						'id' => $action['id'],
						'title' => $action['title'],
						'description' => @$action['description'],
						'function' => $action['function'],
						'target' => fallback($action['target'], '_self'),
					];
				}
			}

			return $actions;
		}

		public function validate($order) {

			if (!$this->modules) {
				return;
			}

			foreach ($this->modules as $module_id => $module) {
				if (method_exists($this->modules[$module_id], 'validate')) {
					if ($result = $module->validate($order)) return $result;
				}
			}
		}

		public function success($order) {

			if (!$this->modules) {
				return;
			}

			$output = '';

			foreach ($this->modules as $module_id => $module) {
				if (method_exists($this->modules[$module_id], 'success')) {
					if ($data = $module->success($order)) {
						$output .= $data;
					}
				}
			}

			return $output;
		}

		public function update($order) {
			return $this->run('update', null, $order);
		}

		public function delete($order) {
			return $this->run('delete', null, $order);
		}
	}
