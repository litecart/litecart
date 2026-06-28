<?php

	$draw_checkbox = function($class_id, $value, $parameters='') use ($consents) {
		if (!isset($_COOKIE['privacy_consents']) || (isset($consents[$class_id]) && in_array($value, $consents[$class_id]))) {
			return f::form_checkbox('consents['. $class_id .'][]', $value, $value, $parameters);
		} else {
			return f::form_checkbox('consents['. $class_id .'][]', $value, true, $parameters);
		}
	};

?>
<div id="site-privacy-consent" role="dialog" aria-modal="false" aria-label="<?php echo f::escape_attr(t('title_cookie_notice', 'Cookie Notice')); ?>"<?php if (isset($_COOKIE['privacy_consents'])) echo ' style="display: none;"'; ?>>
	<div class="fourteen-forty">

		<div class="notice">
			<button name="customize" class="btn btn-default btn-sm" type="button" aria-expanded="false" aria-controls="privacy-classes">
				<?php echo t('title_customize', 'Customize'); ?>
			</button>

			<h2 class="visually-hidden"><?php echo t('title_cookie_notice', 'Cookie Notice'); ?></h2>
			<span><?php echo strtr(t('text_cookie_notice', 'We rely on some data regulated by the EU ePrivacy Directive (EPD) for analyzing, marketing or retargeting that relies on the use of third party services.'), [
				'%url' => document::href_ilink('information', ['page_id' => settings::get('cookie_policy')])
			]); ?></span>
		</div>

		<?php echo f::form_begin('cookies_form', 'post', false, false, ['aria-label' => f::escape_attr(t('title_privacy_consent', 'Privacy Consent'))]); ?>

			<div class="privacy-classes">

				<?php foreach ($privacy_classes as $class) { ?>
				<?php if (empty($class['third_parties'])) continue; ?>
				<div id="<?php echo $class['id']; ?>-cookies" class="privacy-class">
					<div class="class">
						<label>
							<div class="grid">
								<div class="col-1 text-center">
									<?php if ($class['id'] == 'necessary') { ?>
									<?php echo f::form_input_hidden('consents['. $class['id'] .'][]', 'all'); ?>
									<?php echo f::form_checkbox('consents['. $class['id'] .'][]', 'all', 'all', ['disabled' => '']); ?>
									<?php } else { ?>
									<?php echo $draw_checkbox($class['id'], 'all', 'all'); ?>
									<?php } ?>
								</div>

								<div class="col-11">
									<div class="name"><?php echo f::escape_html($class['title']); ?></div>
									<div class="description"><?php echo f::escape_html($class['description']); ?></div>
								</div>
							</div>
						</label>

						<blockquote class="third-parties">
							<?php foreach ($class['third_parties'] as $third_party) { ?>
							<div class="third-party">
								<label>
									<?php echo $draw_checkbox($class['id'], $third_party['id'], 'disabled'); ?>
									<a class="name" href="<?php echo document::href_ilink('third_parties', ['third_party_id' => $third_party['id']]); ?>">
										<?php echo f::escape_html($third_party['name']); ?>
									</a>
								</label>
							</div>
							<?php } ?>
						</blockquote>
					</div>
				</div>
				<?php } ?>

			</div>

			<div class="buttons text-center">
				<?php echo f::form_button('privacy_consent', ['1', t('text_accept', 'Accept')], 'submit', ['style' => 'font-weight: bold;']); ?>
				<?php echo f::form_button('privacy_consent', ['0', t('text_reject', 'Reject')], 'submit'); ?>
			</div>

		<?php echo f::form_end(); ?>
	</div>
</div>

<script>
	waitFor('jQuery', function($) {
		try {
			var privacy_classes = <?php echo f::format_json($privacy_classes, ''); ?>;
			var consents = <?php echo f::format_json($consents, ''); ?>;
			$('#site-privacy-consent').privacyConsent(privacy_classes, consents);
		} catch (e) {
			console.error('Could not initiate privacy consent manager: ' + e.message);
		}
	});
</script>