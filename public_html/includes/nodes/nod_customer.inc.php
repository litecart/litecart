<?php

	class customer {

		public static $data;
		public static $scraps;

		public static function init() {

			if (empty(session::$data['customer']) || !is_array(session::$data['customer'])) {
				self::reset();
			}

			// Bind customer to session
			self::$data = &session::$data['customer'];

			if (empty(session::$data['scraps']) || !is_array(session::$data['scraps'])) {
				session::$data['scraps'] = [];
			}

			// Bind scraps to session
			self::$scraps = &session::$data['scraps'];

			// Sign in a remembered customer
			if (empty(self::$data['id']) && !empty($_COOKIE['customer_remember_me']) && empty($_POST)) {

				try {

					list($email, $key) = explode(':', $_COOKIE['customer_remember_me']);

					$customer = database::query(
						"select * from ". DB_TABLE_PREFIX ."customers
						where email = '". database::input($email) ."'
						limit 1;"
					)->fetch();

					if (!$customer) {
						throw new Exception('Invalid email or the account has been removed');
					}

					$checksum = sha1($customer['email'] . $customer['password_hash'] . $_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ?: ''));

					if ($checksum != $key) {
						if (++$customer['login_attempts'] < 3) {
							database::query(
								"update ". DB_TABLE_PREFIX ."customers
								set login_attempts = login_attempts + 1
								where id = ". (int)$customer['id'] ."
								limit 1;"
							);
						} else {
							database::query(
								"update ". DB_TABLE_PREFIX ."customers
								set login_attempts = 0,
								blocked_until = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
								where id = ". (int)$customer['id'] ."
								limit 1;"
							);
						}

						throw new Exception('Invalid checksum for cookie');
					}

					self::load($customer['id']);
					session::$data['security.timestamp'] = time();

					database::query(
						"update ". DB_TABLE_PREFIX ."customers
						set last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
							last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
							last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
							last_login = '". date('Y-m-d H:i:s') ."',
							login_attempts = 0,
							total_logins = total_logins + 1
						where id = ". (int)$customer['id'] ."
						limit 1;"
					);

				} catch (Exception $e) {
					header('Set-Cookie: customer_remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
				}
			}

			if (!empty(self::$data['id'])) {

				try {

					$customer = database::query(
						"select * from ". DB_TABLE_PREFIX ."customers
						where id = ". (int)self::$data['id'] ."
						limit 1;"
					)->fetch();

					if (!$customer) {
						throw new Exception(t('error_your_account_has_been_removed', 'Your account has been removed'));
					}

					if (!$customer['status']) {
						throw new Exception(t('error_your_account_is_disabled', 'Your account is disabled'));
					}

					if (!empty($customer['sessions_expiry'])) {
						if (!isset(session::$data['customer_security_timestamp']) || session::$data['customer_security_timestamp'] < strtotime($customer['sessions_expiry'])) {
							throw new Exception(t('error_session_expired_due_to_account_changes', 'Session expired due to changes in the account'));
						}
					}

					session::$data['customer'] = array_replace(session::$data['customer'], array_intersect_key($customer, session::$data['customer']));

				} catch (Exception $e) {

					self::reset();

					if (!empty($_COOKIE['customer_remember_me'])) {
						header('Set-Cookie: customer_remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax');
					}

					notices::add('errors', $e->getMessage());

					redirect(document::ilink('f:account/sign_in'));
					exit;
				}
			}

			// Collect scraps
			if (route::$selected['endpoint'] == 'frontend' && file_get_contents('php://input')) {
				foreach ([
					'#^(given|first)[ _-]?name$#i' => 'firstname',
					'#^(family|sur|last)[ _-]?name$#i' => 'lastname',
					'#^(address[ _-]?1|street[ _-]?(address)?)$#i' => 'address1',
					'#^(post|postal|zip)[ _-]?code$#i' => 'postcode',
					'#^city|town|locality$#i' => 'city',
					'#^country[ _-]?code$#i' => 'country_code',
					'#^email[ _-]?(address)?$#i' => 'email',
					'#^phone[ _-]?(no|number)?$#i' => 'phone',
					'#^lon(gitude)?$#i' => 'longitude',
					'#^lat(itude)?$#i' => 'latitude',
				] as $pattern => $field) {

					foreach ($_POST as $key => $value) {
						if (preg_match($pattern, $key)) {
							self::$scraps[$field] = $value;
						}
					}
				}
			}

			// Use scraps for empty fields
			foreach (self::$scraps as $key => $value) {
				if (empty(self::$data[$key])) {
					self::$data[$key] = $value;
				}
			}

			self::identify();

			document::$jsenv['customer'] = [
				'id' => &self::$data['id'],
				'country_code' => &self::$data['country_code'],
				'display_prices_including_tax' => &self::$data['display_prices_including_tax'],
			];

			event::register('after_capture', [__CLASS__, 'after_capture']);
		}

		public static function after_capture() {

			// Load regional settings screen
			if (route::$selected['endpoint'] == 'frontend') {
				if (settings::get('regional_settings_screen')) {
					if (empty(session::$data['skip_regional_settings_screen'])) {

						if (!customer::check_login()) {
							functions::draw_lightbox(document::ilink('regional_settings'));
						}
						session::$data['skip_regional_settings_screen'] = true;
					}
				}
			}
		}

		######################################################################

		public static function identify() {

			// Build list of supported countries
			$countries = database::query(
				"select iso_code_2 from ". DB_TABLE_PREFIX ."countries
				where status;"
			)->fetch_all('iso_code_2');

			// Unset non supported country
			if (!in_array(self::$data['country_code'], $countries)) {
				self::$data['country_code'] = '';
			}

			// Set country from URI
			if (!empty($_GET['country'])) {
				if (in_array($_GET['country'], $countries)) {
					self::$data['country_code'] = $_GET['country'];
				}
			}

			// Set country from cookie
			if (empty(self::$data['country_code'])) {
				if (!empty($_COOKIE['country_code']) && in_array($_COOKIE['country_code'], $countries)) {
					self::$data['country_code'] = $_COOKIE['country_code'];
				}
			}

			// Get country from HTTP header (CloudFlare)
			if (empty(self::$data['country_code'])) {
				if (!empty($_SERVER['HTTP_CF_IPCOUNTRY']) && in_array($_SERVER['HTTP_CF_IPCOUNTRY'], $countries)) {
					self::$data['country_code'] = $_SERVER['HTTP_CF_IPCOUNTRY'];
				}
			}

			// Get country from TLD
			if (empty(self::$data['country_code'])) {
				if (preg_match('#\.([a-z]{2})$#', $_SERVER['HTTP_HOST'], $matches)) {

					$matches[1] = strtr(strtoupper($matches[1]), [
						'UK' => 'GB', // ccTLD .uk is not a country
						'SU' => 'RU', // ccTLD .su is not a country
					]);

					$country = database::query(
						"select * from ". DB_TABLE_PREFIX ."countries
						where status
						and iso_code_2 = '". database::input(strtoupper($matches[1])) ."'
						limit 1;"
					)->fetch();

					if (!empty($country['iso_code_2'])) {
						self::$data['country_code'] = $country['iso_code_2'];
					}
				}
			}

			// Get country from browser locale
			if (empty(self::$data['country_code'])) {
				if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match('#(^[a-z]{2}-([a-z]{2}))#i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {
					if (!empty($matches[2]) && in_array(strtoupper($matches[2]), $countries)) {
						self::$data['country_code'] = strtoupper($matches[2]);
					}
				}
			}

			// Set default country
			if (empty(self::$data['country_code']) && in_array(settings::get('default_country_code'), $countries)) {
				self::$data['country_code'] = settings::get('default_country_code');
			}

			// Set store country
			if (empty(self::$data['country_code']) && in_array(settings::get('store_country_code'), $countries)) {
				self::$data['country_code'] = settings::get('store_country_code');
			}

			// Set first country in list
			if (empty(self::$data['country_code'])) {
				self::$data['country_code'] = $countries[0]['iso_code_2'];
			}

			// Set zone from cookie
			if (empty(self::$data['zone_code'])) {
				if (!empty($_COOKIE['zone_code'])) {
					self::$data['zone_code'] = $_COOKIE['zone_code'];
				}
			}

			// Set default zone
			if (empty(self::$data['zone_code']) && self::$data['country_code'] == settings::get('default_country_code')) {
				self::$data['zone_code'] = settings::get('default_zone_code');
			}

			// Set store zone
			if (empty(self::$data['zone_code']) && self::$data['country_code'] == settings::get('store_country_code')) {
				self::$data['zone_code'] = settings::get('store_zone_code');
			}

			// Unset zone if not in country
			if (!empty(self::$data['zone_code']) && empty(reference::country(self::$data['country_code'])->zones[self::$data['zone_code']])) {
				self::$data['zone_code'] = '';
			}

			// Set first zone in country
			if (empty(self::$data['zone_code']) && !empty(reference::country(self::$data['country_code'])->zones)) {
				self::$data['zone_code'] = array_keys(reference::country(self::$data['country_code'])->zones)[0];
			}

			// Set shipping country if empty
			if (empty(self::$data['shipping_address']['country_code'])) {
				self::$data['shipping_address']['country_code'] = self::$data['country_code'];
				self::$data['shipping_address']['zone_code'] = self::$data['zone_code'];
			}

			// Unset zone if not in country
			if (!isset(reference::country(self::$data['shipping_address']['country_code'])->zones[self::$data['shipping_address']['zone_code']])) {
				self::$data['shipping_address']['zone_code'] = '';
			}

			// Set tax from cookie
			if (!isset(self::$data['display_prices_including_tax']) || self::$data['display_prices_including_tax'] === null) {
				if (isset($_COOKIE['display_prices_including_tax'])) {
					self::$data['display_prices_including_tax'] = !empty($_COOKIE['display_prices_including_tax']) ? 1 : 0;
				}
			}

			// Set default tax
			if (!isset(self::$data['display_prices_including_tax']) || self::$data['display_prices_including_tax'] === null) {
				self::$data['display_prices_including_tax'] = settings::get('default_display_prices_including_tax') ? 1 : 0;
			}
		}

		public static function reset() {

			$customer = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."customers;"
			)->each(function($field) use (&$customer) {
				$customer[$field['Field']] = database::create_variable($field);
			});

			$customer['display_prices_including_tax'] = null;

			session::$data['customer'] = $customer;
		}

		public static function load($id) {

			self::reset();

			$customer = database::query(
				"select * from ". DB_TABLE_PREFIX ."customers
				where id = ". (int)$id ."
				limit 1;"
			)->fetch(function(&$customer){

				foreach ($customer as $field => $value) {
					if (preg_match('#^shipping_(.*)$#', $field, $matches)) {
						unset($customer['shipping_'.$matches[1]]);
						$customer['shipping_address'][$matches[1]] = $value;
					}
				}
			});

			if ($customer) {
				session::$data['customer'] = array_replace(session::$data['customer'], array_intersect_key($customer, session::$data['customer']));
			}

			if (!empty(self::$data['language_code']) && self::$data['language_code'] == language::$selected['code']) {
				language::set(self::$data['language_code']);
			}
		}

		public static function require_login() {

			if (!self::check_login()) {
				notices::add('warnings', t('warning_must_login_page', 'You must be logged in to view the page.'));
				$redirect_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
				header('Location: ' . document::ilink('f:account/sign_in', ['redirect_url' => $redirect_url]));
				exit;
			}
		}

		public static function check_login() {
			if (!empty(self::$data['id'])) return true;
		}

		public static function log($event) {

			$event = [
				'session_id' => isset($event['session_id']) ? $event['session_id'] : session::$data['id'],
				'customer_id' => isset($event['customer_id']) ? $event['customer_id'] : self::$data['id'],
				'type' => isset($event['type']) ? $event['type'] : 'unknown',
				'description' => isset($event['description']) ? $event['description'] : null,
				'data' => !empty($event['data']) ? json_encode($event['data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
				'url' => isset($event['url']) ? $event['url'] : document::link(),
				'ip_address' => isset($event['ip_address']) ? $event['ip_address'] : $_SERVER['REMOTE_ADDR'],
				'hostname' => isset($event['hostname']) ? $event['hostname'] : gethostbyaddr(isset($event['ip_address']) ? $event['ip_address'] : $_SERVER['REMOTE_ADDR']),
				'user_agent' => isset($event['user_agent']) ? $event['user_agent'] : $_SERVER['HTTP_USER_AGENT'],
				'expires_at' => isset($event['expires_at']) ? date('Y-m-d H:i:s', strtotime($event['expires_at'])) : date('Y-m-d H:i:s', strtotime('+3 months')),
				'created_at' => date('Y-m-d H:i:s'),
			];

			$event = array_filter($event, function($val){
				return $val != null;
			});

			if (preg_match('#bot|crawl#', $event['hostname']) || preg_match('#bot|crawl#', $event['user_agent'])) {
				return;
			}

			database::query(
				"insert into ". DB_TABLE_PREFIX ."customers_activity
				(`". implode("`, `", database::input(array_keys($event))) ."`)
				values ('". implode("', '", database::input($event)) ."');"
			);
		}
	}
