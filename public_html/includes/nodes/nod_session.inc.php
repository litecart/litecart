<?php

	class session {

		public static $data;

		public static function init() {

			$_SESSION = &self::$data;

			register_shutdown_function([__CLASS__, 'save']);
			//event::register('shutdown', [__CLASS__, 'save']);

			if (!empty($_COOKIE['LCSESSID']) && self::validate_id($_COOKIE['LCSESSID'])) {
				self::load($_COOKIE['LCSESSID']);

			} else {

				self::reset();

				if (empty(self::$data['last_ip_address'])) {
					self::$data['last_ip_address'] = $_SERVER['REMOTE_ADDR'];
				}

				if (empty(self::$data['last_user_agent'])) {
					self::$data['last_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
				}

				if ($_SERVER['REMOTE_ADDR'] != self::$data['last_ip_address']
				|| $_SERVER['HTTP_USER_AGENT'] != self::$data['last_user_agent']) {
					self::$data['last_ip_address'] = $_SERVER['REMOTE_ADDR'];
					self::$data['last_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
					self::regenerate_id();
				}

				// Collect Urchin Tracking Module (UTM) data
				if (empty(self::$data['utm_data'])) {
					self::$data['utm_data'] = [];
				}

				foreach ([
					'source',
					'medium',
					'campaign',
					'term',
					'content',
				] as $key) {
					if (!empty($_GET['utm_'.$key])) {
						self::$data['utm'][$key] = $_GET['utm_'.$key];
					}
				}

				if (empty(self::$data['is_bot'])) { // Needs an addon to detect bots
					database::query(
						"insert into ". DB_TABLE_PREFIX ."statistics
						(type, entity_type, entity_id, measure_group_type, measure_group_value, `count`)
						values ('page_views', 'domain', '". database::input($_SERVER['HTTP_HOST']) ."', 'day', '". database::input(date('Y-m-d')) ."', 1)
						on duplicate key update
						`count` = `count` + 1;"
					);
				}
			}

			// Keep track on some updated information
			self::$data['last_url'] = document::link() ?: null;

			// Count page view when the current request differs from the last one
			$current_request = $_SERVER['REQUEST_METHOD'] .' '. $_SERVER['REQUEST_URI'];
			if (isset(self::$data['last_request']) && self::$data['last_request'] !== $current_request) {
				self::$data['page_views'] = (!empty(self::$data['page_views']) ? (int)self::$data['page_views'] : 0) + 1;
			}

			if (empty(self::$data['referrer']) && !empty($_SERVER['HTTP_REFERER'])) {
				if (empty(self::$data['referrer']) && !empty($_SERVER['HTTP_REFERER'])) {
					self::$data['referrer'] = $_SERVER['HTTP_REFERER'];
				}
			}

			self::$data['last_request'] = $current_request;

		}

		public static function reset() {
			self::$data = (new ent_session())->data;
		}

		public static function load($session_id) {

			self::reset();

			$session = database::query(
				"select * from ". DB_TABLE_PREFIX ."sessions
				where id = '". database::input($session_id) ."'
				limit 1;"
			)->fetch(function(&$row){
				$row['data'] = $row['data'] ? json_decode($row['data'], true) : [];
			});

			if (!$session) {
				return false;
			}

			self::$data = $session['data'];

			return true;
		}

		public static function save() {

			// If we don't have an id we should generate one
			if (empty(self::$data['id'])) {
				self::generate_id();
			}

			// Save only the payload without pretty printing to reduce storage
			database::query(
				"insert into ". DB_TABLE_PREFIX ."sessions
				(id, data, updated_at, created_at)
				values (
					'". database::input(self::$data['id']) ."',
					'". database::input(json_encode(self::$data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					'". database::input(date('Y-m-d H:i:s')) ."',
					'". database::input(date('Y-m-d H:i:s')) ."'
				)
				on duplicate key update
					data = '". database::input(json_encode(self::$data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					expires_at = '". database::input(date('Y-m-d H:i:s', strtotime('+1 hour'))) ."',
					updated_at = '". database::input(date('Y-m-d H:i:s')) ."';"
			);

			return database::affected_rows() ? true : false;
		}

		public static function generate_id() {
			$id = bin2hex(random_bytes(16));
			self::$data['id'] = $id;
			self::set_cookie();
			return $id;
		}

		public static function validate_id($session_id) {

			if (preg_match('#^[0-9a-z]+$#i', $session_id)) {
				return true;
			}

			return false;
		}

		public static function regenerate_id() {

			if (!empty(self::$data['id'])) {
				database::query(
					"update ". DB_TABLE_PREFIX ."sessions
					set expires_at = '". database::input(date('Y-m-d H:i:s')) ."'
					where id = '". database::input(self::$data['id']) ."'
					limit 1;"
				);
			}

			self::$data['id'] = self::generate_id();
		}

		public static function set_cookie() {

			$is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
				|| (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

			// Only use SameSite=None when cookie is Secure (required by modern browsers)
			$samesite = $is_secure ? 'None' : 'Lax';

			header('Set-Cookie: LCSESSID='. rawurlencode(self::$data['id']) .';Path=/;'. ($is_secure ? 'Secure;' : '') .'HttpOnly;SameSite=' . $samesite);
		}
	}
