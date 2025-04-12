<style>
#box-newsletter-subscribe {
	padding: var(--gutter-y )var(--gutter-x);
	background: #f9f9f9;
	border-top: 2px solid;
	border-bottom: 2px solid;
	border-image-slice: 1;
	border-image-source: linear-gradient(to left, #97989f00, #89849269, #97989f00);
}
#box-newsletter-subscribe .row > div:last-child {
	align-self: center;
}
#box-newsletter-subscribe .wrapper {
	display: inline-flex;
	gap: var(--gutter-y) var(--gutter-x);
	justify-content: center;
}
</style>

<section id="box-newsletter-subscribe">
	<div class="container text-center">

		<div class="flex-columns" style="place-content: center;">
			<div class="hidden-xs" style="flex: 0 1 170px;">
				<img class="responsive" src="<?php echo document::href_rlink('storage://images/illustration/letter.svg'); ?>" >
			</div>

			<?php echo functions::form_begin('newsletter_subscribe_form', 'post'); ?>

				<h2><?php echo language::translate('box-newsletter-subscribe:title', 'Subscribe to our newsletter!'); ?></h2>

				<p><?php echo language::translate('box_newsletter_subscribe:description', 'Get the latest news and offers straight to your inbox. Sign up now.'); ?></p>

				<div class="form-label">
					<div style="display: flex; flex-direction: row; gap: 1em">
						<?php echo functions::form_input_email('email', true, 'placeholder="'. functions::escape_attr(language::translate('text_enter_your_email_address', 'Enter your email address')) .'" required'); ?>
						<?php echo functions::form_button('subscribe', language::translate('title_subscribe', 'Subscribe')); ?>
					</div>
				</div>

			<?php echo functions::form_end(); ?>
		</div>

	</div>
</section>

<script>
	$('form[name="newsletter_subscribe_form"]').submit(function(e){
		e.preventDefault();
		let url = '<?php echo document::ilink('newsletter'); ?>?email='+ $(this).find('input[name="email"]').val();
		$.litebox(url +' #box-newsletter-subscribe', {
			"seamless": true,
			"width": "640px"
		});
	});
</script>