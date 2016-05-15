<div id="create-account" class="box">
  <h1 class="title"><?php echo language::translate('title_create_account', 'Create Account'); ?></h1>
  <div class="content">
    <?php echo functions::form_draw_form_begin('customer_form', 'post'); ?>
      <table>
        <tr>
          <td><?php echo language::translate('title_tax_id', 'Tax ID'); ?><br />
            <?php echo functions::form_draw_text_field('tax_id', true); ?></td>
          <td><?php echo language::translate('title_company', 'Company'); ?><br />
            <?php echo functions::form_draw_text_field('company', true); ?></td>
        </tr>
        <tr>
          <td><?php echo language::translate('title_firstname', 'First Name'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_text_field('firstname', true, 'required="required"'); ?></td>
          <td><?php echo language::translate('title_lastname', 'Last Name'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_text_field('lastname', true, 'required="required"'); ?></td>
        </tr>
        <tr>
          <td><?php echo language::translate('title_address1', 'Address 1'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_text_field('address1', true, 'required="required"'); ?></td>
          <td><?php echo language::translate('title_address2', 'Address 2'); ?><br />
          <?php echo functions::form_draw_text_field('address2', true); ?></td>
        </tr>
        <tr>
          <td><?php echo language::translate('title_postcode', 'Postcode'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_text_field('postcode', true); ?></td>
          <td><?php echo language::translate('title_city', 'City'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_text_field('city', true); ?></td>
        </tr>
        <tr>
          <td><?php echo language::translate('title_country', 'Country'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_countries_list('country_code', true); ?></td>
          <td><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?> <span class="required">*</span><br />
            <?php echo form_draw_zones_list(isset($_POST['country_code']) ? $_POST['country_code'] : '', 'zone_code', true); ?></td>
        </tr>
        <tr>
          <td><?php echo language::translate('title_email', 'Email'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_email_field('email', true, 'required="required"'); ?></td>
          <td><?php echo language::translate('title_phone', 'Phone'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_phone_field('phone', true, 'required="required"'); ?></td>
        </tr>
        <tr>
          <td><?php echo language::translate('title_newsletter', 'Newsletter'); ?><br />
            <label><?php echo functions::form_draw_checkbox('newsletter', '1', true); ?> <?php echo language::translate('title_subscribe', 'Subscribe'); ?></label></td>
          </td>
          <td></td>
        </tr>
        <tr>
          <td><?php echo language::translate('title_desired_password', 'Desired Password'); ?> <span class="required">*</span><br />
          <?php echo functions::form_draw_password_field('password', '', 'required="required"'); ?></td>
          <td><?php echo language::translate('title_confirm_password', 'Confirm Password'); ?> <span class="required">*</span><br />
          <?php echo functions::form_draw_password_field('confirmed_password', '', 'required="required"'); ?></td>
        </tr>
        <?php if (settings::get('captcha_enabled')) { ?>
        <tr>
          <td colspan="2"><?php echo language::translate('title_captcha', 'CAPTCHA'); ?> <span class="required">*</span><br />
            <?php echo functions::captcha_generate(100, 40, 4, 'create_account', 'numbers', 'align="absbottom"') .' '. functions::form_draw_text_field('captcha', '', 'style="width: 90px; height: 30px; font-size: 24px; text-align: center;"'); ?>
          </td>
        </tr>
        <?php } ?>
        <tr>
          <td colspan="2"><?php echo functions::form_draw_button('create_account', language::translate('title_create_account', 'Create Account')); ?></td>
        </tr>
      </table>
    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <script>
    $("#box-checkout-account input, #box-checkout-account select").change(function() {
      if ($(this).val() == '') return;
      $('body').css('cursor', 'wait');
      $.ajax({
        url: '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
        type: 'post',
        data: $(this).closest('form').serialize(),
        cache: false,
        async: true,
        dataType: 'json',
        error: function(jqXHR, textStatus, errorThrown) {
          if (console) console.log(errorThrown.message)
        },
        success: function(data) {
          if (data['alert']) {
            alert(data['alert']);
          }
          $.each(data, function(key, value) {
            console.log(key +": "+ value);
            if ($("input[name='"+key+"']").length && $("input[name='"+key+"']").val() == '') $("input[name='"+key+"']").val(data[key]);
          });
        },
        complete: function() {
          $('body').css('cursor', 'auto');
        }
      });
    });

    $("form[name='customer_form'] input, form[name='customer_form'] select").change(function() {
      if ($(this).val() == '') return;
      $('body').css('cursor', 'wait');
      $.ajax({
        url: '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
        type: 'post',
        data: $(this).closest('form').serialize(),
        cache: false,
        async: true,
        dataType: 'json',
        error: function(jqXHR, textStatus, errorThrown) {
          if (console) console.warn(errorThrown.message);
        },
        success: function(data) {
          if (data['alert']) {
            alert(data['alert']);
            return;
          }
          $.each(data, function(key, value) {
            console.log(key +" "+ value);
            if ($("input[name='"+key+"']").length && $("input[name='"+key+"']").val() == '') $("input[name='"+key+"']").val(data[key]);
          });
        },
        complete: function() {
          $('body').css('cursor', 'auto');
        }
      });
    });

    $("select[name='country_code']").change(function(){
      if ($(this).find('option:selected').data('tax-id-format') != '') {
        $(this).closest('table').find("input[name='tax_id']").attr('pattern', $(this).find('option:selected').data('tax-id-format'));
      } else {
        $(this).closest('table').find("input[name='tax_id']").removeAttr('pattern');
      }

      if ($(this).find('option:selected').data('postcode-format') != '') {
        $(this).closest('table').find("input[name='postcode']").attr('pattern', $(this).find('option:selected').data('postcode-format'));
        $(this).closest('table').find("input[name='postcode']").attr('required', 'required');
        $(this).closest('table').find("input[name='postcode']").closest('td').find('.required').show();
      } else {
        $(this).closest('table').find("input[name='postcode']").removeAttr('pattern');
        $(this).closest('table').find("input[name='postcode']").removeAttr('required');
        $(this).closest('table').find("input[name='postcode']").closest('td').find('.required').hide();
      }

      if ($(this).find('option:selected').data('phone-code') != '') {
        $(this).closest('table').find("input[name='phone']").attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'));
      } else {
        $(this).closest('table').find("input[name='phone']").removeAttr('placeholder');
      }

      $('body').css('cursor', 'wait');
      $.ajax({
        url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
        type: 'get',
        cache: true,
        async: true,
        dataType: 'json',
        error: function(jqXHR, textStatus, errorThrown) {
          if (console) console.warn(errorThrown.message);
        },
        success: function(data) {
          $("select[name='zone_code']").html('');
          if (data) {
            $("select[name='zone_code']").removeAttr('disabled');
            $("select[name='zone_code']").closest('td').css('opacity', 1);
            $.each(data, function(i, zone) {
              $("select[name='zone_code']").append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
            });
          } else {
            $("select[name='zone_code']").attr('disabled', 'disabled');
            $("select[name='zone_code']").closest('td').css('opacity', 0.15);
          }
        },
        complete: function() {
          $('body').css('cursor', 'auto');
        }
      });
    });

    if ($("select[name='country_code']").find('option:selected').data('tax-id-format') != '') {
      $("select[name='country_code']").closest('table').find("input[name='tax_id']").attr('pattern', $("select[name='country_code']").find('option:selected').data('tax-id-format'));
    } else {
      $("select[name='country_code']").closest('table').find("input[name='tax_id']").removeAttr('pattern');
    }

    if ($("select[name='country_code']").find('option:selected').data('postcode-format') != '') {
      $("select[name='country_code']").closest('table').find("input[name='postcode']").attr('pattern', $("select[name='country_code']").find('option:selected').data('postcode-format'));
      $("select[name='country_code']").closest('table').find("input[name='postcode']").attr('required', 'required');
      $("select[name='country_code']").closest('table').find("input[name='postcode']").closest('td').find('.required').show();
    } else {
      $("select[name='country_code']").closest('table').find("input[name='postcode']").removeAttr('pattern');
      $("select[name='country_code']").closest('table').find("input[name='postcode']").removeAttr('required');
      $("select[name='country_code']").closest('table').find("input[name='postcode']").closest('td').find('.required').hide();
    }

    if ($("select[name='country_code']").find('option:selected').data('phone-code') != '') {
      $("select[name='country_code']").closest('table').find("input[name='phone']").attr('placeholder', '+' + $("select[name='country_code']").find('option:selected').data('phone-code'));
    } else {
      $("select[name='country_code']").closest('table').find("input[name='phone']").removeAttr('placeholder');
    }

    if ($("select[name='shipping_address[country_code]']").find('option:selected').data('postcode-format') != '') {
      $("select[name='shipping_address[country_code]']").closest('table').find("input[name='shipping_address[postcode]']").attr('pattern', $("select[name='shipping_address[country_code]']").find('option:selected').data('postcode-format'));
    } else {
      $("select[name='shipping_address[country_code]']").closest('table').find("input[name='shipping_address[postcode]']").removeAttr('pattern');
    }

    if ($("select[name='zone_code'] option").length == 0) $("select[name='zone_code']").closest('td').css('opacity', 0.15);
  </script>
</div>