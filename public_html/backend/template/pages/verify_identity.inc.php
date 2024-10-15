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

				<h1><?php echo language::translate('title_two_factor_authentication', 'Two-Factor Authentication'); ?></h1>

				<div class="form-group">
					<label><?php echo language::translate('title_verification_code', 'Verification Code'); ?></label>
					<?php echo functions::form_input_text('code', '', 'autocomplete="one-time-code" inputmode="numeric" maxlength="6" pattern="\d{6}"'); ?>
				</div>

				<div class="form-group">
					<?php echo functions::form_button('verify', language::translate('title_verify', 'Verify'), 'submit', 'class="btn btn-default btn-block btn-lg"'); ?>
				</div>

				<div class="form-group text-center">
					<?php echo functions::form_button('resend', language::translate('title_resend_code', 'Resend Code'), 'submit', 'class="btn btn-default btn-sm"'); ?>
				</div>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</section>

<script>
	$('input[name="code"]').trigger('focus');
</script>