<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/pages/account/verify.inc.php
	*/
	document::$layout = 'blank';

	document::$head_tags[] = '<meta name="viewport" content="width=device-width, initial-scale=1">';

  if (empty(session::$data['security.customer']['verification'])) {
		redirect(document::ilink(''), 303);
		exit;
	}

  $send_verification_code = function(){

		session::$data['security.customer']['verification'] = [
			'type' => 'eotp',
			'code' => str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT),
			'expires' => date('Y-m-d H:i:s', strtotime('+15 minutes')),
			'attempts' => 0,
		];

		(new ent_email())
			->add_recipient(customer::$data['email'])
			->set_subject(t('title_verification_code', 'Verification Code'))
			->add_body(strtr(t('email_verification_code', 'Verification code: {code}'), [
				'{code}' => session::$data['security.customer']['verification']['code'],
			]))
			->send();

		notices::add('notices', t('notice_verification_code_sent_via_email', 'A verification code was sent via email'));
	};

	if (isset($_POST['verify'])) {
		try {

			// Rate limit checking
			if (database::query(
				"select count(*) as num_attempts from ". DB_PREFIX ."rate_limiting
				where action = 'verification_failed'
				and ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'
				and created_at >= '". date('Y-m-d H:i:s', strtotime('-5 minutes')) ."';"
			)->fetch('num_attempts') > 3) {
				throw new Exception(t('error_too_many_attempts', 'Too many failed attempts. Please try again later.'));
			}

			if (empty($_POST['code'])) {
				throw new Exception(t('error_must_provide_verification_code', 'You must provide a verification code'));
			}

			$code_valid = false;

			if (session::$data['security.customer']['verification']['type'] === 'totp') {

				if (!empty(customer::$data['totp_secret'])
					&& f::totp_verify_code(customer::$data['totp_secret'], $_POST['code'])) {
					$code_valid = true;
				}

			} else {

				if (time() > strtotime(session::$data['security.customer']['verification']['expires'])) {
					throw new Exception(t('error_verification_code_expired', 'The verification code has expired'));
				}

				if ($_POST['code'] === (string)session::$data['security.customer']['verification']['code']) {
					$code_valid = true;
				}
			}

			if (!$code_valid) {

				database::insert('rate_limiting', [
					'action' => 'verification_failed',
					'ip_address' => $_SERVER['REMOTE_ADDR'],
					'hostname' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
					'user_agent' => $_SERVER['HTTP_USER_AGENT'],
					'scope_type' => 'email',
					'scope_key' => customer::$data['email'],
					'created_at' => date('Y-m-d H:i:s'),
				]);

				throw new Exception(t('error_invalid_verification_code', 'Invalid verification code'));
			}

			$known_ips = f::string_split(customer::$data['known_ips']);

			array_unshift($known_ips, $_SERVER['REMOTE_ADDR']);
			$known_ips = array_unique($known_ips);

			if (count($known_ips) > 5) {
				array_pop($known_ips);
			}

			database::query(
				"update ". DB_PREFIX ."customers
				set verified = 1,
					known_ips = '". database::input(implode(',', $known_ips)) ."'
				where id = ". (int)customer::$data['id'] ."
				limit 1;"
			);

			database::query(
				"delete from ". DB_PREFIX ."rate_limiting
				where action = 'verification_failed'
				and (
					ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'
					". (!empty(customer::$data['email']) ? "or (scope_type = 'email' and scope_key = '". database::input(customer::$data['email']) ."')" : '') ."
				);"
			);

			security::$data['timestamp'] = time();
			security::rotate_csrf_token();

			if (!empty($_POST['redirect_url'])) {
				$redirect_url = new type_url($_POST['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink('f:account/index');
			}

			notices::add('success', strtr(t('success_now_logged_in_as', 'You are now logged in as {username}'), [
				'{username}' => customer::$data['username']
			]));

			redirect($redirect_url, 303);
			exit;

		} catch (Exception $e) {

			notices::add('errors', $e->getMessage());

			if (session::$data['security.customer']['verification']['type'] == 'eotp') {
				if (++session::$data['security.customer']['verification']['attempts'] >= 5 || time() > strtotime(session::$data['security.customer']['verification']['expires'])) {
					$send_verification_code();
				}
			} elseif (session::$data['security.customer']['verification']['type'] == 'totp') {
				if (++session::$data['security.customer']['verification']['attempts'] >= 5) {
					unset(session::$data['security.customer']['verification']);
					notices::add('errors', t('error_too_many_attempts', 'Too many failed attempts. Please sign in again.'));
					redirect(document::ilink('account/sign_in'));
					exit;
				}
			}
		}
	}

	if (isset($_POST['resend'])) {
		try {

			// Rate limit checking
			if (database::query(
				"select count(*) as num_attempts from ". DB_PREFIX ."rate_limiting
				where action = 'verification_sent'
				and ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'
				and created_at >= '". date('Y-m-d H:i:s', strtotime('-5 minutes')) ."';"
			)->fetch('num_attempts') > 3) {
				throw new Exception(t('error_too_many_attempts', 'Too many failed attempts. Please try again later.'));
			}

			$send_verification_code();

			database::insert('rate_limiting', [
				'action' => 'verification_sent',
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'hostname' => reverse_dns($_SERVER['REMOTE_ADDR']),
				'user_agent' => $_SERVER['HTTP_USER_AGENT'],
				'scope_type' => 'email',
				'scope_key' => customer::$data['email'],
				'created_at' => date('Y-m-d H:i:s')
			]);

			notices::add('notices', t('notice_verification_code_sent_via_email', 'A verification code was sent via email'));

		} catch (Exception $e) {
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
	width: 100vw;
	height: 100vh;
	background: url(<?php echo document::href_rlink('app://backend/template/images/background.svg'); ?>);
	background-size: cover;
}
html.dark-mode body {
	background: #1a2133;
}

#box-verify-identity {
	width: 360px;
	margin: auto;
	border-radius: 0px 25px 0px 25px;
	box-shadow: 0px 0px 60px rgba(0, 0, 0, .25);
	overflow: hidden;
}
#box-verify-identity .card-header a {
	display: block;
}
#box-verify-identity .card-header img {
	margin: 0 auto;
	max-width: 250px;
	max-height: 100px;
}

input[autocomplete="one-time-code"] {
	--otc-ls: 2ch;
	--otc-gap: 1.25;
	--_otp-bgsz: calc(var(--otc-ls) + 1ch);

	all: unset;
	background: linear-gradient(90deg, var(--otc-bg, #eee) calc(var(--otc-gap) * var(--otc-ls)), transparent 0) 0 0 / var(--_otp-bgsz) 100%;
	caret-color: var(--otc-cc, #333);
	clip-path: inset(0% calc(var(--otc-ls) / 2) 0% 0%);
	font-family: monospace;
	font-size: var(--otc-fz, 2.25em);
	font-weight: 700;
	inline-size: calc(6 * var(--_otp-bgsz));
	letter-spacing: var(--otc-ls);
	padding-block: var(--otc-pb, 1ch);
	padding-inline-start: calc(((var(--otc-ls) - 1ch) / 2) * var(--otc-gap));
}
.selector {
	caret-shape: block;
}
</style>

<section id="box-verify-identity">
	<div class="card" style="margin: 0;">
		<div class="card-header text-center">
			<a href="<?php echo document::href_ilink(''); ?>">
				<img src="<?php echo document::href_rlink('storage://images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>">
			</a>
		</div>

		<?php echo f::form_begin('authentication_form', 'post'); ?>
			<?php echo f::form_input_hidden('redirect_url', true); ?>

			<div class="card-body">

				{{notices}}

				<h1><?php echo t('title_two_factor_authentication', 'Two-Factor Authentication'); ?></h1>

				<label class="form-group">
					<div class="form-label"><?php echo t('title_verification_code', 'Verification Code'); ?></div>
					<?php echo f::form_input_text('code', '', ['autocomplete' => 'one-time-code', 'inputmode' => 'numeric', 'maxlength' => '6', 'pattern' => '\d{6}']); ?>
				</label>

				<label class="form-group">
					<?php echo f::form_button('verify', t('title_verify', 'Verify'), 'submit', ['class' => 'btn btn-default btn-block btn-lg']); ?>
				</label>

				<?php if (empty(security::$data['verification']['type']) || security::$data['verification']['type'] !== 'totp') { ?>
				<label class="form-group text-center">
					<?php echo f::form_button('resend', t('title_resend_code', 'Resend Code'), 'submit', ['class' => 'btn btn-default btn-sm']); ?>
				</label>
				<?php } ?>
			</div>

		<?php echo f::form_end(); ?>
	</div>
</section>

<script>
	$('input[name="code"]').trigger('focus');

	$('input[name="code"]').on('input', function() {
		if ($(this).val().match(/^\d{6}$/)) {
			$('button[name="verify"]').trigger('click');
		}
	});
</script>