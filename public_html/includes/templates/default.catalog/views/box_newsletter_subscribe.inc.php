<section id="box-newsletter-subscribe" class="card text-center">

  <div class="card-body">

    <h2><?php echo language::translate('box_newsletter_subscribe:title', 'Subscribe to our newsletter!'); ?></h2>

    <p><?php echo language::translate('box_newsletter_subscribe:description', 'Get the latest news and offers straight to your inbox. Subscribe now.'); ?></p>

    <?php echo functions::form_draw_form_begin('newsletter_subscribe_form', 'post', document::ilink('newsletter')); ?>

      <div class="form-group" style="max-width: 400px; margin: 0 auto;">
        <div class="input-group">
          <?php echo functions::form_draw_text_field('email', true, 'placeholder="your@email.com" required'); ?>
          <?php echo functions::form_draw_button('subscribe', language::translate('title_subscribe', 'Subscribe')); ?>
        </div>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</section>

<script>
  $('form[name="newsletter_subscribe_form"]').submit(function(e){
    e.preventDefault();
    $.featherlight('<?php echo document::ilink('newsletter'); ?>?email='+ $(this).find('input[name="email"]').val() +' #box-newsletter-subscribe', {
      "seamless": true,
      "width": "640px"
    });
  })
</script>