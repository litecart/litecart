<?php

	class session {

		public static $data;

		public static function init(): void {

			$_SESSION = &self::$data;

			event::register('before_output', [__CLASS__, 'save']);
			register_shutdown_function([__CLASS__, 'save']);
			
			$session_name = ini_get('session.name');

			if (!empty($_COOKIE[$session_name]) && self::validate_id($_COOKIE[$session_name])) {
				self::load($_COOKIE[$session_name]);

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

				if (empty($_COOKIE[$session_name])) {
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

			event::register('before_capture', [__CLASS__, 'before_capture']);
		}

		public static function before_capture(): void {

			// Security Stats
			if (!is_ajax_request()) {
				self::$data['security']['page_loads'] = (self::$data['security']['page_loads'] ?? 0) + 1;
			}

			// Write statistics
			if (
				$_SERVER['SERVER_SOFTWARE'] != 'CLI' // Not a CLI request
				&& !str_starts_with(route::$request, BACKEND_ALIAS .'/') // Not a backend request
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
				&& !str_starts_with(route::$request, BACKEND_ALIAS .'/') // Not a backend request
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

				// Fallback to find known visitor by ip address and user agent
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
						country_code = '". database::input(customer::$data['country_code'] ?? '') ."',
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

			// Keep track of the last 10 requests in the session (excluding AJAX requests)
			if (!is_ajax_request()) {
				self::$data['last_requests'] = array_slice((self::$data['last_requests'] ?? []) + [
					'method' => $_SERVER['REQUEST_METHOD'],
					'url' => $_SERVER['REQUEST_URI'],
					'timestamp' => date('Y-m-d H:i:s'),
				], -10);
			}

			// Keep track of page views in the session
			self::$data['page_views'] = (!empty(self::$data['page_views']) ? (int)self::$data['page_views'] : 0) + 1;

			// Keep track of external referrer (but only the first one in the session)
			if (empty(self::$data['referrer']) && !empty($_SERVER['HTTP_REFERER']) && parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) != $_SERVER['HTTP_HOST']) {
				self::$data['referrer'] = $_SERVER['HTTP_REFERER'];
			}
		}

		## Node specific methods

		public static function reset(): void {
			self::$data = (new ent_session())->data;
		}

		public static function load(string $session_id): bool {

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

			return true;
		}

		public static function save(): bool {

			// If we don't have an id we should generate one
			if (empty(self::$data['id'])) {
				self::generate_id();
			}

			$expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

			database::query(
				"insert into ". DB_TABLE_PREFIX ."sessions
				(id, data, expires_at, updated_at, created_at)
				values (
					'". database::input(self::$data['id']) ."',
					'". database::input(f::format_json(self::$data, '')) ."',
					'". database::input($expires_at) ."',
					'". database::input(date('Y-m-d H:i:s')) ."',
					'". database::input(date('Y-m-d H:i:s')) ."'
				)
				on duplicate key update
					data = '". database::input(f::format_json(self::$data, '')) ."',
					expires_at = '". database::input($expires_at) ."',
					updated_at = '". database::input(date('Y-m-d H:i:s')) ."';"
			);

			return database::affected_rows() ? true : false;
		}

		public static function generate_id(): string {
			$id = bin2hex(random_bytes(24)); // 48 characters
			self::$data['id'] = $id;
			self::set_cookie();
			return $id;
		}

		public static function validate_id(string $session_id): bool {

			if (preg_match('#^[0-9a-z]+$#i', $session_id)) {
				return true;
			}

			return false;
		}

		public static function regenerate_id(): void {

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

		public static function set_cookie(): void {

			$is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
				|| (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

			// Only use SameSite=None when cookie is Secure (required by modern browsers)
			$samesite = $is_secure ? 'None' : 'Lax';
			
			$session_name = ini_get('session.name');

			header('Set-Cookie: '. $session_name .'='. rawurlencode(self::$data['id']) .';Path=/;'. ($is_secure ? 'Secure;' : '') .'HttpOnly;SameSite=' . $samesite);
		}

		public static function close(): void {
			trigger_error('Calling '.__CLASS__.'::close() is deprecated and can be removed.', E_USER_DEPRECATED);
		}
	}
