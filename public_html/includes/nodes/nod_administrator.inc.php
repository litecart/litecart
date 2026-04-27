<?php

	class administrator {

		public static $data;

		public static function init() {

			if (empty(session::$data['administrator']) || !is_array(session::$data['administrator'])) {
				self::reset();
			}

			// Bind administrator to session
			self::$data = &session::$data['administrator'];

			// Login remembered administrator automatically (HMAC-based token)
			if (!self::$data['id'] && !empty($_COOKIE['remember_me']) && !$_POST && defined('HMAC_KEY_REMEMBER_ME')) {

				try {

					// Decode token to get administrator ID
					$decoded = base64_decode($_COOKIE['remember_me'], true);
					$token = $decoded ? json_decode($decoded, true) : null;
					if (!is_array($token) || empty($token['id'])) {
						throw new Exception('Invalid or legacy cookie format');
					}

					$administrator = database::query(
						"select * from ". DB_TABLE_PREFIX ."administrators
						where id = ". (int)$token['id'] ."
						and status
						and (valid_from is null or valid_from < '". date('Y-m-d H:i:s') ."')
						and (valid_to is null or valid_to > '". date('Y-m-d H:i:s') ."')
						limit 1;"
					)->fetch();

					if (!$administrator) {
						throw new Exception('Invalid administrator or account removed');
					}

					// Verify HMAC with actual password hash
					$verified_id = f::token_verify_remember($_COOKIE['remember_me'], $administrator['password_hash']);
					if ($verified_id === false) {

						if (++$administrator['login_attempts'] < 3) {
							database::query(
								"update ". DB_TABLE_PREFIX ."administrators
								set login_attempts = login_attempts + 1
								where id = ". (int)$administrator['id'] ."
								limit 1;"
							);
						} else {
							database::query(
								"update ". DB_TABLE_PREFIX ."administrators
								set login_attempts = 0,
								valid_from = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
								where id = ". (int)$administrator['id'] ."
								limit 1;"
							);
						}

						throw new Exception('Invalid token signature');
					}

					self::load($administrator['id']);

					database::query(
						"update ". DB_TABLE_PREFIX ."administrators
						set last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
							last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
							last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
							last_login = '". date('Y-m-d H:i:s') ."',
							login_attempts = 0,
							total_logins = total_logins + 1
						where id = ". (int)$administrator['id'] ."
						limit 1;"
					);

				} catch (Exception $e) {
					header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
				}
			}

			if (!empty(self::$data['id'])) {

				$administrator = database::query(
					"select * from ". DB_TABLE_PREFIX ."administrators
					where id = ". (int)self::$data['id'] ."
					limit 1;"
				)->fetch();

				if (!$administrator) {
					self::reset();
					die('The account has been removed');
				}

				if (!$administrator['status']) {
					if (!empty($_COOKIE['remember_me'])) {
						header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax');
					}
					self::reset();
					die('Your account is disabled');
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."administrators
					set last_active = '". date('Y-m-d H:i:s') ."'
					where id = ". (int)self::$data['id'] ."
					limit 1;"
				);

				if (!empty($administrator['sessions_expiry'])) {
					if (!isset(session::$data['administrator_security_timestamp']) || session::$data['administrator_security_timestamp'] < strtotime($administrator['sessions_expiry'])) {
						self::reset();
						notices::add('errors', t('error_session_expired_due_to_account_changes', 'Session expired due to changes in the account'));
						redirect(document::ilink('b:login'), 302);
						exit;
					}
				}
			}
		}

		## Node specific methods

		public static function reset() {

			$administrator = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."administrators;"
			)->each(function($field) use (&$administrator) {
				$administrator[$field['Field']] = database::create_variable($field);
			});

			$administrator['apps'] = [];
			$administrator['widgets'] = [];

			session::$data['administrator'] = $administrator;
		}

		public static function load($administrator_id) {

			self::reset();

			$administrator = database::query(
				"select * from ". DB_TABLE_PREFIX ."administrators
				where id = ". (int)$administrator_id ."
				limit 1;"
			)->fetch();

			if (!$administrator) {
				throw new Exception('No administrator found');
			}

			$administrator['apps'] = $administrator['apps'] ? json_decode($administrator['apps'], true) : [];
			$administrator['widgets'] = $administrator['widgets'] ? json_decode($administrator['widgets'], true) : [];

			session::$data['administrator'] = $administrator;
		}

		public static function require_login() {

			if (!self::check_login()) {
				redirect(document::ilink('b:login', ['redirect_url' => $_SERVER['REQUEST_URI']]), 302);
				exit;
			}

			if (!empty(session::$data['security_verification'])) {
				if (!in_array(route::$selected['resource'], ['b:login', 'b:logout', 'b:verify'])) {
					redirect(document::ilink('b:verify', ['redirect_url' => $_SERVER['REQUEST_URI']]), 302);
					exit;
				}
			}
		}

		public static function check_login() {
			return !empty(self::$data['id']);
		}
	}
