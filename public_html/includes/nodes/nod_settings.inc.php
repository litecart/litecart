<?php

  class settings {
    private static $_cache;

    public static function init() {

      $settings_query = database::query(
        "select `key`, `value`, `function`
        from ". DB_TABLE_PREFIX ."settings
        where `type` = 'global';"
      );

      while ($setting = database::fetch($settings_query)) {

				switch (true) {

					case (substr($setting['function'], 0, 9) == 'regional_'):

						if (!class_exists('language') || empty(language::$selected)) continue 2;

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
      }
    }

    ######################################################################

    public static function get(string $key, $fallback=null) {

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

					if (!class_exists('language') || empty(language::$selected)) return;

          if ($setting['value']) {
            $setting['value'] = json_decode($setting['value'], true);

						if (!empty($setting['value'][language::$selected['code']])) {
							$setting['value'] = $setting['value'][language::$selected['code']];

						} else if (!empty($value['en'])) {
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

    public static function set($key, $value) {
      self::$_cache[$key] = $value;
    }
  }
