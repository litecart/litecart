<?php

	class notices {

		public static $data;

		public static function init(): void {

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

		## Node specific methods

		public static function reset(?string $type=null): void {

			if ($type) {
				self::$data[$type] = [];

			} else if (!empty(self::$data)) {

				foreach (self::$data as $type => $container) {
					self::$data[$type] = [];
				}
			}
		}

		public static function add(string $type, string $msg, ?string $key=null): void {

			if ($key) {
				self::$data[$type][$key] = $msg;
			} else {
				self::$data[$type][] = $msg;
			}
		}

		public static function remove(string $type, string $key): void {
			unset(self::$data[$type][$key]);
		}

		public static function get(string $type): array|false {

			if (!isset(self::$data[$type])) {
				return false;
			}

			return self::$data[$type];
		}

		public static function dump(string $type): array {
			$stack = self::$data[$type];

			self::$data[$type] = [];

			return $stack;
		}

		public static function render(): string {

			self::$data = array_filter(self::$data);

			if (empty(self::$data)) return '';

			$view = match(route::$selected['endpoint'] ?? null) {
				'backend' => new ent_view('app://backend/template/partials/notices.inc.php'),
				'frontend' => new ent_view('app://frontend/templates/'.settings::get('template').'/partials/notices.inc.php'),
				default => new ent_view('app://frontend/templates/'.settings::get('template').'/partials/notices.inc.php'),
			};

			$view->snippets['notices'] = self::$data;
			$output = $view->render();

			self::reset();

			return $output;
		}
	}
