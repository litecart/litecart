<div class="box" id="box-checkout-customer">
  <div class="heading"><h2><?php echo language::translate('title_customer_information', 'Customer Information'); ?></h2></div>
  <div class="content">
    <?php echo functions::form_draw_form_begin('customer_form', 'post'); ?>
      <div class="billing-address">
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
              <?php echo functions::form_draw_text_field('postcode', true, 'required="required" style="width: 50px;"'); ?></td>
            <td><?php echo language::translate('title_city', 'City'); ?> <span class="required">*</span><br />
              <?php echo functions::form_draw_text_field('city', true, 'required="required"'); ?></td>
          </tr>
          <tr>
            <td><?php echo language::translate('title_country', 'Country'); ?> <span class="required">*</span><br />
              <?php echo functions::form_draw_countries_list('country_code', true, false, 'required="required"'); ?></td>
            <td><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?> <span class="required">*</span><br />
              <?php echo functions::form_draw_zones_list(isset($_POST['country_code']) ? $_POST['country_code'] : '', 'zone_code', true); ?></td>
          </tr>
          <?php if (empty(customer::$data['id'])) { ?>
          <tr>
            <td width="50%"><?php echo language::translate('title_email', 'E-mail'); ?> <span class="required">*</span><br />
              <?php echo functions::form_draw_email_field('email', true, 'required="required"'. (!empty(customer::$data['id']) ? ' readonly="readonly"' : '')); ?></td>
            <td><?php echo language::translate('title_phone', 'Phone'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_phone_field('phone', true, 'required="required" placeholder="'. functions::reference_get_phone_country_code(isset($_POST['country_code']) ? $_POST['country_code'] : '') .'"'); ?></td>
          </tr>
          <?php if (settings::get('register_guests') && settings::get('fields_customer_password')) { ?>
          <?php if (empty($_POST['email']) || !database::num_rows(database::query("select id from ". DB_TABLE_CUSTOMERS ." where email = '". database::input($_POST['email']) ."' limit 1;"))) { ?>
          <tr>
            <td><?php echo language::translate('title_password', 'Password'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_password_field('password', '', 'required="required"'); ?></td>
            <td nowrap="nowrap"><?php echo language::translate('title_confirm_password', 'Confirm Password'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_password_field('confirmed_password', '', 'required="required"'); ?></td>
          </tr>
          <?php } ?>
          <?php } ?>
          <?php } ?>
          <tr>
            <td colspan="2"><?php echo functions::form_draw_button('set_addresses', language::translate('title_save_changes', 'Save Changes'), 'submit', 'disabled="disabled"'); ?></td>
          </tr>
        </table>
      </div>
      
      <div class="shipping-address">
        <h3><?php echo functions::form_draw_checkbox('different_shipping_address', '1', empty($_POST['different_shipping_address']) ? '' : '1', 'style="margin: 0px;" onclick="if (this.checked == true) $(\'#shipping-address-container\').slideDown(); else $(\'#shipping-address-container\').slideUp();"'); ?> <?php echo language::translate('title_different_shipping_address', 'Different Shipping Address'); ?></h3>
        <div id="shipping-address-container"<?php echo (empty($_POST['different_shipping_address'])) ? ' style="display: none;"' : false; ?>>
          <table>
            <tr>
              <td><?php echo language::translate('title_company', 'Company'); ?><br />
                <?php echo functions::form_draw_text_field('shipping_address[company]', !empty($_POST['different_shipping_address']) ? true : ''); ?></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td><?php echo language::translate('title_firstname', 'First Name'); ?> <span class="required">*</span><br />
                <?php echo functions::form_draw_text_field('shipping_address[firstname]', !empty($_POST['different_shipping_address']) ? true : ''); ?></td>
              <td><?php echo language::translate('title_lastname', 'Last Name'); ?> <span class="required">*</span><br />
                <?php echo functions::form_draw_text_field('shipping_address[lastname]', !empty($_POST['different_shipping_address']) ? true : ''); ?></td>
            </tr>
            <tr>
              <td><?php echo language::translate('title_address1', 'Address 1'); ?> <span class="required">*</span><br />
                <?php echo functions::form_draw_text_field('shipping_address[address1]', !empty($_POST['different_shipping_address']) ? true : ''); ?></td>
              <td><?php echo language::translate('title_address2', 'Address 2'); ?><br />
                <?php echo functions::form_draw_text_field('shipping_address[address2]', !empty($_POST['different_shipping_address']) ? true : ''); ?></td>
            </tr>
            <tr>
              <td><?php echo language::translate('title_postcode', 'Postcode'); ?> <span class="required">*</span><br />
                <?php echo functions::form_draw_text_field('shipping_address[postcode]', !empty($_POST['different_shipping_address']) ? true : '', 'required="required" style="width: 50px;"'); ?></td>
              <td><?php echo language::translate('title_city', 'City'); ?> <span class="required">*</span><br />
                <?php echo functions::form_draw_text_field('shipping_address[city]', !empty($_POST['different_shipping_address']) ? true : ''); ?></td>
            </tr>
            <tr>
              <td><?php echo language::translate('title_country', 'Country'); ?> <span class="required">*</span><br />
                <?php echo functions::form_draw_countries_list('shipping_address[country_code]', isset($_POST['shipping_address']['country_code']) ? $_POST['shipping_address']['country_code'] : (isset($_POST['country_code']) ? $_POST['country_code'] : ''), ''); ?></td>
              <td><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?> <span class="required">*</span><br />
                <?php echo functions::form_draw_zones_list(isset($_POST['shipping_address']['country_code']) ? $_POST['shipping_address']['country_code'] : (isset($_POST['country_code']) ? $_POST['country_code'] : ''), 'shipping_address[zone_code]', true, ''); ?></td>
            </tr>
            <tr>
              <td colspan="2"><?php echo functions::form_draw_button('set_addresses', language::translate('title_save_changes', 'Save Changes'), 'submit', 'disabled="disabled"'); ?></td>
            </tr>
          </table>
        </div>
        
      </div>
      
    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
  $("#box-checkout-account input, #box-checkout-account select").change(function() {
    if ($(this).val() == '') return;
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
        }
        $.each(data, function(key, value) {
          if (console) console.log(key +": "+ value);
          if ($("#box-checkout-account *[name='"+key+"']").length && $("#box-checkout-account *[name='"+key+"']").val() == '') $("#box-checkout-account *[name='"+key+"']").val(data[key]);
        });
      },
    });
  });
  
  $("select[name='country_code']").change(function(){
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
        if ($("select[name='zone_code']").attr('disabled')) $("select[name='zone_code']").removeAttr('disabled');
        if (data) {
          $.each(data, function(i, zone) {
            $("select[name='zone_code']").append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $("select[name='zone_code']").attr('disabled', 'disabled');
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });
  
  $("select[name='shipping_address[country_code]']").change(function(){
    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
      type: 'get',
      cache: true,
      async: false,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        if (console) console.warn(errorThrown.message);
      },
      success: function(data) {
        $("select[name='shipping_address[zone_code]']").html('');
        if ($("select[name='shipping_address[zone_code]']").attr('disabled')) $("select[name='shipping_address[zone_code]']").removeAttr('disabled');
        if (data) {
          $.each(data, function(i, zone) {
            $("select[name='shipping_address[zone_code]']").append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $("select[name='shipping_address[zone_code]']").attr('disabled', 'disabled');
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });
</script>