<style>
#box-newsletter-subscribe .form-group {
  max-width: 400px;
  margin: 0 auto;
}
</style>

<section id="box-newsletter-subscribe" class="card text-center">

  <div class="card-header">
    <h2 class="card-title" style="margin-top: 0;"><?php echo language::translate('box_newsletter_subscribe:title', 'Subscribe to our newsletter!'); ?></h2>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('newsletter_subscribe_form', 'post'); ?>

      <p><?php echo language::translate('box_newsletter_subscribe:description', 'Get the latest news and offers straight to your inbox. Subscribe now.'); ?></p>

      <?php if ($privacy_policy_link) { ?>
      <p><?php echo strtr(language::translate('description_subscribing_to_newsletter_accepts_privacy_policy', 'By subscribing to our newsletter you are accepting our <a href="%link">privacy policy</a>.'), ['%link' => functions::escape_html($privacy_policy_link)]); ?></p>
      <?php } ?>

      <div class="form-group">
        <div class="input-group">
          <?php echo functions::form_draw_text_field('email', true, 'placeholder="your@email.com" required'); ?>
          <?php echo functions::form_draw_button('subscribe', language::translate('title_subscripbe', 'Subscribe')); ?>
        </div>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</section>
