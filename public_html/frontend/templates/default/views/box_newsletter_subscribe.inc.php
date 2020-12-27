<section id="box-newsletter-subscribe">
  <?php echo functions::form_draw_form_begin('newsletter_subscribe_form', 'post'); ?>

    <h2><?php echo language::translate('title_newsletter', 'Newsletter'); ?></h2>

    <p><?php echo language::translate('box_newsletter_subscribe:description', 'Get the latest news and offers straight to your inbox. Sign up now.'); ?></p>

    <div class="form-group">
      <div class="input-group" style="max-width: 480px; margin-top: 4px;">
        <?php echo functions::form_draw_text_field('email', true, 'placeholder="your@email.com" required'); ?>
        <span class="input-group-btn">
          <?php echo functions::form_draw_button('subscribe', language::translate('title_subscribe', 'Subscribe')); ?>
        </span>
      </div>
    </div>

  <?php echo functions::form_draw_form_end(); ?>
</section>