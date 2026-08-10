<?php

	function admin_get_apps(): array {

		$apps_cache_token = cache::token('backend_apps', ['administrator', 'language']);
		if (!$apps = cache::get($apps_cache_token)) {

			$apps = [];

			foreach (scandir('app://backend/apps/') as $folder_name) {

				if (preg_match('#\.disabled$#', $folder_name)) continue;

				$id = basename($folder_name);
				$directory = 'app://backend/apps/'. $folder_name .'/';

				if (in_array($directory, ['.', '..']) || !is_dir($directory)) continue;
				if (!$config = require $directory . 'config.inc.php') continue;

				$config['theme'] = [
					'icon' => $config['theme']['icon'] ?? 'icon-plus',
					'color' => $config['theme']['color'] ?? '#97a3b5',
				];

				if (empty($config['group'])) {
					$config['group'] = 'other';
				}

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

	function admin_get_grouped_apps(): array {

		$apps_cache_token = cache::token('backend_apps', ['administrator', 'language']);
		if (!$apps = cache::get($apps_cache_token)) {

			$groups = [];

			$apps = f::admin_get_apps();

			foreach ($apps as $app) {
				$groups[$app['group']][$app['id']] = $app;
			}

			cache::set($apps_cache_token, $groups);
		}

		return $groups;
	}

	function admin_get_widgets(): array {

		$widgets_cache_token = cache::token('backend_widgets', ['administrator', 'language']);
		if (!$widgets = cache::get($widgets_cache_token)) {

			$widgets = [];

			foreach (scandir('app://backend/widgets/') as $folder_name) {

				if (preg_match('#\.disabled$#', $folder_name)) continue;

				$id = basename($folder_name);
				$directory = 'app://backend/widgets/'. $folder_name .'/';

				if (in_array($directory, ['.', '..']) || !is_dir($directory)) continue;
				if (!$config = require $directory . 'config.inc.php') continue;

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

	function admin_get_mcp_tools(): array {

		$tools_cache_token = cache::token('backend_mcp_tools', ['administrator', 'language']);
		if (!$toolsets = cache::get($tools_cache_token)) {

			$toolsets = [];

			foreach (f::file_search('app://backend/mcp/mcp_*.inc.php') as $mcp_file) {

				// Include without polluting global scope
				$toolset = (function() use ($mcp_file) {
					return include $mcp_file;
				})();

				if (empty($toolset['name']) || !is_array($toolset['tools'])) {
					continue;
				}

				$toolsets[] = [
					'id' => preg_replace('#^mcp_(.+)\.inc\.php$#', '$1', basename($mcp_file)),
					'name' => $toolset['name'],
					'description' => $toolset['description'] ?? '',
					'tools' => array_column($toolset['tools'] ?? [], 'name'),
				];
			}

			cache::set($tools_cache_token, $toolsets);
		}

		return $toolsets;
	}
