<main id="main" class="container">
  <div class="row layout">
    <div class="col-md-3">
      <div id="sidebar">
        <?php include 'app://frontend/partials/box_account_links.inc.php'; ?>
      </div>
    </div>

    <div class="col-md-9">
      <div id="content">
        {{notices}}

        <section id="box-edit-account" class="card">
          <div class="card-header">
            <h1 class="card-title"><?php echo language::translate('title_sign_in_and_security', 'Sign-In and Security'); ?></h1>
          </div>

          <div class="card-body">
            <?php echo functions::form_begin('customer_account_form', 'post', null, false, 'style="max-width: 640px;"'); ?>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
                  <?php echo functions::form_email_field('email', true, 'required'); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_password', 'Password'); ?></label>
                  <?php echo functions::form_password_field('password', '', 'required'); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_new_password', 'New Password'); ?> (<?php echo language::translate('text_or_leave_blank', 'Or leave blank'); ?>)</label>
                  <?php echo functions::form_password_field('new_password', ''); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_confirm_new_password', 'Confirm New Password'); ?></label>
                  <?php echo functions::form_password_field('confirmed_password', ''); ?>
                </div>
              </div>

              <p><?php echo functions::form_button('save_account', language::translate('title_save', 'Save')); ?></p>

            <?php echo functions::form_end(); ?>
          </div>
        </section>

        <section id="box-edit-details" class="card">
          <div class="card-header">
            <h1 class="card-title"><?php echo language::translate('title_customer_profile', 'Customer Profile'); ?></h1>
          </div>

          <div class="card-body">
            <?php echo functions::form_begin('customer_details_form', 'post', null, false, 'style="max-width: 640px;"'); ?>

              <?php if (settings::get('customer_field_company') || settings::get('customer_field_tax_id')) { ?>
              <div class="form-group">
                <?php echo functions::form_toggle_buttons('type', ['individual' => language::translate('title_individual', 'Individual'), 'company' => language::translate('title_company', 'Company')], empty($_POST['type']) ? 'individual' : true); ?>
              </div>

              <div class="company-details" <?php echo (empty($_POST['type']) || $_POST['type'] == 'individual') ? 'style="display: none;"' : ''; ?>>
                <div class="row">
                  <?php if (settings::get('customer_field_company')) { ?>
                  <div class="form-group col-6">
                    <label><?php echo language::translate('title_company_name', 'Company Name'); ?></label>
                    <?php echo functions::form_text_field('company', true, 'required'); ?>
                  </div>
                  <?php } ?>

                  <?php if (settings::get('customer_field_tax_id')) { ?>
                  <div class="form-group col-6">
                    <label><?php echo language::translate('title_tax_id', 'Tax ID'); ?></label>
                    <?php echo functions::form_text_field('tax_id', true); ?>
                  </div>
                  <?php } ?>
                </div>
              </div>
              <?php } ?>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
                  <?php echo functions::form_text_field('firstname', true, 'required'); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
                  <?php echo functions::form_text_field('lastname', true, 'required'); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
                  <?php echo functions::form_text_field('address1', true, 'required'); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
                  <?php echo functions::form_text_field('address2', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
                  <?php echo functions::form_text_field('postcode', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_city', 'City'); ?></label>
                  <?php echo functions::form_text_field('city', true, 'required'); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_country', 'Country'); ?></label>
                  <?php echo functions::form_countries_list('country_code', true, 'required'); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
                  <?php echo form_zones_list('zone_code', fallback($_POST['country_code']), true, 'required'); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_phone_number', 'Phone Number'); ?></label>
                  <?php echo functions::form_phone_field('phone', true, 'required placeholder="'. (isset($_POST['country_code']) ? reference::country($_POST['country_code'])->phone_code : '') .'"'); ?>
                </div>
              </div>

              <div class="form-group">
                <?php echo functions::form_checkbox('newsletter', ['1', language::translate('consent_newsletter', 'I would like to be notified occasionally via e-mail when there are new products or campaigns.')], true); ?>
              </div>

              <?php if (settings::get('customer_shipping_address')) { ?>
              <div class="address shipping-address">

                <h3><?php echo functions::form_checkbox('different_shipping_address', ['1', language::translate('title_different_shipping_address', 'Different Shipping Address')], !empty($_POST['different_shipping_address']) ? '1' : '', 'style="margin: 0px;"'); ?></h3>

                <fieldset<?php echo (empty($_POST['different_shipping_address'])) ? ' style="display: none;" disabled' : false; ?>>

                  <?php if (settings::get('customer_field_company')) { ?>
                  <div class="row">
                    <div class="form-group col-6">
                    <label><?php echo language::translate('title_company_name', 'Company Name'); ?></label>
                      <?php echo functions::form_text_field('shipping_address[company]', true); ?>
                    </div>
                  </div>
                  <?php } ?>

                  <div class="row">
                    <div class="form-group col-6">
                      <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
                      <?php echo functions::form_text_field('shipping_address[firstname]', true); ?>
                    </div>

                    <div class="form-group col-6">
                      <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
                      <?php echo functions::form_text_field('shipping_address[lastname]', true); ?>
                    </div>
                  </div>

                  <div class="row">
                    <div class="form-group col-6">
                      <label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
                      <?php echo functions::form_text_field('shipping_address[address1]', true); ?>
                    </div>

                    <div class="form-group col-6">
                      <label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
                      <?php echo functions::form_text_field('shipping_address[address2]', true); ?>
                    </div>
                  </div>

                  <div class="row">
                    <div class="form-group col-6">
                      <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
                      <?php echo functions::form_text_field('shipping_address[postcode]', true); ?>
                    </div>

                    <div class="form-group col-6">
                      <label><?php echo language::translate('title_city', 'City'); ?></label>
                      <?php echo functions::form_text_field('shipping_address[city]', true); ?>
                    </div>
                  </div>

                  <div class="row">
                    <div class="form-group col-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
                      <label><?php echo language::translate('title_country', 'Country'); ?></label>
                      <?php echo functions::form_countries_list('shipping_address[country_code]', true); ?>
                    </div>

                    <?php if (settings::get('customer_field_zone')) { ?>
                    <div class="form-group col-6">
                      <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
                      <?php echo functions::form_zones_list('shipping_address[zone_code]', fallback($_POST['shipping_address']['country_code'], $_POST['country_code']), true); ?>
                    </div>
                    <?php } ?>
                  </div>

                  <div class="row">
                    <div class="form-group col-6">
                      <label><?php echo language::translate('title_phone_number', 'Phone Number'); ?></label>
                      <?php echo functions::form_phone_field('shipping_address[phone]', true); ?>
                    </div>
                  </div>

                </fieldset>
              </div>
              <?php } ?>

              <div>
                <?php echo functions::form_button('save_details', language::translate('title_save', 'Save')); ?>
              </div>

            <?php echo functions::form_end(); ?>
          </div>
        </section>
      </div>
    </div>
  </div>
</main>

<script>
  $('input[name="type"]').change(function(){
    if ($(this).val() == 'company') {
      $('.company-details :input').prop('disabled', false);
      $('.company-details').slideDown('fast');
    } else {
      $('.company-details :input').prop('disabled', true);
      $('.company-details').slideUp('fast');
    }
  }).first().trigger('change');

  $('form[name="customer_form"]').on('input', ':input', function() {
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
          console.log(key +' '+ value);
          if ($('input[name="'+key+'"]').length && $('input[name="'+key+'"]').val() == '') $('input[name="'+key+'"]').val(data[key]);
        });
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
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