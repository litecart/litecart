<?php if (!empty(customer::$data['id'])) return; ?>
<div class="box" id="box-login">
  <div class="heading"><h3><?php echo language::translate('title_login', 'Login'); ?></h3></div>
  <div class="content">
    <?php echo functions::form_draw_form_begin('login_form', 'post', document::ilink('login')); ?>
     <?php echo functions::form_draw_hidden_field('redirect_url', $_SERVER['REQUEST_URI']); ?>
      <table style="width: 100%;">
        <tr>
          <td><?php echo language::translate('title_email_address', 'E-mail Address'); ?><br />
            <?php echo functions::form_draw_email_field('email', true, ''); ?></td>
        </tr>
        <tr>
          <td><?php echo language::translate('title_password', 'Password'); ?><br />
          <?php echo functions::form_draw_password_field('password', ''); ?></td>
        </tr>
        <tr>
          <td><?php echo functions::form_draw_button('login', language::translate('title_login', 'Login')); ?> <?php echo functions::form_draw_button('lost_password', language::translate('title_lost_password', 'Lost Password')); ?> </td>
        </tr>
        <tr>
          <td><a href="<?php echo document::href_ilink('create_account'); ?>"><?php echo language::translate('text_new_customers_click_here', 'New customers click here'); ?></a></td>
        </tr>
    </table>
    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>