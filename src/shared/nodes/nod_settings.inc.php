<?php

	class settings {

		private static $_cache;

		public static function init(): void {

			database::query(
				"select `key`, `value`, `function`
				from ". DB_TABLE_PREFIX ."settings;"
			)->each(function($setting){

				switch (true) {

					case (substr($setting['function'], 0, 9) == 'regional_'):

						if (!class_exists('language') || !language::$selected) break;

						if ($setting['value']) {
							$setting['value'] = json_decode($setting['value'], true);

							if (!empty($setting['value'][language::$selected['code']])) {
								$setting['value'] = $setting['value'][language::$selected['code']];

							} else if (!empty($setting['value']['en'])) {
								$setting['value'] = $setting['value']['en'];

							} else {
								$setting['value'] = '';
							}

						} else {
							$setting['value'] = '';
						}

						break;
				}

				self::$_cache[$setting['key']] = $setting['value'];
			});
		}

		## Node specific methods

		public static function get(string $key, mixed $fallback=null): mixed {

			if (isset(self::$_cache[$key])) return self::$_cache[$key];

			$setting = database::query(
				"select `key`, `value`, `function`
				from ". DB_TABLE_PREFIX ."settings
				where `key` = '". database::input($key) ."'
				limit 1;"
			)->fetch();

			if (!$setting) {

				if ($fallback === null) {
					trigger_error('Unsupported settings key ('. $key .')', E_USER_WARNING);
				}

				return $fallback;
			}

			switch (true) {

				case (substr($setting['function'], 0, 9) == 'regional_'):

					if (!class_exists('language') || empty(language::$selected)) return $fallback;

					if ($setting['value']) {
						$setting['value'] = json_decode($setting['value'], true);

						if (!empty($setting['value'][language::$selected['code']])) {
							$setting['value'] = $setting['value'][language::$selected['code']];

						} else if (!empty($setting['value']['en'])) {
							$setting['value'] = $setting['value']['en'];

						} else {
							$setting['value'] = '';
						}

					} else {
						$setting['value'] = [];
					}

					break;

			}

			return self::$_cache[$key] = $setting['value'];
		}

		public static function set(string $key, mixed $value): void {
			self::$_cache[$key] = $value;
		}
	}
