<style>
.form-group:hover, .form-group:focus {
  opacity: 1 !important;
}
</style>

<aside id="sidebar">
  <div id="column-left">
    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_customer_service_links.inc.php'); ?>
    <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_account_links.inc.php'); ?>
  </div>
</aside>

<main id="content">
  <!--snippet:notices-->
  <!--snippet:breadcrumbs-->

  <div id="box-create-account" class="box">

    <h1><?php echo language::translate('title_create_account', 'Create Account'); ?></h1>

    <?php echo functions::form_draw_form_begin('customer_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_tax_id', 'Tax ID'); ?></label>
          <?php echo functions::form_draw_text_field('tax_id', true); ?>
        </div>

        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_company', 'Company'); ?></label>
          <?php echo functions::form_draw_text_field('company', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
          <?php echo functions::form_draw_text_field('firstname', true, 'required="required"'); ?>
        </div>

        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
          <?php echo functions::form_draw_text_field('lastname', true, 'required="required"'); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
          <?php echo functions::form_draw_text_field('address1', true, 'required="required"'); ?>
        </div>

        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
          <?php echo functions::form_draw_text_field('address2', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_postcode', 'Postcode'); ?></label>
          <?php echo functions::form_draw_text_field('postcode', true); ?>
        </div>

        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_city', 'City'); ?></label>
          <?php echo functions::form_draw_text_field('city', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_country', 'Country'); ?></label>
          <?php echo functions::form_draw_countries_list('country_code', true); ?>
        </div>

        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
          <?php echo form_draw_zones_list(isset($_POST['country_code']) ? $_POST['country_code'] : '', 'zone_code', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_email', 'Email'); ?></label>
          <?php echo functions::form_draw_email_field('email', true, 'required="required"'); ?>
        </div>

        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_phone', 'Phone'); ?></label>
          <?php echo functions::form_draw_phone_field('phone', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_desired_password', 'Desired Password'); ?></label>
          <?php echo functions::form_draw_password_field('password', '', 'required="required"'); ?>
        </div>

        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_confirm_password', 'Confirm Password'); ?></label>
          <?php echo functions::form_draw_password_field('confirmed_password', '', 'required="required"'); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_newsletter', 'Newsletter'); ?></label>
          <div class="checkbox">
            <label><?php echo functions::form_draw_checkbox('newsletter', '1', true); ?> <?php echo language::translate('title_subscribe', 'Subscribe'); ?></label>
          </div>
        </div>

        <?php if (settings::get('captcha_enabled')) { ?>
        <div class="form-group col-md-halfs">
          <label><?php echo language::translate('title_captcha', 'CAPTCHA'); ?></label>
          <?php echo functions::form_draw_captcha_field('captcha', 'create_account', 'required="required"'); ?>
        </div>
        <?php } ?>
      </div>

      <p><a class="view-all" href="#"><?php echo language::translate('title_view_all', 'View All'); ?></a></p>

      <div class="btn-group">
        <?php echo functions::form_draw_button('create_account', language::translate('title_create_account', 'Create Account')); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</main>

<script>
  $('#box-create-account :input:not([type="hidden"]):not([required])').closest('.form-group').css('opacity', '0.25');
  $('#box-create-account .view-all').click(function(e) {
    e.preventDefault();
    $('#box-create-account .form-group').css('opacity', '');
  });

  $('#box-create-account').on('input propertychange', ':input', function() {
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

  $('select[name="country_code"]').change(function(){
    if ($(this).find('option:selected').data('tax-id-format') != '') {
      $(this).closest('table').find('input[name="tax_id"]').attr('pattern', $(this).find('option:selected').data('tax-id-format'));
    } else {
      $(this).closest('table').find('input[name="tax_id"]').removeAttr('pattern');
    }

    if ($(this).find('option:selected').data('postcode-format') != '') {
      $(this).closest('table').find('input[name="postcode"]').attr('pattern', $(this).find('option:selected').data('postcode-format'));
      $(this).closest('table').find('input[name="postcode"]').attr('required', 'required');
      $(this).closest('table').find('input[name="postcode"]').closest('td').find('.required').show();
    } else {
      $(this).closest('table').find('input[name="postcode"]').removeAttr('pattern');
      $(this).closest('table').find('input[name="postcode"]').removeAttr('required');
      $(this).closest('table').find('input[name="postcode"]').closest('td').find('.required').hide();
    }

    if ($(this).find('option:selected').data('phone-code') != '') {
      $(this).closest('table').find('input[name="phone"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'));
    } else {
      $(this).closest('table').find('input[name="phone"]').removeAttr('placeholder');
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
        $('select[name="zone_code"]').html('');
        if (data.length) {
          $('select[name="zone_code"]').removeAttr('disabled');
          $('select[name="zone_code"]').closest('td').css('opacity', 1);
          $.each(data, function(i, zone) {
            $('select[name="zone_code"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="zone_code"]').attr('disabled', 'disabled');
          $('select[name="zone_code"]').closest('td').css('opacity', 0.15);
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });

  if ($('select[name="country_code"]').find('option:selected').data('tax-id-format') != '') {
    $('select[name="country_code"]').closest('table').find("input[name='tax_id']").attr('pattern', $('select[name="country_code"]').find('option:selected').data('tax-id-format'));
  } else {
    $('select[name="country_code"]').closest('table').find("input[name='tax_id']").removeAttr('pattern');
  }

  if ($('select[name="country_code"]').find('option:selected').data('postcode-format') != '') {
    $('select[name="country_code"]').closest('table').find('input[name="postcode"]').attr('pattern', $('select[name="country_code"]').find('option:selected').data('postcode-format'));
    $('select[name="country_code"]').closest('table').find('input[name="postcode"]').attr('required', 'required');
    $('select[name="country_code"]').closest('table').find('input[name="postcode"]').closest('td').find('.required').show();
  } else {
    $('select[name="country_code"]').closest('table').find('input[name="postcode"]').removeAttr('pattern');
    $('select[name="country_code"]').closest('table').find('input[name="postcode"]').removeAttr('required');
    $('select[name="country_code"]').closest('table').find('input[name="postcode"]').closest('td').find('.required').hide();
  }

  if ($("select[name='country_code']").find('option:selected').data('phone-code') != '') {
    $('select[name="country_code"]').closest('table').find('input[name="phone"]').attr('placeholder', '+' + $('select[name="country_code"]').find('option:selected').data('phone-code'));
  } else {
    $('select[name="country_code"]').closest('table').find('input[name="phone"]').removeAttr('placeholder');
  }

  if ($('select[name="shipping_address[country_code]"]').find('option:selected').data('postcode-format') != '') {
    $('select[name="shipping_address[country_code]"]').closest('table').find('input[name="shipping_address[postcode]"]').attr('pattern', $('select[name="shipping_address[country_code]"]').find('option:selected').data('postcode-format'));
  } else {
    $('select[name="shipping_address[country_code]"]').closest('table').find('input[name="shipping_address[postcode]"]').removeAttr('pattern');
  }

  if ($('select[name="zone_code"] option').length == 0) $('select[name="zone_code"]').closest('td').css('opacity', 0.15);
</script>