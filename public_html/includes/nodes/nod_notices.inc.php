<?php

	class notices {

		public static $data;

		public static function init() {

			if (empty(session::$data['notices']) || !is_array(session::$data['notices'])) {
				session::$data['notices'] = [
					'errors' => [],
					'warnings' => [],
					'notices' => [],
					'success' => [],
				];
			}

			self::$data = &session::$data['notices'];
		}

		public static function reset($type=null) {

			if ($type) {
				self::$data[$type] = [];

			} else if (!empty(self::$data)) {
				
				foreach (self::$data as $type => $container) {
					self::$data[$type] = [];
				}
			}
		}

		public static function add($type, $msg, $key=null) {
			
			if ($key) {
				self::$data[$type][$key] = $msg;
			}	else {
				self::$data[$type][] = $msg;
			}
		}

		public static function remove($type, $key) {
			unset(self::$data[$type][$key]);
		}

		public static function get($type) {
			
			if (!isset(self::$data[$type])) {
				return false;
			}
	
			return self::$data[$type];
		}

		public static function dump($type) {
			
			$stack = self::$data[$type];
			
			self::$data[$type] = [];
			
			return $stack;
		}

		public static function render() {

			self::$data = array_filter(self::$data);

			if (empty(self::$data)) return '';

			switch (route::$selected['endpoint']) {

				case 'backend':
					$view = new ent_view('app://backend/template/partials/notices.inc.php');
					break;

				default:
					$view = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/notices.inc.php');
					break;
			}

			$view->snippets['notices'] = self::$data;
			$output = $view->render();

			self::reset();

			return $output;
		}
	}
