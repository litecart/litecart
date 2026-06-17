<?php

	class security {
		public static $data;

		public static function init() {

			if (empty(session::$data['security'])) {
				session::$data['security'] = [];
			}

			self::$data = &session::$data['security'];

			// Set default values for security data
			foreach ([
				'is_bot' => false,
				'is_human' => false,
				'is_whitelisted' => null,
				'last_requests' => [],
				'caught_in_honeypot' => false,
				'failed_authentications' => 0,
				'404_hits' => 0,
				'page_loads' => 0,
			] as $key => $default_value) {
				if (!isset(self::$data[$key])) {
					self::$data[$key] = $default_value;
				}
			}

			// Do we need to check if human?
			if (empty(self::$data['is_human']) && empty(self::$data['is_whitelisted'])) {
				if (!preg_match('#(are_you_human|csp_report|account/sign_out|'. preg_quote(BACKEND_ALIAS, '#')	.'/(logout|manifest))#', route::$request)) {

					try {

						// Check if client has been caught in a honeypot
						if (!empty(self::$data['stuck_in_honeypot'])) {
							throw new Exception('Caught in a honeypot');
						}

						// Check if burst of requests in the session
						if (!empty(self::$data['last_requests'])) {

							$measure_breakpoint = strtotime('-10 seconds');
							$num_requests = 0;

							foreach (self::$data['last_requests'] as $request) {
								if (strtotime($request['timestamp']) > $measure_breakpoint) {
									$num_requests++;
								}
							}

							if ($num_requests > 5) {
								throw new Exception('Burst of requests in the session');
							}
						}

						// Check if we have too many failed authentications in the session
						if (isset(self::$data['failed_authentications']) && self::$data['failed_authentications'] >= 5) {
							throw new Exception('Many failed authentications');
						}

						// Check if we have too many 404 hits in the session
						if (isset(self::$data['404_hits']) && self::$data['404_hits'] >= 10) {
							throw new Exception('Suspiciously large amount of 404 hits');
						}

						// Check if we have a large amount of page loads in the session
						if (isset(self::$data['page_loads']) && self::$data['page_loads'] >= 100) {
							throw new Exception('Suspiciously large amount of page loads');
						}

						// Check if client IP address lacks a hostname
						if ($_SERVER['REMOTE_ADDR'] == gethostbyaddr($_SERVER['REMOTE_ADDR'])) {
							throw new Exception('IP address without a hostname');
						}

						// All good for now

					} catch (Exception $e) {
						self::require_human();
					}
				}
			}

			// CSRF protection for state-changing requests
			if ($_SERVER['SERVER_SOFTWARE'] != 'CLI' && !in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'OPTIONS'])) {

				// Excluded paths (payment gateway callbacks, MCP endpoints)
				$csrf_excluded_paths = ['checkout/verify', 'mcp'];
				$csrf_skip = false;
				$request_path = strtok($_SERVER['REQUEST_URI'], '?');
				foreach ($csrf_excluded_paths as $path) {
					if (preg_match('#/' . preg_quote($path, '#') . '(?:/|$)#', $request_path)) {
						$csrf_skip = true;
						break;
					}
				}

				if (!$csrf_skip) {
					$submitted_token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
					if (!hash_equals(security::csrf_token(), $submitted_token)) {
						http_response_code(403);
						if (is_ajax_request()) {
							header('Content-Type: application/json');
							echo f::format_json(['error' => 'CSRF token mismatch. Please reload the page and try again.']);
						} else {
							echo implode(PHP_EOL, [
								'<h1>403 Forbidden</h1>',
								'<p>CSRF token mismatch. Please <a href="javascript:history.back()">go back</a> and try again.</p>'
							]);
						}
						exit;
					}
				}
			}
		}

		public static function is_human() {
			return !empty(self::$data['is_human']);
		}

		public static function require_human() {

			if (!empty(self::$data['is_human']) || !empty(self::$data['is_whitelisted'])) {
				return;
			}

			if (!preg_match('#(are_you_human|csp_report|account/sign_out|'. preg_quote(BACKEND_ALIAS, '#')	.'/(logout|manifest))#', route::$request)) {
				return;
			}

			$remote_hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);

			foreach ([
				'.applebot.apple.com',
				'.amazonbot.amazon.com',
				'.bytedance.com',
				'.facebook.com',
				'.fbsv.net',
				'.googlebot.com',
				'.google.com',
				'.huawei.com',
				'.search.msn.com',
				'.slurp.yahoo.com',
				'.duckduckgo.com',
				'.bing.com',
				'.yandex.com',
				'.yandex.ru',
				'.yandex.net',
				'.baidu.com',
				'.baidu.jp',
				'.x.com',

			] as $bot_hostname) {
				if (str_ends_with($remote_hostname, $bot_hostname)) {
					session::$data['is_bot'] = true;
					self::$data['is_whitelisted'] = true;
					break;
				}
			}

			if (!empty(self::$data['is_whitelisted'])) return;

			redirect(document::ilink('f:are_you_human', ['redirect_url' => $_SERVER['REQUEST_URI']]));
			exit;
		}

		public static function csrf_token(): string {
			if (empty(self::$data['csrf_token'])) {
				self::$data['csrf_token'] = bin2hex(random_bytes(32));
			}
			return self::$data['csrf_token'];
		}

		public static function rotate_csrf_token(): string {
			self::$data['csrf_token'] = bin2hex(random_bytes(32));
			return self::$data['csrf_token'];
		}
	}
