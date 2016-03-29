<div id="box-contact-us" class="box">
  <h1 class="title"><?php echo language::translate('title_contact_us', 'Contact Us'); ?></h1>

  <div class="content">
    <p class="address"><?php echo nl2br(settings::get('store_postal_address')); ?></p>

    <?php if (settings::get('store_phone')) { ?><p class="phone"><?php echo functions::draw_fonticon('fa-phone'); ?> <a href="tel:<?php echo settings::get('store_phone'); ?>"><?php echo settings::get('store_phone'); ?></a></p><?php } ?>

    <p class="email"><?php echo functions::draw_fonticon('fa-envelope'); ?> <a href="mailto:<?php echo settings::get('store_email'); ?>"><?php echo settings::get('store_email'); ?></a></p>

    <?php echo functions::form_draw_form_begin('contact_form', 'post'); ?>
    <table>
      <tr>
        <td><?php echo language::translate('title_name', 'Name'); ?> <span class="required">*</span><br />
          <?php echo functions::form_draw_text_field('name', true); ?></td>
        <td><?php echo language::translate('title_email_address', 'Email Address'); ?> <span class="required">*</span><br />
          <?php echo functions::form_draw_email_field('email', true, ''); ?></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo language::translate('title_subject', 'Subject'); ?> <span class="required">*</span><br />
          <?php echo functions::form_draw_text_field('subject', true, 'data-size="large"'); ?></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo language::translate('title_message', 'Message'); ?> <span class="required">*</span><br />
          <?php echo functions::form_draw_textarea('message', true, 'data-size="large" style="height: 250px;"'); ?></td>
      </tr>
      <?php if (settings::get('captcha_enabled')) { ?>
      <tr>
        <td colspan="2"><?php echo language::translate('title_captcha', 'CAPTCHA'); ?> <span class="required">*</span><br />
          <?php echo functions::captcha_generate(100, 40, 4, 'contact_us', 'numbers', 'align="absbottom"') .' '. functions::form_draw_text_field('captcha', '', 'style="width: 90px; height: 30px; font-size: 24px; text-align: center;"'); ?>
        </td>
      </tr>
      <?php } ?>
      <tr>
        <td><?php echo functions::form_draw_button('send', language::translate('title_send', 'Send'), 'submit', 'style="font-weight: bold;"'); ?></td>
        <td>&nbsp;</td>
      </tr>
    </table>
    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>