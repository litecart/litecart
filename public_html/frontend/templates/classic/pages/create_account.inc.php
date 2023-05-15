<main id="main" class="container">
  <div id="sidebar">
    <div id="column-left">
      <?php include 'app://frontend/partials/box_account_links.inc.php'; ?>
    </div>
  </div>

  <div id="content">
    {{notices}}
    {{breadcrumbs}}

    <section id="box-create-account">

      <h1><?php echo language::translate('title_create_account', 'Create Account'); ?></h1>

      <?php echo functions::form_begin('customer_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

        <?php if (settings::get('customer_field_company') || settings::get('customer_field_tax_id')) { ?>
        <div class="row">
          <?php if (settings::get('customer_field_company')) { ?>
          <div class="form-group col-6">
            <label><?php echo language::translate('title_company_name', 'Company Name'); ?> (<?php echo language::translate('text_or_leave_blank', 'Or leave blank'); ?>)</label>
            <?php echo functions::form_text_field('company', true); ?>
          </div>
          <?php } ?>

          <?php if (settings::get('customer_field_tax_id')) { ?>
          <div class="form-group col-6">
            <label><?php echo language::translate('title_tax_id', 'Tax ID'); ?> (<?php echo language::translate('text_or_leave_blank', 'Or leave blank'); ?>)</label>
            <?php echo functions::form_text_field('tax_id', true); ?>
          </div>
          <?php } ?>
        </div>
        <?php } ?>

        <div class="row">
          <div class="form-group col-6">
            <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
            <?php echo functions::form_text_field('firstname', true, 'required'); ?>
          </div>

          <div class="form-group col-6">
            <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
            <?php echo functions::form_text_field('lastname', true, 'required'); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-6">
            <label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
            <?php echo functions::form_text_field('address1', true); ?>
          </div>

          <div class="form-group col-6">
            <label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
            <?php echo functions::form_text_field('address2', true); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-6">
            <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
            <?php echo functions::form_text_field('postcode', true); ?>
          </div>

          <div class="form-group col-6">
            <label><?php echo language::translate('title_city', 'City'); ?></label>
            <?php echo functions::form_text_field('city', true); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
            <label><?php echo language::translate('title_country', 'Country'); ?></label>
            <?php echo functions::form_countries_list('country_code', true, 'required'); ?>
          </div>

          <?php if (settings::get('customer_field_zone')) { ?>
          <div class="form-group col-6">
            <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
            <?php echo functions::form_zones_list('zone_code', fallback($_POST['country_code']), true, 'required'); ?>
          </div>
          <?php } ?>
        </div>

        <div class="row">
          <div class="form-group col-6">
            <label><?php echo language::translate('title_email', 'Email'); ?></label>
            <?php echo functions::form_email_field('email', true, 'required'); ?>
          </div>

          <div class="form-group col-6">
            <label><?php echo language::translate('title_phone_number', 'Phone Number'); ?></label>
            <?php echo functions::form_phone_field('phone', true); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-6">
            <label><?php echo language::translate('title_desired_password', 'Desired Password'); ?></label>
            <?php echo functions::form_password_field('password', '', 'required'); ?>
          </div>

          <div class="form-group col-6">
            <label><?php echo language::translate('title_confirm_password', 'Confirm Password'); ?></label>
            <?php echo functions::form_password_field('confirmed_password', '', 'required'); ?>
          </div>
        </div>

        <div class="form-group">
          <?php echo functions::form_checkbox('newsletter', ['1', language::translate('consent_newsletter', 'I would like to be notified occasionally via e-mail when there are new products or campaigns.')], true); ?>
        </div>

        <?php if ($consent) { ?>
        <p class="consent">
          <?php echo functions::form_checkbox('terms_agreed', ['1', $consent], true, 'required'); ?>
        </p>
        <?php } ?>

        <?php if (settings::get('captcha_enabled')) { ?>
        <div class="row">
          <div class="form-group col-6">
            <label><?php echo language::translate('title_captcha', 'CAPTCHA'); ?></label>
            <?php echo functions::form_captcha_field('captcha', 'create_account', 'required'); ?>
          </div>
        </div>
        <?php } ?>

        <div class="btn-group">
          <?php echo functions::form_button('create_account', language::translate('title_create_account', 'Create Account')); ?>
        </div>

      <?php echo functions::form_end(); ?>
    </section>
  </div>
</main>

<script>
  $('#box-create-account').on('change', ':input', function() {
    if ($(this).val() == '') return;

    $('body').css('cursor', 'wait');

    $.getJSON(
      '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
      $('.billing-address :input').serialize(),
      function(data) {
        if (data['alert']) alert(data['alert']);
        $.each(data, function(key, value) {
          $('.billing-address :input[name="'+key+'"]').val(value);
        });
      }
    ).always(function(){
      $('body').css('cursor', 'auto');
    });
  });

  $('select[name="country_code"]').change(function(e) {

    if ($(this).find('option:selected').data('tax-id-format')) {
      $('input[name="tax_id"]').attr('pattern', $(this).find('option:selected').data('tax-id-format'));
    } else {
      $('input[name="tax_id"]').removeAttr('pattern');
    }

    if ($(this).find('option:selected').data('postcode-format')) {
      $('input[name="postcode"]').attr('pattern', $(this).find('option:selected').data('postcode-format'));
    } else {
      $('input[name="postcode"]').removeAttr('pattern');
    }

    if ($(this).find('option:selected').data('phone-code')) {
      $('input[name="phone"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'));
    } else {
      $('input[name="phone"]').removeAttr('placeholder');
    }

    <?php if (settings::get('customer_field_zone')) { ?>
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
        if (data.length) {
          $('select[name="zone_code"]').prop('disabled', false);
          $.each(data, function(i, zone) {
            $('select[name="zone_code"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="zone_code"]').prop('disabled', true);
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
    <?php } ?>
  });

  if ($('select[name="country_code"] option:selected').data('tax-id-format')) {
    $('input[name="tax_id"]').attr('pattern', $('select[name="country_code"] option:selected').data('tax-id-format'));
  } else {
    $('input[name="tax_id"]').removeAttr('pattern');
  }

  if ($('select[name="country_code"] option:selected').data('postcode-format')) {
    $('input[name="postcode"]').attr('pattern', $('select[name="country_code"] option:selected').data('postcode-format'));
  } else {
    $('input[name="postcode"]').removeAttr('pattern');
  }

  if ($('select[name="country_code"] option:selected').data('phone-code')) {
    $('input[name="phone"]').attr('placeholder', '+' + $('select[name="country_code"] option:selected').data('phone-code'));
  } else {
    $('input[name="phone"]').removeAttr('placeholder');
  }
</script>