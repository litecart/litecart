<style>
#box-newsletter-subscribe {
	padding: var(--gutter-y )var(--gutter-x);
	padding-top: 0;
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

<section id="box-newsletter-subscribe" aria-label="<?= f::escape_attr(t('box-newsletter-subscribe:title', 'Subscribe to our newsletter!')) ?>">
	<div class="container text-center">

		<div class="flex-columns" style="place-content: center;">
			<div class="hidden-xs" style="flex: 0 1 170px;">
				<img class="responsive" src="<?= document::href_rlink('storage://images/illustration/newsletter.svg') ?>" alt="" style="max-height: 150px;">
			</div>

			<?= f::form_begin('newsletter_subscribe_form', 'post', false, false, ['aria-label' => f::escape_attr(t('box-newsletter-subscribe:title', 'Subscribe to our newsletter!'))]) ?>

				<h2><?= t('box-newsletter-subscribe:title', 'Subscribe to our newsletter!') ?></h2>

				<p><?= t('box_newsletter_subscribe:description', 'Get the latest news and offers straight to your inbox. Sign up now.') ?></p>

				<div class="form-label">
					<div style="display: flex; flex-direction: row; gap: 1em">
						<label for="newsletter_subscribe_email" class="hidden"><?= t('title_email_address', 'Email Address') ?></label>
						<?= f::form_input_email('email', true, ['id' => 'newsletter_subscribe_email', 'placeholder' => f::escape_attr(t('text_enter_your_email_address', 'Enter your email address')), 'autocomplete' => 'email', 'required' => true]) ?>
						<?= f::form_button('subscribe', t('title_subscribe', 'Subscribe')) ?>
					</div>
				</div>

			<?= f::form_end() ?>
		</div>

	</div>
</section>

<script>
	$('form[name="newsletter_subscribe_form"]').submit(function(e){
		e.preventDefault();
		let url = '<?= document::ilink('newsletter') ?>?email='+ $(this).find('input[name="email"]').val();
		$.litebox(url +' #box-newsletter-subscribe', {
			"seamless": true,
			"width": "640px"
		});
	});
</script>