<?php

	function admin_get_apps() {

		$apps_cache_token = cache::token('backend_apps', ['administrator', 'language']);
		if (!$apps = cache::get($apps_cache_token)) {

			$apps = [];

			foreach (scandir('app://backend/apps/') as $folder_name) {

				$id = basename($folder_name);
				$directory = 'app://backend/apps/'. $folder_name .'/';

				if (in_array($directory, ['.', '..']) || !is_dir($directory)) continue;
				if (!$config = require $directory . 'config.inc.php') continue;

				$config['theme'] = [
					'icon' => fallback($config['theme']['icon'], 'fa-plus'),
					'color' => fallback($config['theme']['color'], '#97a3b5'),
				];

				$apps[$id] = array_merge(['id' => $id, 'directory' => $directory], $config);
			}

			uasort($apps, function($a, $b) use ($apps) {

				if (!isset($a['priority'])) $a['priority'] = 0;
				if (!isset($b['priority'])) $b['priority'] = 0;

				if ($a['priority'] == $b['priority']) {
					return ($a['name'] < $b['name']) ? -1 : 1;
				}

				return ($a['priority'] < $b['priority']) ? -1 : 1;
			});

			cache::set($apps_cache_token, $apps);
		}

		return $apps;
	}

	function admin_get_widgets() {

		$widgets_cache_token = cache::token('backend_widgets', ['administrator', 'language']);
		if (!$widgets = cache::get($widgets_cache_token)) {

			$widgets = [];

			foreach (scandir('app://backend/widgets/') as $folder_name) {

				$id = basename($folder_name);
				$directory = 'app://backend/widgets/'. $folder_name .'/';

				if (in_array($directory, ['.', '..']) || !is_dir($directory)) continue;
				if (!$config = require $directory . 'config.inc.php') return;

				$widgets[$id] = array_merge(['id' => $id, 'directory' => $directory], $config);
			}

			uasort($widgets, function($a, $b) use ($widgets) {

				if (!isset($a['priority'])) $a['priority'] = 0;
				if (!isset($b['priority'])) $b['priority'] = 0;

				if ($a['priority'] == $b['priority']) {
					return ($a['name'] < $b['name']) ? -1 : 1;
				}

				return ($a['priority'] < $b['priority']) ? -1 : 1;
			});

			cache::set($widgets_cache_token, $widgets);
		}

		return $widgets;
	}
