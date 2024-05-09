<main id="content" class="fourteen-forty">
  {{notices}}
  {{breadcrumbs}}

  <div class="layout row">
    <div class="col-md-6">

      <section id="box-newsletter-subscribe" class="card">
        <div class="card-body">
          <h2><?php echo language::translate('box_newsletter_subscribe:title', 'Subscribe to our newsletter!'); ?></h2>

          <p>
            <?php echo language::translate('box_newsletter_subscribe:description', 'Get the latest news and offers straight to your inbox. Subscribe now.'); ?>
          </p>

          <?php echo functions::form_begin('newsletter_subscribe_form', 'post', document::ilink('newsletter')); ?>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
                <?php echo functions::form_input_text('firstname', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
                <?php echo functions::form_input_text('lastname', true); ?>
              </div>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
              <?php echo functions::form_input_email('email', true, 'required'); ?>
            </div>

            <?php if (settings::get('captcha_enabled')) { ?>
            <div class="row">
              <div class="form-group col-xs-6">
                <label><?php echo language::translate('title_captcha', 'CAPTCHA'); ?></label>
                <?php echo functions::form_captcha('captcha', 'newsletter_subscribe', 'required'); ?>
              </div>
            </div>
            <?php } ?>

            <?php if ($consent) { ?>
            <p class="consent">
              <div class="checkbox">
                <?php echo '<label>'. functions::form_draw_checkbox('terms_agreed', '1', true, 'required') .' '. $consent .'</label>'; ?>
              </div>
            </p>
            <?php } ?>

            <?php echo functions::form_button('subscribe', language::translate('title_subscribe', 'Subscribe')); ?>

          <?php echo functions::form_end(); ?>
        </div>
      </section>
    </div>

    <div class="col-md-6">
      <section id="box-newsletter-unsubscribe" class="card">
        <div class="card-body">
          <h2><?php echo language::translate('box_newsletter_unsubscribe:title', 'Unsubscribe from our newsletter'); ?></h2>

          <?php echo functions::form_begin('newsletter_unsubscribe_form', 'post', document::ilink('newsletter')); ?>

            <div class="form-group">
              <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
              <?php echo functions::form_input_email('email', true, 'required'); ?>
            </div>

            <?php echo functions::form_button('unsubscribe', language::translate('title_unsubscribe', 'Unsubscribe')); ?>

          <?php echo functions::form_end(); ?>
        </div>
      </section>
    </div>
  </div>
</main>
