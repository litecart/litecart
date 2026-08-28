<?php

	document::$layout = 'blank';

	document::$head_tags[] = '<meta name="viewport" content="width=device-width, initial-scale=1">';

	if (empty(security::$data['verification'])) {
		redirect(document::ilink(''), 303);
		exit;
	}

	if (isset($_POST['verify'])) {
		try {

			if (empty($_POST['code'])) {
				throw new Exception(t('error_must_provide_verification_code', 'You must provide a verification code'));
			}

			$is_totp = !empty(security::$data['verification']['type'])
				&& security::$data['verification']['type'] === 'totp';

			if ($is_totp) {

				require_once 'app://shared/functions/func_totp.inc.php';

				if (empty(customer::$data['totp_secret'])
					|| !totp_verify_code(customer::$data['totp_secret'], $_POST['code'])) {
					throw new Exception(t('error_invalid_verification_code', 'Invalid verification code'));
				}

			} else {

				if ($_POST['code'] != security::$data['verification']['code']) {
					throw new Exception(t('error_invalid_verification_code', 'Invalid verification code'));
				}

				if (time() > security::$data['verification']['expires']) {
					throw new Exception(t('error_verification_code_expired', 'The verification code has expired'));
				}

				// The unknown-IP challenge records the successful IP as trusted.
				// TOTP is location-independent and intentionally doesn't.
				$known_ips = customer::$data['known_ips'];
				array_unshift($known_ips, $_SERVER['REMOTE_ADDR']);
				$known_ips = array_slice(array_unique($known_ips), 0, 10);

				database::query(
					"update ". DB_TABLE_PREFIX ."customers
					set known_ips = '". database::input(implode(',', $known_ips)) ."'
					where id = ". (int)customer::$data['id'] ."
					limit 1;"
				);
			}

			unset(security::$data['verification']);

			if (!empty($_POST['redirect_url'])) {
				$redirect_url = new type_url($_POST['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink('b:');
			}

			notices::add('success', strtr(t('success_now_logged_in_as', 'You are now logged in as {username}'), [
				'{username}' => customer::$data['username']
			]));

			redirect($redirect_url, 303);
			exit;

		} catch (Exception $e) {

			notices::add('errors', $e->getMessage());

			if (++security::$data['verification']['attempts'] >= 5) {
				unset(security::$data['verification']);
				notices::add('errors', t('error_too_many_attempts', 'Too many failed attempts. Please sign in again.'));
				redirect(document::ilink('login'));
				exit;
			}
		}
	}

	if (isset($_POST['resend'])) {
		try {

			(new ent_customer())->send_email('verification_code', [
				'code' => security::$data['verification']['code'],
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
		if ($(this).val().length === 6) {
			$(this).closest('form').submit();
		}
	});
</script>