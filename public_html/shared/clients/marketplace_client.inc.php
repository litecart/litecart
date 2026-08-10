<?php

	class marketplace_client {
		private static $_client;
/*
		public static function connect() {

			try {

				$result = self::_call('POST', '/connect');

				return $result;

			} catch (Exception $e) {
					// Do nothing
			}
		}
*/
		public static function whoami() {

			try {

				$marketplace_profile_cache_token = cache::token('marketplace_profile', [], 'memory', 43200);
				if (!$result = cache::get($marketplace_profile_cache_token, 43200)) {

					$result = self::_call('GET', '/whoami');
					cache::set($marketplace_profile_cache_token, $result);
				}

				return $result;

			} catch (Exception $e) {
					// Do nothing
			}
		}

		public static function get_marketplace($limit=16) {

			try {

				$marketplace_categories_cache_token = cache::token('marketplace', [], 'memory', 43200);
				if (!$result = cache::get($marketplace_categories_cache_token, 43200)) {

					$result = self::_call('GET', '/marketplace/home?limit='. $limit);
					cache::set($marketplace_categories_cache_token, $result);
				}

				return $result;

			} catch (Exception $e) {
				return [
					'categories' => [],
					'featured' => [],
					'most_popular' => [],
					'best_selling' => [],
				];
			}
		}

		public static function get_categories() {

			try {

				$result = self::get_marketplace();
				return $result['categories'];

			} catch (Exception $e) {
					// Do nothing
			}
		}

		public static function get_best_selling_addons() {

			try {

				$result = self::get_marketplace();
				return $result['best_selling'];

			} catch (Exception $e) {
					// Do nothing
			}
		}

		public static function get_most_popular_addons() {

			try {

				$marketplace = self::get_marketplace();
				return $result['popular'];

			} catch (Exception $e) {
				die($e->getMessage());
					// Do nothing
			}
		}

		public static function get_addons($filter) {

			try {

				$result = self::_call('GET', '/addons', array_filter($filter));

				preg_match('#Link: .*<[^>]+page=(\d+)>; rel=last#', self::$_client->last_response['headers'], $matches);

				return [
					'addons' => (($result ?? '') ?: []),
					'pages' => (($matches[1] ?? '') ?: 1),
				];

			} catch (Exception $e) {
				return [
					'addons' => [],
					'pages' => 0,
				];
			}
		}

		public static function get_addon($addon_id) {

			try {

				$result = self::_call('GET', '/addons/'.(int)$addon_id);
				return $result;

			} catch (Exception $e) {
				die($e->getMessage());
				return false;
			}
		}

		public static function get_addon_package($package_id) {

			try {

				$result = self::_call('GET', '/addons/packages/'.(int)$package_id);
				return $result;

			} catch (Exception $e) {
				die($e->getMessage());
				return false;
			}
		}

		public static function get_licenses() {
return [];
			try {

				$result = self::_call('GET', '/licenses');

				return $result;

			} catch (Exception $e) {
				return false;
			}
		}

		private static function _call($method, $endpoint, $data=null) {

				self::$_client = new http_client();

				if (strtoupper($method) == 'GET') {
					$url = document::link('https://www.litecart.net/api'.$endpoint, $data);
				} else {
					$url = document::link('https://www.litecart.net/api'.$endpoint);
				}

				$result = self::$_client->call($method, $url, $data, [
					'X-Access-Token' => settings::get('marketplace_access_token'),
				]);

				if (!$result = json_decode($result, true)) {
					throw new Exception('Invalid JSON response');
				}

				if (!empty($result['error'])) {
					throw new Exception($result['error']);
				}

				return $result;
		}
	}
