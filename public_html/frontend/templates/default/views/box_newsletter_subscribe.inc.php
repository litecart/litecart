<style>
#box-newsletter-subscribe {
  padding: 2rem;
  background: #f9f9f9;
}
</style>

<section id="box-newsletter-subscribe">
  <div class="container">

    <?php echo functions::form_draw_form_begin('newsletter_subscribe_form', 'post'); ?>

      <h2><?php echo language::translate('box-newsletter-subscribe:title', 'Subscribe to our newsletter!'); ?></h2>

      <p><?php echo language::translate('box_newsletter_subscribe:description', 'Get the latest news and offers straight to your inbox. Sign up now.'); ?></p>

      <div class="form-group">
        <div class="input-group" style="max-width: 480px; margin-top: 4px;">
          <?php echo functions::form_draw_text_field('email', true, 'placeholder="your@email.com" required'); ?>
          <span class="input-group-btn">
            <?php echo functions::form_draw_button('subscribe', language::translate('title_subscripbe', 'Subscribe')); ?>
          </span>
        </div>
      </div>

    <?php echo functions::form_draw_form_end(); ?>

  </div>
</section>