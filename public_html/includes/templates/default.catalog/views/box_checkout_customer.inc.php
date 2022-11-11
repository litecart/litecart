<section id="box-checkout-customer" class="card">
  <?php echo functions::form_draw_hidden_field('customer_details', 'true'); ?>

  <div class="card-header">
    <?php if (settings::get('accounts_enabled') && empty(customer::$data['id'])) { ?>
    <a class="btn btn-default float-end" href="<?php echo document::ilink('login', ['redirect_url' => document::ilink('checkout')]) ?>#box-login" data-toggle="lightbox" data-seamless="true" data-require-window-width="768"><?php echo language::translate('title_sign_in', 'Sign In'); ?></a>
    <?php } ?>

    <h2 class="card-title"><?php echo language::translate('title_customer_details', 'Customer Details'); ?></h2>
  </div>

  <div class="card-body">
    <div class="address billing-address">

      <?php if (settings::get('customer_field_company') || settings::get('customer_field_tax_id')) { ?>
      <div class="row">
        <?php if (settings::get('customer_field_company')) { ?>
        <div class="form-group col-xs-6">
          <label><?php echo language::translate('title_company', 'Company'); ?> (<?php echo language::translate('text_or_leave_blank', 'Or leave blank'); ?>)</label>
          <?php echo functions::form_draw_text_field('company', true); ?>
        </div>
        <?php } ?>

        <?php if (settings::get('customer_field_tax_id')) { ?>
        <div class="form-group col-xs-6">
          <label><?php echo language::translate('title_tax_id', 'Tax ID'); ?> (<?php echo language::translate('text_or_leave_blank', 'Or leave blank'); ?>)</label>
          <?php echo functions::form_draw_text_field('tax_id', true); ?>
        </div>
        <?php } ?>
      </div>
      <?php } ?>

      <div class="row">
        <div class="form-group col-xs-6">
          <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
          <?php echo functions::form_draw_text_field('firstname', true, 'required'); ?>
        </div>

        <div class="form-group col-xs-6">
          <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
          <?php echo functions::form_draw_text_field('lastname', true, 'required'); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-sm-6">
          <label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
          <?php echo functions::form_draw_text_field('address1', true, 'required'); ?>
        </div>

        <div class="form-group col-sm-6">
          <label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
          <?php echo functions::form_draw_text_field('address2', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-xs-4 col-sm-6">
          <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
          <?php echo functions::form_draw_text_field('postcode', true); ?>
        </div>

        <div class="form-group col-xs-8 col-sm-6">
          <label><?php echo language::translate('title_city', 'City'); ?></label>
          <?php echo functions::form_draw_text_field('city', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-xs-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
          <label><?php echo language::translate('title_country', 'Country'); ?></label>
          <?php echo functions::form_draw_countries_list('country_code', true); ?>
        </div>

        <?php if (settings::get('customer_field_zone')) { ?>
        <div class="form-group col-xs-6">
          <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
          <?php echo functions::form_draw_zones_list(isset($_POST['country_code']) ? $_POST['country_code'] : '', 'zone_code', true); ?>
        </div>
        <?php } ?>
      </div>

      <div class="row">
        <div class="form-group col-xs-6">
          <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
          <?php echo functions::form_draw_email_field('email', true, 'required'. (!empty(customer::$data['id']) ? ' readonly' : '')); ?>
        </div>

        <div class="form-group col-xs-6">
          <label><?php echo language::translate('title_phone', 'Phone'); ?></label>
          <?php echo functions::form_draw_phone_field('phone', true, 'required'); ?>
        </div>
      </div>
    </div>

    <div class="address shipping-address">

      <h3><?php echo functions::form_draw_checkbox('different_shipping_address', '1', !empty($_POST['different_shipping_address']) ? '1' : '', 'style="margin: 0px;"'); ?> <?php echo language::translate('title_different_shipping_address', 'Different Shipping Address'); ?></h3>

      <fieldset<?php echo (empty($_POST['different_shipping_address'])) ? ' style="display: none;" disabled' : false; ?>>

        <?php if (settings::get('customer_field_company')) { ?>
        <div class="row">
          <div class="form-group col-xs-6">
          <label><?php echo language::translate('title_company', 'Company'); ?> (<?php echo language::translate('text_or_leave_blank', 'Or leave blank'); ?>)</label>
            <?php echo functions::form_draw_text_field('shipping_address[company]', true); ?>
          </div>
        </div>
        <?php } ?>

        <div class="row">
          <div class="form-group col-xs-6">
            <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
            <?php echo functions::form_draw_text_field('shipping_address[firstname]', true); ?>
          </div>

          <div class="form-group col-xs-6">
            <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
            <?php echo functions::form_draw_text_field('shipping_address[lastname]', true); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-sm-6">
            <label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
            <?php echo functions::form_draw_text_field('shipping_address[address1]', true); ?>
          </div>

          <div class="form-group col-sm-6">
            <label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
            <?php echo functions::form_draw_text_field('shipping_address[address2]', true); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-xs-4">
            <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
            <?php echo functions::form_draw_text_field('shipping_address[postcode]', true); ?>
          </div>

          <div class="form-group col-xs-8">
            <label><?php echo language::translate('title_city', 'City'); ?></label>
            <?php echo functions::form_draw_text_field('shipping_address[city]', true); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-xs-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
            <label><?php echo language::translate('title_country', 'Country'); ?></label>
            <?php echo functions::form_draw_countries_list('shipping_address[country_code]', true); ?>
          </div>

          <?php if (settings::get('customer_field_zone')) { ?>
          <div class="form-group col-xs-6">
            <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
            <?php echo functions::form_draw_zones_list(isset($_POST['shipping_address']['country_code']) ? $_POST['shipping_address']['country_code'] : $_POST['country_code'], 'shipping_address[zone_code]', true); ?>
          </div>
          <?php } ?>
        </div>

        <div class="row">
          <div class="form-group col-xs-6">
            <label><?php echo language::translate('title_phone', 'Phone'); ?></label>
            <?php echo functions::form_draw_phone_field('shipping_address[phone]', true); ?>
          </div>
        </div>

      </fieldset>
    </div>

    <?php if (!empty(customer::$data['id'])) { ?>
    <p><?php echo functions::form_draw_checkbox('save_to_account', '1', true, 'style="margin: 0px;"'); ?> <?php echo language::translate('title_save_details_to_my_account', 'Save details to my account'); ?></p>
    <?php } ?>

    <?php if (!$subscribed_to_newsletter) { ?>
    <div class="form-group">
      <label class="checkbox"><?php echo functions::form_draw_checkbox('newsletter', '1', true); ?> <?php echo language::translate('consent_newsletter', 'I would like to be notified occasionally via e-mail when there are new products or campaigns.'); ?></label>
    </div>
    <?php } ?>

    <?php if (settings::get('accounts_enabled') && empty(customer::$data['id'])) { ?>
    <div class="account">

      <?php if (!empty($account_exists)) { ?>

      <div class="alert alert-info">
        <?php echo functions::draw_fonticon('fa-info-circle'); ?> <?php echo language::translate('notice_existing_customer_account_will_be_used', 'We found an existing customer account that will be used for this order'); ?>
      </div>

      <?php } else { ?>

      <h3><?php echo functions::form_draw_checkbox('create_account', '1', true, 'style="margin: 0px;"'); ?> <?php echo language::translate('title_create_account', 'Create Account'); ?></h3>

      <fieldset<?php echo (empty($_POST['create_account'])) ? ' style="display: none;" disabled' : false; ?>>

        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label><?php echo language::translate('title_desired_password', 'Desired Password'); ?></label>
              <?php echo functions::form_draw_password_field('password', '', 'autocomplete="new-password" data-toggle="password-strength"'); ?>
            </div>
          </div>

          <div class="col-sm-6">
            <div class="form-group">
              <label><?php echo language::translate('title_confirm_password', 'Confirm Password'); ?></label>
              <?php echo functions::form_draw_password_field('confirmed_password', '', 'autocomplete="off"'); ?>
            </div>
          </div>
        </div>

      </fieldset>
      <?php } ?>
    </div>
    <?php } ?>

    <div>
      <button class="btn btn-block btn-default" name="save_customer_details" type="submit" disabled><?php echo language::translate('title_save_changes', 'Save Changes'); ?></button>
    </div>

  </div>
</section>

<script>
  <?php if (!empty(notices::$data['errors'])) { ?>
  alert("<?php echo functions::escape_js(notices::$data['errors'][0]); notices::$data['errors'] = []; ?>");
  <?php } ?>

// Initiate fields

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

  if ($('select[name="shipping_address[country_code]"] option:selected').data('postcode-format')) {
    $('input[name="shipping_address[postcode]"]').attr('pattern', $('select[name="shipping_address[country_code]"] option:selected').data('postcode-format'));
  } else {
    $('input[name="shipping_address[postcode]"]').removeAttr('pattern');
  }

  if ($('select[name="shipping_address[country_code]"] option:selected').data('phone-code')) {
    $('input[name="shipping_address[phone]"]').attr('placeholder', '+' + $('select[name="shipping_address[country_code]"] option:selected').data('phone-code'));
  } else {
    $('input[name="shipping_address[phone]"]').removeAttr('placeholder');
  }

  $('input[name="create_account"][type="checkbox"]').trigger('change');

  window.customer_form_changed = false;
  window.customer_form_checksum = $('#box-checkout-customer :input').serialize();
</script>