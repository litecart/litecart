<style>
#box-newsletter-subscribe {
  padding: 1em;
}
#box-newsletter-subscribe .form-group {
  max-width: 400px;
  margin-bottom: 0;
}
</style>

<section id="box-newsletter-subscribe" class="box border">

  <?php echo functions::form_draw_form_begin('newsletter_subscribe_form', 'post'); ?>

    <h2 class="title" style="margin-top: 0;"><?php echo language::translate('box-newsletter-subscribe:title', 'Subscribe to our newsletter!'); ?></h2>

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

</section>
