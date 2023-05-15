<section id="box-newsletter-subscribe" class="card text-center">

  <div class="card-header">
    <div class="card-title"><?php echo language::translate('box_newsletter_subscribe:title', 'Subscribe to our newsletter!'); ?></div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('newsletter_subscribe_form', 'post'); ?>

      <p><?php echo language::translate('box_newsletter_subscribe:description', 'Get the latest news and offers straight to your inbox. Sign up now.'); ?></p>

      <div class="form-group" style="margin-bottom: 0;">
        <div class="input-group" style="max-width: 480px; margin-top: 4px; margin-left: auto; margin-right: auto;">
          <?php echo functions::form_text_field('email', true, 'placeholder="your@email.com" required'); ?>
          <?php echo functions::form_button('subscribe', language::translate('title_subscribe', 'Subscribe')); ?>
        </div>
      </div>

    <?php echo functions::form_end(); ?>

  </div>
</section>
