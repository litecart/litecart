<?php

  if (!empty($_GET['customer_id'])) {
    $customer = new ent_customer($_GET['customer_id']);
  } else {
    $customer = new ent_customer();
  }

  if (empty($_POST)) {
    $_POST = $customer->data;
  }

  document::$snippets['title'][] = !empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_add_new_customer', 'Add New Customer');

  breadcrumbs::add(language::translate('title_customers', 'Customers'), document::link(WS_DIR_ADMIN, ['doc' => 'customers'], ['app']));
  breadcrumbs::add(!empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_add_new_customer', 'Add New Customer'));

  if (isset($_POST['sign_in'])) {

    try {

      customer::load($_GET['customer_id']);

      session::$data['security.timestamp'] = time();
      session::regenerate_id();

      notices::add('success', strtr(language::translate('success_logged_in_as_user', 'You are now logged in as %firstname %lastname.'), [
        '%email' => customer::$data['email'],
        '%firstname' => customer::$data['firstname'],
        '%lastname' => customer::$data['lastname'],
      ]));

      header('Location: '. document::ilink(''));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['newsletter'])) $_POST['newsletter'] = 0;
      if (empty($_POST['different_shipping_address'])) $_POST['different_shipping_address'] = 0;

      $fields = [
        'code',
        'status',
        'email',
        'password',
        'tax_id',
        'company',
        'firstname',
        'lastname',
        'address1',
        'address2',
        'postcode',
        'city',
        'country_code',
        'zone_code',
        'phone',
        'newsletter',
        'notes',
        'different_shipping_address',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $customer->data[$field] = $_POST[$field];
      }

      $fields = [
        'company',
        'firstname',
        'lastname',
        'address1',
        'address2',
        'postcode',
        'city',
        'country_code',
        'zone_code',
        'phone',
      ];

      foreach ($fields as $field) {
        $customer->data['shipping_address'][$field] = !empty($_POST['shipping_address'][$field]) ? $_POST['shipping_address'][$field] : '';
      }

      $customer->save();

      if (!empty($_POST['new_password'])) $customer->set_password($_POST['new_password']);

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc' => 'customers']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($customer->data['id'])) throw new Exception(language::translate('error_must_provide_customer', 'You must provide a customer'));

      $customer->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc' => 'customers']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (!empty($customer->data['id'])) {

    $orders_query = database::query(
      "select count(o.id) as total_count, sum(oi.total_sales) as total_sales
      from ". DB_TABLE_PREFIX ."orders o
      left join (
        select order_id, sum(price * quantity) as total_sales from ". DB_TABLE_PREFIX ."orders_items
        group by order_id
      ) oi on (oi.order_id = o.id)
      where o.order_status_id in (
        select id from ". DB_TABLE_PREFIX ."order_statuses
        where is_sale
      )
      and (o.customer_id = ". (int)$customer->data['id'] ." or o.customer_email = '". database::input($customer->data['email']) ."');"
    );

    $orders = database::fetch($orders_query);
  }
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo !empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_add_new_customer', 'Add New Customer'); ?>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('customer_form', 'post', '', false, 'autocomplete="off"'); ?>

      <div class="row" style="max-width: 960px;">

        <div class="col-md-8">

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_status', 'Status'); ?></label>
              <?php echo functions::form_draw_toggle('status', (file_get_contents('php://input') != '') ? true : '1', 'e/d'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_code', 'Code'); ?></label>
              <?php echo functions::form_draw_text_field('code', true); ?>
            </div>
          </div>

          <?php if (!empty($customer->data['id'])) { ?>
          <div class="form-group">
            <?php echo functions::form_draw_button('sign_in', ['true', language::translate('text_sign_in_as_customer', 'Sign in as customer')], 'submit', 'class="btn btn-default btn-block"'); ?>
          </div>
          <?php } ?>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
              <?php echo functions::form_draw_email_field('email', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_newsletter', 'Newsletter'); ?></label>
              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('newsletter', '1', true); ?> <?php echo language::translate('title_subscribe', 'Subscribe'); ?></label>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_company', 'Company'); ?></label>
              <?php echo functions::form_draw_text_field('company', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_tax_id', 'Tax ID / VATIN'); ?></label>
              <?php echo functions::form_draw_text_field('tax_id', true); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
              <?php echo functions::form_draw_text_field('firstname', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
              <?php echo functions::form_draw_text_field('lastname', true); ?>
            </div>
            </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
              <?php echo functions::form_draw_text_field('address1', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
              <?php echo functions::form_draw_text_field('address2', true); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
              <?php echo functions::form_draw_text_field('postcode', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_city', 'City'); ?></label>
              <?php echo functions::form_draw_text_field('city', true); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_country', 'Country'); ?></label>
              <?php echo functions::form_draw_countries_list('country_code', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_zone', 'Zone'); ?></label>
              <?php echo functions::form_draw_zones_list(isset($_POST['country_code']) ? $_POST['country_code'] : '', 'zone_code', true); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_phone', 'Phone'); ?></label>
              <?php echo functions::form_draw_phone_field('phone', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo !empty($customer->data['id']) ? language::translate('title_new_password', 'New Password') : language::translate('title_password', 'Password'); ?></label>
              <?php echo functions::form_draw_password_field('new_password', '', 'autocomplete="off"'); ?>
            </div>
          </div>

          <?php if (!empty($customer->data['id'])) { ?>
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_ip', 'Last IP'); ?></label>
              <?php echo functions::form_draw_text_field('last_ip', true, 'readonly'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_host', 'Last Host'); ?></label>
              <?php echo functions::form_draw_text_field('last_host', true, 'readonly'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_login', 'Last Login'); ?></label>
              <?php echo functions::form_draw_text_field('date_login', true, 'readonly'); ?>
            </div>
          </div>
          <?php } ?>

          <h3><?php echo functions::form_draw_checkbox('different_shipping_address', '1', !empty($_POST['different_shipping_address']) ? '1' : '', 'style="margin: 0px;"'); ?> <?php echo language::translate('title_different_shipping_address', 'Different Shipping Address'); ?></h3>

          <fieldset class="shipping-address"<?php echo (empty($_POST['different_shipping_address'])) ? ' style="display: none;" disabled' : false; ?>>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_company', 'Company'); ?></label>
                <?php echo functions::form_draw_text_field('shipping_address[company]', true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
                <?php echo functions::form_draw_text_field('shipping_address[firstname]', true); ?>
              </div>

              <div class="form-group col-sm-6">
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
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
                <?php echo functions::form_draw_text_field('shipping_address[postcode]', true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_city', 'City'); ?></label>
                <?php echo functions::form_draw_text_field('shipping_address[city]', true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_country', 'Country'); ?></label>
                <?php echo functions::form_draw_countries_list('shipping_address[country_code]', true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
                <?php echo functions::form_draw_zones_list(isset($_POST['shipping_address']['country_code']) ? $_POST['shipping_address']['country_code'] : $_POST['country_code'], 'shipping_address[zone_code]', true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_phone', 'Phone'); ?></label>
                <?php echo functions::form_draw_phone_field('shipping_address[phone]', true); ?>
              </div>
            </div>

          </fieldset>

          <div class="panel-action btn-group">
            <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
            <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
            <?php echo (isset($customer->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label><?php echo language::translate('title_notes', 'Notes'); ?></label>
            <?php echo functions::form_draw_textarea('notes', true, 'style="height: 450px;"'); ?>
          </div>

          <?php if (!empty($customer->data['id'])) { ?>
          <table class="table table-striped table-hover data-table">
            <tr>
              <td class="col-md-6"><?php echo language::translate('title_orders', 'Orders'); ?><br />
                <?php echo !empty($orders['total_count']) ? (int)$orders['total_count'] : '0'; ?>
              </td>
              <td class="col-md-6"><?php echo language::translate('title_total_sales', 'Total Sales'); ?><br />
                <?php echo currency::format(!empty($orders['total_sales']) ? $orders['total_sales'] : 0, false, settings::get('store_currency_code')); ?>
              </td>
            </tr>
          </table>
          <?php } ?>
        </div>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>

// Init

  if ($('select[name="country_code"]').find('option:selected').data('tax-id-format') != '') {
    $('select[name="country_code"]').closest('table').find('input[name="tax_id"]').attr('pattern', $('select[name="country_code"]').find('option:selected').data('tax-id-format'));
  } else {
    $('select[name="country_code"]').closest('table').find('input[name="tax_id"]').removeAttr('pattern');
  }

  if ($('select[name="country_code"]').find('option:selected').data('postcode-format') != '') {
    $('select[name="country_code"]').closest('table').find('input[name="postcode"]').attr('pattern', $('select[name="country_code"]').find('option:selected').data('postcode-format'));
    $('select[name="country_code"]').closest('table').find('input[name="postcode"]').prop('required', true);
    $('select[name="country_code"]').closest('table').find('input[name="postcode"]').closest('td').find('.required').show();
  } else {
    $('select[name="country_code"]').closest('table').find('input[name="postcode"]').removeAttr('pattern');
    $('select[name="country_code"]').closest('table').find('input[name="postcode"]').prop('required', false);
    $('select[name="country_code"]').closest('table').find('input[name="postcode"]').closest('td').find('.required').hide();
  }

  if ($('select[name="country_code"]').find('option:selected').data('phone-code') != '') {
    $('select[name="country_code"]').closest('table').find('input[name="phone"]').attr('placeholder', '+' + $('select[name="country_code"]').find('option:selected').data('phone-code'));
  } else {
    $('select[name="country_code"]').closest('table').find('input[name="phone"]').removeAttr('placeholder');
  }

  if (!$('select[name="zone_code"] option').length) $('select[name="zone_code"]').closest('td').css('opacity', 0.15);

// Init (Shipping address)

  $('input[name="different_shipping_address"]').change(function(e){
    if (this.checked == true) {
      $('fieldset.shipping-address').prop('disabled', false).slideDown('fast');
    } else {
      $('fieldset.shipping-address').prop('disabled', true).slideUp('fast');
    }
  }).trigger('change');

  if ($('select[name="shipping_address[country_code]"]').find('option:selected').data('tax-id-format') != '') {
    $('select[name="shipping_address[country_code]"]').closest('table').find('input[name="tax_id"]').attr('pattern', $('select[name="shipping_address[country_code]"]').find('option:selected').data('tax-id-format'));
  } else {
    $('select[name="shipping_address[country_code]"]').closest('table').find('input[name="tax_id"]').removeAttr('pattern');
  }

  if ($('select[name="shipping_address[country_code]"]').find('option:selected').data('postcode-format') != '') {
    $('select[name="shipping_address[country_code]"]').closest('table').find('input[name="shipping_address[postcode]"]').attr('pattern', $('select[name="shipping_address[country_code]"]').find('option:selected').data('postcode-format'));
    $('select[name="shipping_address[country_code]"]').closest('table').find('input[name="shipping_address[postcode]"]').prop('required', true);
    $('select[name="shipping_address[country_code]"]').closest('table').find('input[name="shipping_address[postcode]"]').closest('td').find('.required').show();
  } else {
    $('select[name="shipping_address[country_code]"]').closest('table').find('input[name="shipping_address[postcode]"]').removeAttr('pattern');
    $('select[name="shipping_address[country_code]"]').closest('table').find('input[name="shipping_address[postcode]"]').prop('required', false);
    $('select[name="shipping_address[country_code]"]').closest('table').find('input[name="shipping_address[postcode]"]').closest('td').find('.required').hide();
  }

  if ($('select[name="shipping_address[country_code]"]').find('option:selected').data('phone-code') != '') {
    $('select[name="shipping_address[country_code]"]').closest('table').find('input[name="shipping_address[phone]"]').attr('placeholder', '+' + $('select[name="shipping_address[country_code]"]').find('option:selected').data('phone-code'));
  } else {
    $('select[name="shipping_address[country_code]"]').closest('table').find('input[name="shipping_address[phone]"]').removeAttr('placeholder');
  }

  if (!$('select[name="shipping_address[zone_code]"] option').length) $('select[name="shipping_address[zone_code]"]').closest('td').css('opacity', 0.15);

// Get Address

  $('form[name="customer_form"]').on('change', ':input', function() {
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

// Get Address (Shipping address)

  $('form[name="customer_form"]').on('change', ':input', function() {
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
          if ($('input[name="shipping_address['+key+']"]').length && $('input[name="shipping_address['+key+']"]').val() == '') $('input[name="shipping_address['+key+']"]').val(data[key]);
        });
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });

// On change country

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

// On change country (Shipping address)

  $('select[name="shipping_address[country_code]"]').change(function(e) {

    if ($(this).find('option:selected').data('postcode-format')) {
      $('input[name="shipping_address[postcode]"]').attr('pattern', $(this).find('option:selected').data('postcode-format'));
    } else {
      $('input[name="shipping_address[postcode]"]').removeAttr('pattern');
    }

    if ($(this).find('option:selected').data('phone-code')) {
      $('input[name="shipping_address[phone]"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'));
    } else {
      $('input[name="shipping_address[phone]"]').removeAttr('placeholder');
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
        $('select[name="shipping_address[zone_code]"]').html('');
        if (data.length) {
          $('select[name="shipping_address[zone_code]"]').prop('disabled', false);
          $.each(data, function(i, zone) {
            $('select[name="shipping_address[zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="shipping_address[zone_code]"]').prop('disabled', true);
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });

  $('input[name="different_shipping_address"]').change(function(e){
    if (this.checked == true) {
      $('#shipping-address').slideDown('fast');
    } else {
      $('#shipping-address').slideUp('fast');
    }
  });
</script>