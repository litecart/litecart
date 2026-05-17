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

			if (empty($_POST['username'])) {
				throw new Exception(t('error_must_provide_username_or_email', 'You must provide your username or email address'));
			}

			if (empty($_POST['password'])) {
				throw new Exception(t('error_must_provide_password', 'You must provide a password'));
			}

			$administrator = database::query(
				"select * from ". DB_TABLE_PREFIX ."administrators
				where username = '". database::input(strtolower($_POST['username'])) ."'
				or email = '". database::input(strtolower($_POST['username'])) ."'
				limit 1;"
			)->fetch();

			if (!$administrator) {
				throw new Exception(t('error_administrator_not_found', 'The administrator could not be found in our database'));
			}

			$administrator['known_ips'] = f::string_split($administrator['known_ips']);
			$administrator['known_fingerprints'] = f::string_split($administrator['known_fingerprints']);

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

				if (++$administrator['login_attempts'] < 3) {

					database::query(
						"update ". DB_TABLE_PREFIX ."administrators
						set login_attempts = login_attempts + 1
						where id = ". (int)$administrator['id'] ."
						limit 1;"
					);

					throw new Exception(t('error_wrong_username_password_combination', 'Wrong combination of username and password or the account does not exist.'));

				} else {

					database::query(
						"update ". DB_TABLE_PREFIX ."administrators
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
							'{hostname}' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
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
					"update ". DB_TABLE_PREFIX ."administrators
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
				"update ". DB_TABLE_PREFIX ."administrators
				set known_fingerprints = '". database::input(implode(',', $administrator['known_fingerprints'])) ."',
					last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
					last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
					last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
					login_attempts = 0,
					total_logins = total_logins + 1,
					last_login = '". date('Y-m-d H:i:s') ."'
				where id = ". (int)$administrator['id'] ."
				limit 1;"
			);

			administrator::load($administrator['id']);

			session::$data['security.administrator']['timestamp'] = time();
			session::regenerate_id();
			session::rotate_csrf_token();

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
						"update ". DB_TABLE_PREFIX ."administrators
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
				$token = f::token_create_remember($administrator['id'], $administrator['password_hash']);
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
			session::$data['security']['failed_authentications']++;
			http_response_code(401); // Troublesome with HTTP Auth Basic (e.g. .htpasswd)
			notices::add('errors', $e->getMessage());
		}
	}

?>
<style>
body {
	display: flex;
	flex-direction: column;
	width: 100vw;
	height: 100vh;
}

.loader-wrapper {
	display: none;
	position: absolute !important;
	top: 50%;
	left: 50%;
	margin-top: -64px;
	margin-inline-start: -64px;
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
</style>

<div class="loader-wrapper">
	<div class="loader" style="width: 128px; height: 128px;"></div>
</div>

<div id="box-login">

	<?php echo f::form_begin('login_form', 'post'); ?>
		<?php echo f::form_input_hidden('login', 'true'); ?>
		<?php echo f::form_input_hidden('redirect_url', true); ?>

		<div class="card" style="margin: 0;">
			<div class="card-header text-center">
				<a href="<?php echo document::href_ilink(''); ?>">
					<img src="<?php echo document::href_rlink('storage://images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>">
				</a>
			</div>

			<div class="card-body">

				{{notices}}

				<h1><?php echo t('title_sign_in', 'Sign In'); ?></h1>

				<label class="form-group">
					<?php echo f::form_input_username('username', true, ['placeholder' => t('title_username_or_email_address', 'Username or Email Address')]); ?>
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
				<div class="grid">
					<div class="col-md-6 text-start">
						<a class="btn btn-unstyled btn-lg" href="<?php echo document::href_ilink('f:'); ?>">
							<?php echo f::draw_fonticon('icon-chevron-left'); ?> <?php echo t('title_frontend', 'Frontend'); ?>
						</a>
					</div>
					<div class="col-md-6 text-end">
						<?php echo f::form_button('login', t('title_login', 'Login'), 'submit', ['class' => 'btn btn-default btn-lg']); ?>
					</div>
				</div>
			</div>

		</div>

	<?php echo f::form_end(); ?>
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
		$('#box-login .card-body').slideUp(100, function() {
			$('#box-login').fadeOut(250, function() {
				$('.loader-wrapper').fadeIn(100, function() {
					form.submit();
				});
			});
		});
	});
</script>