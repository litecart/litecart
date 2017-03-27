<aside id="sidebar">
  <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATE . 'views/column_left.inc.php'); ?>
</aside>

<main id="content">
  {snippet:notices}
  {snippet:breadcrumbs}

  <div id="box-reset-password" class="box">

    <h2 class="title"><?php echo language::translate('title_reset_password', 'Reset Password'); ?></h2>

    <?php echo functions::form_draw_form_begin('reset_password_form', 'post', null, false, 'style="width: 320px;"'); ?>

      <div class="form-group">
        <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
        <?php echo functions::form_draw_email_field('email', true); ?>
      </div>

      <?php if (isset($_REQUEST['reset_token'])) { ?>
      <div class="form-group">
        <label><?php echo language::translate('title_reset_token', 'Reset Token'); ?></label>
        <?php echo functions::form_draw_text_field('reset_token', true); ?>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_new_password', 'New Password'); ?></label>
        <?php echo functions::form_draw_password_field('new_password', ''); ?>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_confirmed_password', 'Confirmed Password'); ?></label>
        <?php echo functions::form_draw_password_field('confirmed_password', ''); ?>
      </div>
      <?php } ?>

      <p class="btn-group btn-block">
        <?php echo functions::form_draw_button('reset_password', language::translate('title_reset_password', 'Reset Password')); ?>
      </p>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</main>