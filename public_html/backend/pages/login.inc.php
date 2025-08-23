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
				where lower(username) = '". database::input(strtolower($_POST['username'])) ."'
				or lower(email) = '". database::input(strtolower($_POST['username'])) ."'
				limit 1;"
			)->fetch(function($administrator){
				$administrator['known_ips'] = functions::string_split($administrator['known_ips']);
				return $administrator;
			});

			if (!$administrator) {
				throw new Exception(t('error_administrator_not_found', 'The administrator could not be found in our database'));
			}

			if (empty($administrator['status'])) {
				throw new Exception(t('error_administrator_account_disabled', 'The administrator account is disabled'));
			}

			if (!empty($administrator['valid_from']) && date('Y-m-d H:i:s') < $administrator['valid_from']) {
				throw new Exception(strtr(t('error_account_is_blocked', 'The account is blocked until {datetime}'), [
					'{datetime}' => functions::datetime_format('datetime', $administrator['valid_from'])
				]));
			}

			if (!empty($administrator['valid_to']) && date('Y-m-d H:i:s') > $administrator['valid_to']) {
				throw new Exception(strtr(t('error_account_expired', 'The account expired {datetime}'), [
					'{datetime}' => functions::datetime_format('datetime', $administrator['valid_to'])
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

			database::query(
				"update ". DB_TABLE_PREFIX ."administrators
				set last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
					last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
					last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
					login_attempts = 0,
					total_logins = total_logins + 1,
					last_login = '". date('Y-m-d H:i:s') ."'
				where id = ". (int)$administrator['id'] ."
				limit 1;"
			);

			administrator::load($administrator['id']);

			session::$data['administrator_security_timestamp'] = time();
			session::regenerate_id();

			unset(session::$data['security_verification']);

			if (!in_array($_SERVER['REMOTE_ADDR'], $administrator['known_ips']) && !empty($administrator['two_factor_auth']) && !empty($administrator['email'])) {

				session::$data['security_verification'] = [
					'code' => mt_rand(100000, 999999),
					'expires' => strtotime('+15 minutes'),
					'attempts' => 0,
				];

				(new ent_email())
					->add_recipient($administrator['email'])
					->set_subject(t('title_verification_code', 'Verification Code'))
					->add_body(strtr(t('email_verification_code', 'Verification code: {code}'), [
						'{code}' => session::$data['security_verification']['code']
					]))
					->send();

				notices::add('notices', t('notice_verification_code_sent_via_email', 'A verification code was sent via email'));

				if (!empty($_POST['redirect_url'])) {
					redirect(document::ilink('verify', ['redirect_url' => $_POST['redirect_url']]));
				} else {
					redirect(document::ilink('verify'));
				}

				exit;

			} else {

				array_unshift($administrator['known_ips'], $_SERVER['REMOTE_ADDR']);
				$administrator['known_ips'] = array_unique($administrator['known_ips']);

				if (count($administrator['known_ips']) > 5) {
					array_pop($administrator['known_ips']);
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."administrators
					set known_ips = '". database::input(implode(',', $administrator['known_ips'])) ."'
					where id = ". (int)$administrator['id'] ."
					limit 1;"
				);
			}

			if (!empty($_POST['remember_me'])) {
				$checksum = sha1($administrator['username'] . $administrator['password_hash'] . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
				header('Set-Cookie: remember_me='. $administrator['username'] .':'. $checksum .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; HttpOnly; SameSite=Lax', false);
			} else if (!empty($_COOKIE['remember_me'])) {
				header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
			}

			if (!empty($_POST['redirect_url'])) {
				$redirect_url = new ent_link($_POST['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink('b:');
			}

			notices::add('success', strtr(t('success_now_logged_in_as', 'You are now logged in as {username}'), [
				'{username}' => administrator::$data['username']
			]));

			redirect($redirect_url);
			exit;

		} catch (Exception $e) {
			http_response_code(401); // Troublesome with HTTP Auth (e.g. .htpasswd)
			notices::add('errors', $e->getMessage());
		}
	}

?>
<style>
html {
	background: #f8f8f8;
}

body {
	display: flex;
	flex-direction: column;
	width: 100vw;
	height: 100vh;
	background: url(<?php echo document::href_rlink('app://backend/template/images/background.svg'); ?>);
	background-size: cover;
}
html.dark-mode body {
	background: #1a2133;
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
	width: 400px;
	margin: auto;
	border-radius: var(--border-radius);
}
#box-login .card-header a {
	display: block;
}
#box-login .card-header img {
	margin: 0 auto;
	max-width: 250px;
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

	<?php echo functions::form_begin('login_form', 'post'); ?>
		<?php echo functions::form_input_hidden('login', 'true'); ?>
		<?php echo functions::form_input_hidden('redirect_url', true); ?>

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
					<?php echo functions::form_input_username('username', true, 'placeholder="'. t('title_username_or_email_address', 'Username or Email Address') .'"'); ?>
					<div class="form-label"></div>
				</label>

				<label class="form-group">
					<?php echo functions::form_input_password('password', '', 'placeholder="'. t('title_password', 'Password') .'" autocomplete="current-password"'); ?>
					<div class="form-label"></div>
				</label>

				<div class="form-group">
					<?php echo functions::form_checkbox('remember_me', ['1', t('title_remember_me', 'Remember Me')], true); ?>
				</div>
			</div>

			<div class="card-footer">
				<div class="grid">
					<div class="col-md-6 text-start">
						<a class="btn btn-unstyled btn-lg" href="<?php echo document::href_ilink('f:'); ?>">
							<?php echo functions::draw_fonticon('icon-chevron-left'); ?> <?php echo t('title_go_to_frontend', 'Go To Frontend'); ?>
						</a>
					</div>
					<div class="col-md-6 text-end">
						<?php echo functions::form_button('login', t('title_login', 'Login'), 'submit', 'class="btn btn-default btn-lg"'); ?>
					</div>
				</div>
			</div>

		</div>

	<?php echo functions::form_end(); ?>
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