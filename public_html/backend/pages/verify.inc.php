<?php

	document::$layout = 'blank';

	document::$head_tags[] = '<meta name="viewport" content="width=device-width, initial-scale=1">';

	if (empty(session::$data['security_verification'])) {
		redirect(document::ilink(''));
		exit;
	}

	$send_verification_code = function(){

		session::$data['security_verification'] = [
			'code' => mt_rand(100000, 999999),
			'expires' => strtotime('+15 minutes'),
			'attempts' => 0,
		];

		$email = new ent_email();
		$email->add_recipient(administrator::$data['email'])
					->set_subject(t('title_verification_code', 'Verification Code'))
					->add_body(strtr(t('email_verification_code', 'Verification code: %code'), ['%code' => session::$data['security_verification']['code']]))
					->send();

		notices::add('notices', t('notice_verification_code_sent_via_email', 'A verification code was sent via email'));
	};

	if (isset($_POST['verify'])) {
		try {

			if (empty($_POST['code'])) {
				throw new Exception(t('error_must_provide_verification_code', 'You must provide a verification code'));
			}

			if ($_POST['code'] != session::$data['security_verification']['code']) {
				throw new Exception(t('error_invalid_verification_code', 'Invalid verification code'));
			}

			if (time() > session::$data['security_verification']['expires']) {
				throw new Exception(t('error_verification_code_expired', 'The verification code has expired'));
			}

			$known_ips = preg_split('#\s*,\s*#', administrator::$data['known_ips'], -1, PREG_SPLIT_NO_EMPTY);

			array_unshift($known_ips, $_SERVER['REMOTE_ADDR']);
			$known_ips = array_unique($known_ips);

			if (count($known_ips) > 5) {
				array_pop($known_ips);
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."administrators
				set known_ips = '". database::input(implode(',', $known_ips)) ."'
				where id = ". (int)administrator::$data['id'] ."
				limit 1;"
			);

			unset(session::$data['security_verification']);

			if (!empty($_POST['redirect_url'])) {
				$redirect_url = new ent_link($_POST['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink('b:');
			}

			notices::add('success', str_replace(['%username'], [administrator::$data['username']], t('success_now_logged_in_as', 'You are now logged in as %username')));
			redirect($redirect_url);
			exit;

		} catch (Exception $e) {

			notices::add('errors', $e->getMessage());

			if (++session::$data['security_verification']['attempts'] >= 5 || time() > session::$data['security_verification']['expires']) {
				$send_verification_code();
			}
		}
	}

	if (isset($_POST['resend'])) {
		try {
			$send_verification_code();
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

		<?php echo functions::form_begin('authentication_form', 'post'); ?>
			<?php echo functions::form_input_hidden('redirect_url', true); ?>

			<div class="card-body">

				{{notices}}

				<h1><?php echo t('title_two_factor_authentication', 'Two-Factor Authentication'); ?></h1>

				<label class="form-group">
					<div class="form-label"><?php echo t('title_verification_code', 'Verification Code'); ?></div>
					<?php echo functions::form_input_text('code', '', 'autocomplete="one-time-code" inputmode="numeric" maxlength="6" pattern="\d{6}"'); ?>
				</label>

				<label class="form-group">
					<?php echo functions::form_button('verify', t('title_verify', 'Verify'), 'submit', 'class="btn btn-default btn-block btn-lg"'); ?>
				</label>

				<label class="form-group text-center">
					<?php echo functions::form_button('resend', t('title_resend_code', 'Resend Code'), 'submit', 'class="btn btn-default btn-sm"'); ?>
				</label>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</section>

<script>
	$('input[name="code"]').trigger('focus');
</script>