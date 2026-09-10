<?php

	document::$layout = 'blank';

	document::$title[] = t('title_login', 'Login');
	document::$head_tags[] = '<meta name="viewport" content="width=device-width, initial-scale=1">';

	if (!session_name()) {
		notices::add('notices', t('error_missing_session_cookie', 'We failed to identify your browser session. Make sure your browser has cookies enabled or try another browser.'));
	}

	if (isset($_POST['login'])) {

		try {

			if (!empty($_COOKIE['remember_me'])) {
				header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
			}

			// Rate limit checking
			if (database::query(
				"select count(*) as num_attempts from ". DB_PREFIX ."rate_limiting
				where action = 'login_failed'
				and ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'
				and created_at >= '". date('Y-m-d H:i:s', strtotime('-5 minutes')) ."'"
			)->fetch('num_attempts') > 3) {
				throw new Exception(t('error_too_many_attempts', 'Too many failed attempts. Please try again later.'));
			}

			if (empty($_POST['username'])) {
				throw new Exception(t('error_must_provide_username_or_email', 'You must provide your username or email address'));
			}

			if (empty($_POST['password'])) {
				throw new Exception(t('error_must_provide_password', 'You must provide a password'));
			}

			$administrator = database::query(
				"select * from ". DB_PREFIX ."administrators
				where username = '". database::input(strtolower($_POST['username'])) ."'
				or email = '". database::input(strtolower($_POST['username'])) ."'
				limit 1;"
			)->fetch(function(&$administrator){
				$administrator['known_ips'] = f::string_split($administrator['known_ips']);
				$administrator['known_fingerprints'] = f::string_split($administrator['known_fingerprints']);
			});

			if (!$administrator) {
				throw new Exception(t('error_administrator_not_found', 'The administrator could not be found in our database'));
			}

			if (empty($administrator['status'])) {
				throw new Exception(t('error_administrator_account_disabled', 'The administrator account is disabled'));
			}

			if (!empty($administrator['valid_from']) && date('Y-m-d H:i:s') < $administrator['valid_from']) {
				throw new Exception(strtr(t('error_account_is_blocked', 'The account is blocked until {datetime}'), [
					'{datetime}' => f::datetime_format('datetime', $administrator['valid_from'])
				]));
			}

			if (!empty($administrator['valid_to']) && date('Y-m-d H:i:s') > $administrator['valid_to']) {
				throw new Exception(strtr(t('error_account_expired', 'The account expired {datetime}'), [
					'{datetime}' => f::datetime_format('datetime', $administrator['valid_to'])
				]));
			}

			if (!password_verify($_POST['password'], $administrator['password_hash'])) {

				database::insert('rate_limiting', [
					'action' => 'login_failed',
					'ip_address' => $_SERVER['REMOTE_ADDR'],
					'hostname' => reverse_dns($_SERVER['REMOTE_ADDR']),
					'user_agent' => $_SERVER['HTTP_USER_AGENT'],
					'created_at' => date('Y-m-d H:i:s'),
				]);

				// Notify the administrator on first rate limiting hit
				if (!empty($administrator['email'])) {
					$window_start = date('Y-m-d H:i:s', strtotime('-5 minutes'));
					$failed_count = (int)database::query(
						"select count(*) as num_attempts from ". DB_PREFIX ."rate_limiting
						where action = 'login_failed'
						and created_at >= '". $window_start ."'"
					)->fetch('num_attempts');

					$notified = (int)database::query(
						"select count(*) as num_sent from ". DB_PREFIX ."rate_limiting
						where action = 'login_failed_notified'
						and created_at >= '". $window_start ."'"
					)->fetch('num_sent');

					if ($failed_count >= 3 && !$notified) {

						database::insert('rate_limiting', [
							'action' => 'login_failed_notified',
							'ip_address' => $_SERVER['REMOTE_ADDR'],
							'hostname' => reverse_dns($_SERVER['REMOTE_ADDR']),
							'user_agent' => $_SERVER['HTTP_USER_AGENT'],
							'created_at' => date('Y-m-d H:i:s'),
						]);

						$aliases = [
							'{store_name}' => settings::get('store_name'),
							'{store_link}' => document::ilink(''),
							'{username}' => $administrator['username'],
							'{ip_address}' => $_SERVER['REMOTE_ADDR'],
							'{hostname}' => reverse_dns($_SERVER['REMOTE_ADDR']),
							'{user_agent}' => $_SERVER['HTTP_USER_AGENT'],
						];

						$subject = t('title_administrator_account_blocked', 'Administrator Account Blocked');
						$message = strtr(t('administrator_account_blocked:email_body', implode("\r\n", [
							'Your administrator account {username} has been temporarily blocked because of too many invalid login attempts.',
							'',
							'Client: {ip_address} ({hostname})',
							'{user_agent}',
							'',
							'{site_name}',
							'{site_link}',
						])), $aliases);

						(new ent_email())
							->add_recipient($administrator['email'], $administrator['username'])
							->set_subject($subject)
							->add_body($message)
							->send();
					}
				}

				if (++$administrator['login_attempts'] < 3) {

					database::query(
						"update ". DB_PREFIX ."administrators
						set login_attempts = login_attempts + 1
						where id = ". (int)$administrator['id'] ."
						limit 1;"
					);

					throw new Exception(t('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'));

				} else {

					database::query(
						"update ". DB_PREFIX ."administrators
						set login_attempts = 0,
						valid_from = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
						where id = ". (int)$administrator['id'] ."
						limit 1;"
					);

					if (!empty($administrator['email'])) {

						$aliases = [
							'{store_name}' => settings::get('store_name'),
							'{store_link}' => document::ilink(''),
							'{username}' => $administrator['username'],
							'{expires}' => date('Y-m-d H:i:00', strtotime('+15 minutes')),
							'{ip_address}' => $_SERVER['REMOTE_ADDR'],
							'{hostname}' => reverse_dns($_SERVER['REMOTE_ADDR']),
							'{user_agent}' => $_SERVER['HTTP_USER_AGENT'],
						];

						$subject = t('title_administrator_account_blocked', 'Administrator Account Blocked');
						$message = strtr(t('administrator_account_blocked:email_body', implode("\r\n", [
							'Your administrator account {username} has been blocked until {expires} because of too many invalid login attempts.',
							'',
							'Client: {ip_address} ({hostname})',
							'{user_agent}',
							'',
							'{store_name}',
							'{store_link}',
						])), $aliases);

						(new ent_email())
							->add_recipient($administrator['email'], $administrator['username'])
							->set_subject($subject)
							->add_body($message)
							->send();
					}

					throw new Exception(strtr(t('error_account_has_been_blocked', 'This account has been temporary blocked {n} minutes'), [
						'{n}' => 15
					]));
				}

				throw new Exception(t('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'));
			}

			if (password_needs_rehash($administrator['password_hash'], PASSWORD_DEFAULT)) {
				database::query(
					"update ". DB_PREFIX ."administrators
					set password_hash = '". database::input(password_hash($_POST['password'], PASSWORD_DEFAULT)) ."'
					where id = ". (int)$administrator['id'] ."
					limit 1;"
				);
			}

			if (!empty($administrator['last_ip_address']) && $administrator['last_ip_address'] != $_SERVER['REMOTE_ADDR']) {
				notices::add('warnings', strtr(t('warning_account_previously_used_by_another_ip', 'Your account was previously used by another IP address {ip_address} ({hostname}). If this was not you then your login credentials might be compromised.'), [
					'{username}' => $administrator['username'],
					'{ip_address}' => $administrator['last_ip_address'],
					'{hostname}' => $administrator['last_hostname'],
				]));
			}

			if (!empty(session::$data['fingerprint'])) {
				array_unshift($administrator['known_fingerprints'], session::$data['fingerprint']);
				$administrator['known_fingerprints'] = array_slice(array_unique($administrator['known_fingerprints']), 0, 10);
			}

			database::query(
				"update ". DB_PREFIX ."administrators
				set known_fingerprints = '". database::input(implode(',', $administrator['known_fingerprints'])) ."',
					last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
					last_hostname = '". database::input(reverse_dns($_SERVER['REMOTE_ADDR'])) ."',
					last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
					login_attempts = 0,
					total_logins = total_logins + 1,
					last_login = '". date('Y-m-d H:i:s') ."'
				where id = ". (int)$administrator['id'] ."
				limit 1;"
			);

			administrator::load($administrator['id']);

			database::query(
				"delete from ". DB_PREFIX ."rate_limiting
				where action = 'login_failed'
				and ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'"
			);

			session::$data['security.administrator']['timestamp'] = time();
			session::regenerate_id();
			security::rotate_csrf_token();

			unset(session::$data['security.administrator']['verification']);

			// TOTP (opt-in per administrator). When enrolled, always challenge —
			// independent of the known-IP check below. Email OTP remains the
			// fallback for admins who haven't enrolled.
			if (!empty($administrator['totp_secret'])) {

				session::$data['security.administrator']['verification'] = [
					'type' => 'totp',
					'code' => random_int(100000, 999999), // Not used for TOTP, but required for the verification form.
					'expires' => strtotime('+5 minutes'),
					'attempts' => 0,
				];

				if (!empty($_POST['redirect_url'])) {
					redirect(document::ilink('verify', ['redirect_url' => $_POST['redirect_url']]));
				} else {
					redirect(document::ilink('verify'));
				}

				exit;
			}

			$is_known_ip = false;
			$is_known_range = false;

			foreach ($administrator['known_ips'] as $known_ip) {
				if ($_SERVER['REMOTE_ADDR'] == $known_ip) {
					$is_known_ip = true;
					$is_known_range = true;
					break;
				} else if (preg_replace('#[0-9]{1,3}\.[0-9]{1,3}$#', '', $_SERVER['REMOTE_ADDR']) == preg_replace('#[0-9]{1,3}\.[0-9]{1,3}$#', '', $known_ip)) {
					$is_known_range = true;
					break;
				}
			}

			if (!$is_known_ip) {
				if ($is_known_range) {

					array_unshift($administrator['known_ips'], $_SERVER['REMOTE_ADDR']);
					$administrator['known_ips'] = array_slice(array_unique($administrator['known_ips']), 0, 10);

					database::query(
						"update ". DB_PREFIX ."administrators
						set known_ips = '". database::input(implode(',', $administrator['known_ips'])) ."'
						where id = ". (int)$administrator['id'] ."
						limit 1;"
					);

				} else {

					if (!empty($administrator['two_factor_auth']) && !empty($administrator['email'])) {

						session::$data['security.administrator']['verification'] = [
							'type' => 'eotp',
							'code' => random_int(100000, 999999),
							'expires' => strtotime('+15 minutes'),
							'attempts' => 0,
						];

						(new ent_email())
							->add_recipient($administrator['email'])
							->set_subject(t('title_verification_code', 'Verification Code'))
							->add_body(strtr(t('email_verification_code', 'Verification code: {code}'), [
								'{code}' => session::$data['security.administrator']['verification']['code']
							]))
							->send();

						notices::add('notices', t('notice_verification_code_sent_via_email', 'A verification code was sent via email'));

						if (!empty($_POST['redirect_url'])) {
							redirect(document::ilink('verify', ['redirect_url' => $_POST['redirect_url']]));
						} else {
							redirect(document::ilink('verify'));
						}

						exit;
					}
				}
			}

			if (!empty($_POST['remember_me']) && defined('HMAC_KEY_REMEMBER_ME')) {
				$token = f::token_create_remember($administrator['username'], $administrator['password_hash']);
				header('Set-Cookie: remember_me='. $token .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+30 days')) .'; HttpOnly; SameSite=Lax' . (!empty($_SERVER['HTTPS']) ? '; Secure' : ''), false);
			} else if (!empty($_COOKIE['remember_me'])) {
				header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
			}

			if (!empty($_POST['redirect_url'])) {
				$redirect_url = new type_url($_POST['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink('b:');
			}

			notices::add('success', strtr(t('success_now_logged_in_as', 'You are now logged in as {username}'), [
				'{username}' => administrator::$data['username']
			]));

			redirect($redirect_url, 303);
			exit;

		} catch (Exception $e) {
			security::$data['failed_authentications']++;
			http_response_code(401); // Troublesome with HTTP Auth Basic (e.g. .htpasswd)
			notices::add('errors', $e->getMessage());
		}
	}

?>
<style>
body {
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	width: 100vw;
	min-height: 100vh;
}

.loader-wrapper {
	display: none;
	position: absolute !important;
	top: 50%;
	left: 50%;
	margin-top: -128px;
	margin-inline-start: -128px;
}

#box-login {
	width: 320px;
	margin: auto;
	border-radius: var(--border-radius);
}

#box-login .card-header a {
	display: block;
}

#box-login .card-header img {
	margin: 0 auto;
	max-width: 200px;
	max-height: 100px;
}

.btn-unstyled {
	box-shadow: none;
	background: transparent;
	border: none;
	color: inherit;
}

.card-footer .row {
	align-items: center;
}
.btn-unstyled span {
	text-decoration: none;
	background-image: linear-gradient(currentColor, currentColor);
	background-size: 0% 1px;
	background-repeat: no-repeat;
	background-position: 0 100%;
	transition: background-size .25s ease, color .25s ease;
}
.btn-unstyled:hover span,
.btn-unstyled:focus-visible span {
	background-size: 100% 1px;
}

.login-brand {
	display: flex;
	justify-content: center;
	align-items: center;
	margin: 1.5rem 0;
	text-align: center;
}
.login-brand a {
	display: inline-block;
	text-decoration: none;
	transition: transform .25s ease, filter .25s ease;
}
.login-brand a:hover {
	transform: scale(1.05);
}
.login-brand img {
	display: block;
	height: 1.5rem;
	width: auto;
	transition: all linear 150ms;
	filter: drop-shadow(0px 0 0 rgba(0,0,0,0)) drop-shadow(0 0 0 rgba(0,0,0,0));
}
.login-brand:hover img {
	filter: drop-shadow(0px 5px 2px rgba(0,0,0,.15)) drop-shadow(0 1px 2px rgba(0,0,0,.025));
}
.login-brand .brand-fallback {
	font-size: 1.1rem;
	font-weight: 700;
	letter-spacing: -.02em;
	color: var(--login-text);
	filter: drop-shadow(0 2px 6px rgba(0, 0, 0, .35));
}

.theme-toggle {
	position: absolute;
	top: 1.5em;
	inset-inline-end: 1.5em;
	justify-self: end;
}
</style>

<div class="loader-wrapper">
	<div class="loader" style="width: 256px; height: 256px;"></div>
</div>

<?php echo f::form_begin('login_form', 'post'); ?>
	<?php echo f::form_input_hidden('login', 'true'); ?>
	<?php echo f::form_input_hidden('redirect_url', true); ?>

	<article id="box-login" class="card">
		<div class="card-header">
			<div class="card-title">
				<?php echo t('title_sign_in', 'Sign In'); ?>
			</div>
			<div class="theme-toggle">
				<?php echo f::form_toggle('theme', ['light' => f::draw_fonticon('icon-sun'), 'dark' => f::draw_fonticon('icon-moon')], (!empty($_COOKIE['theme']) && in_array($_COOKIE['theme'], ['light', 'dark'])) ? $_COOKIE['theme'] : 'light'); ?>
			</div>
		</div>

		<div class="card-body">

			{{notices}}

			<label class="form-group">
				<?php echo f::form_input_username('username', true, ['placeholder' => t('title_username_or_email_address', 'Username or Email Address'), 'autocomplete' => 'username']); ?>
				<div class="form-label"></div>
			</label>

			<label class="form-group">
				<?php echo f::form_input_password('password', '', ['placeholder' => t('title_password', 'Password') , 'autocomplete' => 'current-password']); ?>
				<div class="form-label"></div>
			</label>

			<div class="form-group">
				<?php echo f::form_checkbox('remember_me', ['1', t('title_remember_me', 'Remember Me')], true); ?>
			</div>
		</div>

		<div class="card-footer">
			<div class="row">
				<div class="col-6 text-start">
					<a class="btn btn-unstyled" href="<?php echo document::href_ilink('f:'); ?>">
						<?php echo f::draw_fonticon('icon-chevron-left'); ?> <span><?php echo t('title_frontend', 'Frontend'); ?></span>
					</a>
				</div>
				<div class="col-6 text-end">
					<?php echo f::form_button('login', t('title_login', 'Login'), 'submit'); ?>
				</div>
			</div>
		</div>

	</article>

<?php echo f::form_end(); ?>

<div class="login-brand">
	<a href="https://www.litecart.net/" aria-label="LiteCart">
		<img src="<?php echo document::href_rlink('app://backend/template/images/logotype.svg'); ?>" alt="<?php echo f::escape_html(settings::get('store_name')); ?>">
	</a>
</div>

<script>
	if (!$('input[name="username"]').val()) {
		$('input[name="username"]').trigger('focus');
	} else {
		$('input[name="password"]').trigger('focus');
	}

	$('form[name="login_form"]').submit(function(e) {
		e.preventDefault();
		let form = this;

		$('#box-login').slideUp(500, function() {
			$('.loader-wrapper').fadeIn(500, function() {
				form.submit();
			});
		});
	});
</script>