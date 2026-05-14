<?php

	class session {

		public static $data;

		public static function init() {

			$_SESSION = &self::$data;

			event::register('before_output', [__CLASS__, 'save']);
			register_shutdown_function([__CLASS__, 'save']);

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

				if (empty($_COOKIE['LCSESSID'])) {
					self::generate_id();
				}

				if (empty(self::$data['fingerprint'])) {
					if (!empty($_SERVER['HTTP_CF_VISITOR'])) {

						// Use Cloudflare's visitor identifier if available
						self::$data['fingerprint'] = $_SERVER['HTTP_CF_VISITOR'];

					} else {

						// Generate a fingerprint based on request data
						self::$data['fingerprint'] = hash('sha256', implode([
							$_SERVER['SSL_PROTOCOL'] ?? '',
							$_SERVER['SSL_CIPHER'] ?? '',
							$_SERVER['REMOTE_ADDR'] ?? '',
							$_SERVER['HTTP_USER_AGENT'] ?? '',
							$_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
						]));
					}
				}

				// Collect conversion data (Urchin Tracking Module (UTM) etc.)
				if (empty(self::$data['conversions'])) {
					self::$data['conversions'] = [];
				}

				foreach ([
					'affiliate' => ['affid', 'affiliateId', 'affiliate_id', 'affiliate', 'partner', 'ref', 'referrer', 'clickid'],
					'facebook' => ['fbclid'],
					'google' => ['_rck', '_rcn', '_rct', 'dclid', 'gclid', 'gbraid', 'gad_source', 'gad_campaignid', 'wbraid'],
					'instagram' => ['igshid'],
					'linkedin' => ['li_fat_id'],
					'microsoft' => ['msclkid'],
					'pinterest' => ['epik'],
					'piwik' => ['pk_campaign', 'pk_source', 'pk_medium', 'pk_content', 'pk_kwd'],
					'tiktok' => ['ttclid'],
					'utm' => ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'],
					'yandex' => ['yclid'],
					'x' => ['twclid'],
				] as $provider => $keys) {
					foreach ($keys as $key) {
						if (!empty($_GET[$key])) {
							self::$data['conversions'][$key] = $_GET[$key];
						}
					}
				}
			}

			// Security Stats
			if (!is_ajax_request()) {
				self::$data['security']['page_loads'] = (self::$data['security']['page_loads'] ?? 0) + 1;
			}

			// Write statistics
			if (
				$_SERVER['SERVER_SOFTWARE'] != 'CLI' // Not a CLI request
				&& strpos($_SERVER['REQUEST_URI'], '/'. BACKEND_ALIAS .'/') === false // Not a backend request
				&& empty(self::$data['is_bot']) // Not a bot (Needs an addon to detect bots)
				&& !is_ajax_request() // Not an AJAX request
			) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."statistics
					(type, entity_type, entity_id, measure_group_type, measure_group_value, `count`)
					values ('page_views', 'domain', '". database::input($_SERVER['HTTP_HOST']) ."', 'day', '". database::input(date('Y-m-d')) ."', 1)
					on duplicate key update
					`count` = `count` + 1;"
				);
			}

			// Track who is online
			if (
				$_SERVER['SERVER_SOFTWARE'] != 'CLI' // Not a CLI request
				&& strpos($_SERVER['REQUEST_URI'], '/'. BACKEND_ALIAS .'/') === false // Not a backend request
				&& empty(self::$data['is_bot']) // Not a bot (Needs an addon to detect bots)
				&& !is_ajax_request() // Not an AJAX request
			) {

				// Find known visitor by session id
				$visitor = database::query(
					"select id from ". DB_TABLE_PREFIX ."visitors
					where session_id = '". database::input(self::$data['id']) ."'
					and updated_at > '". date('Y-m-d 00:00:00') ."'
					order by updated_at desc
					limit 1;"
				)->fetch();

				// Find known visitor by ip address and user agent
				if (!$visitor) {
					$visitor = database::query(
						"select id from ". DB_TABLE_PREFIX ."visitors
						where ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'
						and user_agent = '". database::input($_SERVER['HTTP_USER_AGENT'])."'
						and updated_at > '". date('Y-m-d 00:00:00') ."'
						order by updated_at desc
						limit 1;"
					)->fetch();
				}

				// Create new visitor record
				if (!$visitor) {

					database::query(
						"insert into ". DB_TABLE_PREFIX ."visitors
						(session_id, ip_address, hostname, user_agent, referrer, updated_at, created_at)
						values ('". database::input(self::$data['id']) ."', '". database::input($_SERVER['REMOTE_ADDR'])."', '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."', '". database::input($_SERVER['HTTP_USER_AGENT'])."', '". @database::input($_SERVER['HTTP_REFERER'])."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
					);

					$visitor['id'] = database::insert_id();
				}

				// Update visitor record
				database::query(
					"update ". DB_TABLE_PREFIX ."visitors
					set pageviews = pageviews + 1,
						cart_uid = '". database::input(cart::$data['uid']) ."',
						language = '". database::input(language::$selected['code']) ."',
						country_code = '". database::input(customer::$data['country_code']) ."',
						last_page = '". database::input((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ."',
						ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
						hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
						user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
						updated_at = '". date('Y-m-d H:i:s') ."'
					where id = ". (int)$visitor['id'] ."
					limit 1;"
				);
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

			// Do we need to check if human?
			if (empty(self::$data['security']['is_human']) && (!isset(route::$selected['controller']))) {
				if (!isset(route::$selected['resource']) || !in_array(route::$selected['resource'], [
					'f:are_you_human',
					'f:csp_report',
				])) {

					try {

						if (isset(self::$data['security']['failed_authentications']) && self::$data['security']['failed_authentications'] >= 5) {
							throw new Exception('Many failed authentications');
						}

						if (!empty(self::$data['security']['stuck_in_honeypot'])) {
							throw new Exception('Stuck in the honeypot');
						}

						if (isset(self::$data['security']['404_hits']) && self::$data['security']['404_hits'] >= 10) {
							throw new Exception('Suspicious amount of 404 hits');
						}

						if (isset(self::$data['security']['page_loads']) && self::$data['security']['page_loads'] >= 100) {
							throw new Exception('Suspicious amount of page loads');
						}

						if ($_SERVER['REMOTE_ADDR'] == gethostbyaddr($_SERVER['REMOTE_ADDR'])) {
							throw new Exception('IP address without a hostname');
						}

						// All good for now

					} catch (Exception $e) {
						redirect(document::ilink('f:are_you_human', ['redirect_url' => $_SERVER['REQUEST_URI']]));
						exit;
					}
				}
			}
		}

		## Node specific methods

		public static function reset() {
			self::$data = (new ent_session())->data;

			// Security
			self::$data['security'] = [
				'page_loads' => 0,
				'404_hits' => 0,
				'failed_authentications' => 0,
				'last_failed_login' => null,
			];
		}

		public static function load($session_id) {

			self::reset();

			$session = database::query(
				"select * from ". DB_TABLE_PREFIX ."sessions
				where id = '". database::input($session_id) ."'
				and expires_at > '". database::input(date('Y-m-d H:i:s')) ."'
				limit 1;"
			)->fetch(function(&$row){
				$row['data'] = $row['data'] ? json_decode($row['data'], true) : [];
			});

			if (!$session) {
				self::generate_id();
				return false;
			}

			self::$data = $session['data'];

		// Restore ent_order from serialized data
			if (!empty(self::$data['checkout']['order']) && is_array(self::$data['checkout']['order'])) {
				self::$data['checkout']['order'] = ent_order::from_data(self::$data['checkout']['order']);
			}

			return true;
		}

		public static function save() {

			// If we don't have an id we should generate one
			if (empty(self::$data['id'])) {
				self::generate_id();
			}

			// Save only the payload without pretty printing to reduce storage
			$expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

			database::query(
				"insert into ". DB_TABLE_PREFIX ."sessions
				(id, data, expires_at, updated_at, created_at)
				values (
					'". database::input(self::$data['id']) ."',
					'". database::input(f::format_json(self::$data)) ."',
					'". database::input($expires_at) ."',
					'". database::input(date('Y-m-d H:i:s')) ."',
					'". database::input(date('Y-m-d H:i:s')) ."'
				)
				on duplicate key update
					data = '". database::input(f::format_json(self::$data)) ."',
					expires_at = '". database::input($expires_at) ."',
					updated_at = '". database::input(date('Y-m-d H:i:s')) ."';"
			);

			return database::affected_rows() ? true : false;
		}

		public static function generate_id() {
			$id = bin2hex(random_bytes(24)); // 48 characters
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

		public static function csrf_token() {
			if (empty(self::$data['csrf_token'])) {
				self::$data['csrf_token'] = bin2hex(random_bytes(32));
			}
			return self::$data['csrf_token'];
		}

		public static function rotate_csrf_token() {
			self::$data['csrf_token'] = bin2hex(random_bytes(32));
			return self::$data['csrf_token'];
		}

		public static function close() {
			trigger_error('Calling '.__CLASS__.'::close() is deprecated and can be removed.', E_USER_DEPRECATED);
		}
	}
