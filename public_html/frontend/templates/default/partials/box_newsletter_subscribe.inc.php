<style>
#box-newsletter-subscribe {
  padding: var(--gutter-size);
  background: #f9f9f9;
}
#box-newsletter-subscribe .row > div:last-child {
  align-self: center;
}
#box-newsletter-subscribe .wrapper {
  display: inline-flex;
  gap: var(--gutter-size);
  justify-content: center;
}
</style>

<section id="box-newsletter-subscribe">
  <div class="container text-center">

    <div class="wrapper">
      <div class="hidden-xs" style="flex: 0 1 170px;">
        <img class="responsive" src="<?php echo document::href_rlink('storage://images/illustration/letter.svg'); ?>" >
      </div>

      <?php echo functions::form_begin('newsletter_subscribe_form', 'post'); ?>

        <h2><?php echo language::translate('box-newsletter-subscribe:title', 'Subscribe to our newsletter!'); ?></h2>

        <p><?php echo language::translate('box_newsletter_subscribe:description', 'Get the latest news and offers straight to your inbox. Sign up now.'); ?></p>

        <div class="form-group">
          <div style="display: flex; flex-direction: row; gap: 1em">
            <?php echo functions::form_input_email('email', true, 'placeholder="your@email.com" required'); ?>
            <?php echo functions::form_button('subscribe', language::translate('title_subscribe', 'Subscribe')); ?>
          </div>
        </div>

      <?php echo functions::form_end(); ?>
    </div>

  </div>
</section>