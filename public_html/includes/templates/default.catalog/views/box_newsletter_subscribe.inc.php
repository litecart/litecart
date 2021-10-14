<style>
#box-newsletter-subscribe {
  padding: 1em;
}
#box-newsletter-subscribe .row > div:last-child {
  align-self: center;
}
</style>

<section id="box-newsletter-subscribe" class="box border">

  <h2 class="title"><?php echo language::translate('box-newsletter-subscribe:title', 'Subscribe to our newsletter!'); ?></h2>

  <?php echo functions::form_draw_form_begin('newsletter_subscribe_form', 'post'); ?>

    <p><?php echo language::translate('box_newsletter_subscribe:description', 'Get the latest news and offers straight to your inbox. Sign up now.'); ?></p>

    <div class="form-group" style="margin-bottom: 0;">
      <div class="input-group" style="max-width: 480px; margin-top: 4px;">
        <?php echo functions::form_draw_text_field('email', true, 'placeholder="your@email.com" required'); ?>
        <?php echo functions::form_draw_button('subscribe', language::translate('title_subscripbe', 'Subscribe')); ?>
      </div>
    </div>

  <?php echo functions::form_draw_form_end(); ?>

</section>
