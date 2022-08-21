<style>
#box-newsletter-subscribe {
  padding: 2rem;
  background: #f9f9f9;
}
#box-newsletter-subscribe .row > div:last-child {
  align-self: center;
}
</style>

<section id="box-newsletter-subscribe">
  <div class="container text-center">

    <?php echo functions::form_begin('newsletter_subscribe_form', 'post'); ?>

      <h2><?php echo language::translate('box-newsletter-subscribe:title', 'Subscribe to our newsletter!'); ?></h2>

      <p><?php echo language::translate('box_newsletter_subscribe:description', 'Get the latest news and offers straight to your inbox. Sign up now.'); ?></p>

      <div class="form-group">
        <div class="input-group" style="max-width: 480px; margin-top: 4px; margin-left: auto; margin-right: auto;">
          <?php echo functions::form_text_field('email', true, 'placeholder="your@email.com" required'); ?>
          <?php echo functions::form_button('subscribe', language::translate('title_subscribe', 'Subscribe')); ?>
        </div>
      </div>

    <?php echo functions::form_end(); ?>

  </div>
</section>
