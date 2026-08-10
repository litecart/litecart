<main id="main" class="container">
	{{notices}}

	<div id="content">
		<div id="box-are-you-human" class="card" style="max-width: 360px; margin: 2em auto 4em auto;" aria-label="<?php echo f::escape_attr(t('title_are_you_human', 'Are You Human?')); ?>">

			<div class="card-header">
				<h2 class="card-title"><?php echo t('title_are_you_human', 'Are You Human?'); ?></h2>
			</div>

			<div class="card-body">

				<div style="margin-bottom: 2em;"><?php echo t('description_are_you_human', 'You did not pass our security tests and we want to make sure you are not a robot'); ?></div>

				<?php echo f::form_begin('captcha_begin', 'post', false, false, ['aria-label' => f::escape_attr(t('title_are_you_human', 'Are You Human?'))]); ?>

					<label class="form-group" style="position:absolute;width:0;height:0;overflow:hidden;">
						<div class="form-label"><?php echo t('title_email_address', 'Email'); ?></div>
						<?php echo f::form_input_email('email', false, ['autocomplete' => 'off', 'tabindex' => '-1']); ?>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_are_you_a_human', 'Are you a human?'); ?></div>
						<?php echo f::form_toggle('is_human', 'y/n', true); ?>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_captcha', 'CAPTCHA'); ?></div>
						<?php echo f::form_captcha('are_you_human'); ?>
					</label>

					<div>
						<?php echo f::form_button('confirm', t('title_confirm', 'Confirm'), 'submit'); ?>
					</div>

				<?php echo f::form_end(); ?>
			</div>
		</div>
	</div>
</main>