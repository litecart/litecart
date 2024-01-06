<?php

  if (!empty($_GET['cart_id'])) {
    $shopping_cart = new ent_shopping_cart($_GET['cart_id']);
  } else {
    $shopping_cart = new ent_shopping_cart();
    $shopping_cart->data['date_created'] = date('Y-m-d H:i:s');
  }

  if (!$_POST) {
    $_POST = $shopping_cart->data;

  // Convert to local currency
    foreach (array_keys($_POST['items']) as $key) {
      $_POST['items'][$key]['price'] = $_POST['items'][$key]['price'] / $_POST['currency_value'];
      $_POST['items'][$key]['tax'] = $_POST['items'][$key]['tax'] / $_POST['currency_value'];
    }

    if (empty($shopping_cart->data['id'])) {
      $_POST['customer']['country_code'] = settings::get('default_country_code');
    }
  }

  document::$title[] = !empty($shopping_cart->data['id']) ? language::translate('title_edit_shopping_cart', 'Edit Shopping Cart') .' #'. $shopping_cart->data['id'] : language::translate('title_create_new_shopping_cart', 'Create New Shopping Cart');

  breadcrumbs::add(language::translate('title_shopping_carts', 'Shopping Carts'), document::ilink(__APP__.'/shopping_carts'));
  breadcrumbs::add(!empty($shopping_cart->data['id']) ? language::translate('title_edit_shopping_cart', 'Edit Shopping Cart') .' #'. $shopping_cart->data['id'] : language::translate('title_create_new_shopping_cart', 'Create New Shopping Cart'));

// Save data to database
  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['items'])) $_POST['items'] = [];

      if (!empty($_POST['items'])) {
        foreach (array_keys($_POST['items']) as $key) {
          $_POST['items'][$key]['price'] = $_POST['items'][$key]['price'] * $_POST['currency_value'];
          $_POST['items'][$key]['tax'] = $_POST['items'][$key]['tax'] * $_POST['currency_value'];
        }
      }

      $fields = [
        'language_code',
        'currency_code',
        'currency_value',
        'items',
        'display_prices_including_tax',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) {
          $shopping_cart->data[$field] = $_POST[$field];
        }
      }

      $fields = [
        'id',
        'email',
        'tax_id',
        'company',
        'firstname',
        'lastname',
        'address1',
        'address2',
        'postcode',
        'city',
        'phone',
        'country_code',
        'zone_code',
      ];

      foreach ($fields as $field) {
        if (isset($_POST['customer'][$field])) {
          $shopping_cart->data['customer'][$field] = $_POST['customer'][$field];
        }
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
        if (isset($_POST['customer']['shipping_address'][$field])) {
          $shopping_cart->data['customer']['shipping_address'][$field] = $_POST['customer']['shipping_address'][$field];
        }
      }

      $shopping_cart->save();

      if (!empty($_POST['email_shopping_cart_copy'])) {

        $bccs = [];
        foreach (preg_split('#[\s;,]+#', settings::get('email_shopping_cart_copy'), -1, PREG_SPLIT_NO_EMPTY) as $email) {
          $bccs[] = $email;
        }

        $shopping_cart->email_shopping_cart_copy($shopping_cart->data['customer']['email'], $bccs, $shopping_cart->data['language_code']);
      }

      if (!empty($_GET['redirect_url'])) {
        $redirect_url = $_GET['redirect_url'];
      } else {
        $redirect_url = document::ilink(__APP__.'/shopping_carts');
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $redirect_url);
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {

      if (empty($shopping_cart->data['id'])) {
        throw new Exception(language::translate('error_must_provide_shopping_cart', 'You must provide a shopping cart'));
      }

      $shopping_cart->delete();

      if (empty($_GET['redirect_url'])) {
        $_GET['redirect_url'] = document::ilink(__APP__.'/shopping_carts');
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $_GET['redirect_url']);
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  functions::draw_lightbox();

  $account_name = '('. language::translate('title_guest', 'Guest') .')';
  if (!empty($_POST['customer']['id'])) {
    $customer = reference::customer((int)$_POST['customer']['id']);
    $account_name = $customer->company ? $customer->company : $customer->firstname .' '. $customer->lastname;
  }
?>
<style>
#shopping-cart-items tr.highlight {
  bshopping_cart: 1px #f00 solid;
}
#shopping-cart-items tr.extended {
  display: none;
}
#shopping-cart-items tr.highlight + tr.extended {
  display: table-row;
}

#box-shopping-cart-items .subtotal {
  font-size: 1.25em;
}
#box-shopping-cart-items .subtotal .value {
  font-weight: bold;
}

#modal-customer-picker tbody tr {
  cursor: pointer;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($shopping_cart->data['id']) ? language::translate('title_edit_shopping_cart', 'Edit Shopping Cart') .' #'. $shopping_cart->data['id'] : language::translate('title_create_new_shopping_cart', 'Create New Shopping Cart'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('form_shopping_cart', 'post'); ?>

      <section id="general" style="max-width: 980px;">
        <h2><?php echo language::translate('title_general', 'General'); ?></h2>

        <div class="row">
          <div class="form-group col-md-3">
            <label><?php echo language::translate('title_language', 'Language'); ?></label>
            <?php echo functions::form_select_language('language_code', true); ?>
          </div>

          <div class="form-group col-md-3">
            <label><?php echo language::translate('title_currency', 'Currency'); ?></label>
            <?php echo functions::form_select_currency('currency_code', true); ?>
          </div>

          <div class="form-group col-md-3">
            <label><?php echo language::translate('title_lock_prices', 'Lock Prices'); ?></label>
            <?php echo functions::form_toggle('lock_prices', 'y/n', true); ?>
          </div>

          <?php if ($shopping_cart->data['client_ip']) { ?>
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_ip_address', 'IP Address'); ?></label>
            <div class="form-input">
              <?php echo $shopping_cart->data['client_ip']; ?> <a href="https://ip-api.com/#<?php echo $shopping_cart->data['client_ip']; ?>" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a>
            </div>
          </div>
          <?php } ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_link', 'Link'); ?></label>
          <?php if (!empty($shopping_cart->data['id'])) { ?>
          <?php echo functions::form_input_url('link', document::ilink('f:checkout/index', ['cart_uid' => $shopping_cart->data['uid'], 'public_key' => $shopping_cart->data['public_key']], 'readonly')); ?>
          <?php } else { ?>
          <div class="form-input">
            <em>(<?php echo language::translate('text_save_to_generate_link', 'Save to generate link'); ?>)</em>
          </div>
          <?php } ?>
        </div>
      </section>

      <section id="customer-details" style="max-width: 980px;">

        <div class="row" style="margin-bottom: 0;">
          <div class="col-md-6 customer-details">
            <h2><?php echo language::translate('title_billing_address', 'Billing Address'); ?></h2>

            <div class="form-group">
              <div class="input-group">
                <div class="selected-account form-input"><?php echo language::translate('title_id', 'ID'); ?>: <span class="id"><?php echo isset($_POST['customer']['id']) ? (int)$_POST['customer']['id'] : ''; ?></span> &ndash; <span class="name"><?php echo $account_name; ?></span> <a href="<?php echo document::href_ilink('customers/customer_picker'); ?>" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin-inline-start: 5px;"><?php echo language::translate('title_change', 'Change'); ?></a></div>
                <?php echo functions::form_input_hidden('customer[id]', true); ?>
                <?php echo functions::form_button('get_address', language::translate('title_get_address', 'Get Address'), 'button'); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_company_name', 'Company Name'); ?></label>
                <?php echo functions::form_input_text('customer[company]', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_tax_id', 'Tax ID / VATIN'); ?></label>
                <?php echo functions::form_input_text('customer[tax_id]', true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
                <?php echo functions::form_input_text('customer[firstname]', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
                <?php echo functions::form_input_text('customer[lastname]', true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
                <?php echo functions::form_input_text('customer[address1]', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
                <?php echo functions::form_input_text('customer[address2]', true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
                <?php echo functions::form_input_text('customer[postcode]', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_city', 'City'); ?></label>
                <?php echo functions::form_input_text('customer[city]', true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_country', 'Country'); ?></label>
                <?php echo functions::form_select_country('customer[country_code]', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
                <?php echo form_select_zone('customer[zone_code]', fallback($_POST['customer']['country_code']), true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
                <?php echo functions::form_input_email('customer[email]', true, 'required'); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_phone_number', 'Phone Number'); ?></label>
                <?php echo functions::form_input_phone('customer[phone]', true); ?>
              </div>
            </div>
          </div>

          <div class="form-group col-md-6 shipping-address">
            <h2><?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></h2>

            <div class="form-group">
              <?php echo functions::form_button('copy_billing_address', language::translate('title_copy_billing_address', 'Copy Billing Address'), 'button', 'class="btn btn-default btn-block"'); ?>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_company_name', 'Company Name'); ?></label>
                <?php echo functions::form_input_text('customer[shipping_address][company]', true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
                <?php echo functions::form_input_text('customer[shipping_address][firstname]', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
                <?php echo functions::form_input_text('customer[shipping_address][lastname]', true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
                <?php echo functions::form_input_text('customer[shipping_address][address1]', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
                <?php echo functions::form_input_text('customer[shipping_address][address2]', true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
                <?php echo functions::form_input_text('customer[shipping_address][postcode]', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_city', 'City'); ?></label>
                <?php echo functions::form_input_text('customer[shipping_address][city]', true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_country', 'Country'); ?></label>
                <?php echo functions::form_select_country('customer[shipping_address][country_code]', true); ?>
              </div>

              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
                <?php echo form_select_zone('customer[shipping_address][zone_code]', fallback($_POST['customer']['shipping_address']['country_code']), true); ?>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label><?php echo language::translate('title_phone_number', 'Phone Number'); ?></label>
                <?php echo functions::form_input_phone('customer[shipping_address][phone]', true); ?>
              </div>
            </div>
          </div>

        </div>
      </section>

      <section id="cart-items">
        <h2><?php echo language::translate('title_items', 'Items'); ?></h2>

        <div class="table-responsive">
          <table class="table table-striped table-hover table-input table-dragable">
            <thead>
              <tr>
                <th><?php echo language::translate('title_item', 'Item'); ?></th>
                <th style="width: 125px;" class="text-center"><?php echo language::translate('title_qty', 'Qty'); ?></th>
                <th style="width: 200px;" class="text-center"><?php echo language::translate('title_unit_price', 'Unit Price'); ?></th>
                <th style="width: 200px;" class="text-center"><?php echo language::translate('title_discount', 'Discount'); ?></th>
                <th style="width: 200px;" class="text-center"><?php echo language::translate('title_total', 'Total'); ?></th>
                <th style="width: 50px;"></th>
                <th style="width: 50px;"></th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($_POST['items'])) foreach (array_keys($_POST['items']) as $key) { ?>
              <tr class="item">
                <td class="grabable">
                  <?php echo !empty($_POST['items'][$key]['product_id']) ? '<a href="'. document::href_ilink('f:product', ['product_id' => $_POST['items'][$key]['product_id']]) .'" target="_blank">'. $_POST['items'][$key]['name'] .'</a>' : $_POST['items'][$key]['name']; ?>
                  <?php echo functions::form_input_hidden('items['.$key.'][id]', true); ?>
                  <?php echo functions::form_input_hidden('items['.$key.'][product_id]', true); ?>
                </td>
                <td><?php echo functions::form_input_decimal('items['. $key .'][quantity]', true, 2); ?></td>
                <td><?php echo functions::form_input_money('items['. $key .'][price]', $_POST['currency_code'], true); ?></td>
                <td><?php echo functions::form_input_money('items['. $key .'][discount]', $_POST['currency_code'], true); ?></td>
                <td><?php echo functions::form_input_money('items['. $key .'][total]', $_POST['currency_code'], true, 'readonly'); ?></td>
                <td><a class="remove btn btn-default btn-sm" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg fa-fw', 'style="color: #c33;"'); ?></a></td>
                <td><a class="edit btn btn-default btn-sm" href="#" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
              </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="8">
                  <a class="btn btn-default add-product" href="<?php echo document::href_ilink('catalog/product_picker'); ?>" data-toggle="lightbox" data-seamless="true" data-width="" data-href="<?php echo document::href_ilink(__APP__.'/product_picker'); ?>"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #6c6;"'); ?> <?php echo language::translate('title_add_product', 'Add Product'); ?></a>
                  <div class="btn btn-default add-custom-item"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #6c6;"'); ?> <?php echo language::translate('title_add_custom_item', 'Add Custom Item'); ?></div>
                </td>
              </tr>
            </tfoot>
          </table>

          <div class="subtotal text-end">
            <?php echo language::translate('title_subtotal', 'Subtotal'); ?>: <span class="value">0</span>
          </div>
        </div>
      </section>

      <div class="card-action">
        <?php echo functions::form_button_predefined('save'); ?>
        <?php if (!empty($shopping_cart->data['id'])) echo functions::form_button_predefined('delete'); ?>
        <?php echo functions::form_button_predefined('cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>

<div id="modal-customer-picker" class="modal fade" style="max-width: 640px; display: none;">

  <h2><?php echo language::translate('title_customer', 'Customer'); ?></h2>

  <div class="modal-body">
    <div class="form-group">
      <?php echo functions::form_input_text('query', true, 'placeholder="'. functions::escape_html(language::translate('title_search', 'Search')) .'"'); ?>
    </div>

    <div class="form-group results table-responsive">
      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo language::translate('title_id', 'ID'); ?></th>
            <th><?php echo language::translate('title_name', 'Name'); ?></th>
            <th class="main"><?php echo language::translate('title_email', 'Email'); ?></th>
            <th><?php echo language::translate('title_date_registered', 'Date Registered'); ?></th>
          </tr>
        </thead>
        <tbody />
      </table>

      <p class="text-center"><button class="set-guest btn btn-default" type="button"><?php echo language::translate('text_set_as_guest', 'Set As Guest'); ?></button></p>
    </div>
  </div>
</div>

<div id="modal-edit-cart-item" class="modal fade" style="max-width: 640px; display: none;">

  <h2><?php echo language::translate('title_edit_shopping_cart_item', 'Edit shopping_cart Item'); ?></h2>

  <div class="modal-body">

    <div class="row">
      <div class="form-group col-md-9">
        <label><?php echo language::translate('title_name', 'Name'); ?></label>
        <?php echo functions::form_input_text('name', ''); ?>
      </div>

      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_product_id', 'Product ID'); ?></label>
        <?php echo functions::form_input_number('product_id', ''); ?>
      </div>
    </div>

    <div class="row">
        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
        <?php echo functions::form_input_decimal('quantity', ''); ?>
      </div>

        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_price', 'Price'); ?></label>
        <?php echo functions::form_input_money('price', $_POST['currency_code'], ''); ?>
      </div>

        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_tax', 'Tax'); ?></label>
        <?php echo functions::form_input_money('tax', $_POST['currency_code'], ''); ?>
      </div>
    </div>

    <div class="btn-group">
      <?php echo functions::form_button('ok', language::translate('title_ok', 'OK'), 'button', '', 'ok'); ?>
      <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.featherlight.close();"', 'cancel'); ?>
    </div>
  </div>
</div>

<div id="modal-add-cart-item" class="modal fade" style="max-width: 640px; display: none;">

  <h2><?php echo language::translate('title_add_shopping_cart_item', 'Add Shopping Cart Item'); ?></h2>

  <div class="modal-body">

    <div class="row">
      <div class="form-group col-md-9">
        <label><?php echo language::translate('title_name', 'Name'); ?></label>
        <?php echo functions::form_input_text('name', ''); ?>
      </div>

      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_product_id', 'Product ID'); ?></label>
        <?php echo functions::form_input_number('product_id', ''); ?>
      </div>
    </div>

    <div class="row">
        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
        <?php echo functions::form_input_decimal('quantity', ''); ?>
      </div>

        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_price', 'Price'); ?></label>
        <?php echo functions::form_input_money('price', $_POST['currency_code'], ''); ?>
      </div>

      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_tax', 'Tax'); ?></label>
        <?php echo functions::form_input_money('tax', $_POST['currency_code'], ''); ?>
      </div>
    </div>

    <div>
      <?php echo functions::form_button('ok', language::translate('title_ok', 'OK'), 'button', '', 'ok'); ?>
      <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.featherlight.close();"', 'cancel'); ?>
    </div>
  </div>
</div>

<script>

  $('select[name="currency_code"]').change(function(e){
    $('input[name="currency_value"]').val($(this).find('option:selected').data('value'));
    $('input[data-type="currency"]').closest('.input-group').find('.input-group-text').text($(this).val());
    calculate_total();
  });

// Customer

  $('#customer-details button[name="get_address"]').click(function() {
    $.ajax({
      url: '<?php echo document::ilink('customers/get_address.json'); ?>',
      type: 'post',
      data: 'customer_id=' + $('*[name="customer[id]"]').val(),
      cache: true,
      async: false,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        if (console) console.warn(errorThrown.message);
      },
      success: function(data) {
        $.each(data, function(key, value) {
          if (key.match(/^shipping_address/)) {
            $.each(value, function(key, value) {
              if ($('*[name="customer[shipping_address]['+key+']"]').length) $('*[name="customer[shipping_address]['+key+']"]').val(value).trigger('change');
            });
          } else {
            if ($('*[name="customer['+key+']"]').length) $('*[name="customer['+key+']"]').val(value).trigger('change');
          }
        });
      },
    });
  });

  $('#customer-details select[name="customer[country_code]"]').change(function() {

    if ($(this).find('option:selected').data('tax-id-format')) {
      $('input[name="customer[tax_id]"]').attr('pattern', $(this).find('option:selected').data('tax-id-format'));
    } else {
      $('input[name="customer[tax_id]"]').removeAttr('pattern');
    }

    if ($(this).find('option:selected').data('postcode-format')) {
      $('input[name="customer[postcode]"]').attr('pattern', $(this).find('option:selected').data('postcode-format'));
    } else {
      $('input[name="customer[postcode]"]').removeAttr('pattern');
    }

    if ($(this).find('option:selected').data('phone-code')) {
      $('input[name="customer[phone]"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'));
    } else {
      $('input[name="customer[phone]"]').removeAttr('placeholder');
    }

    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo document::ilink('countries/zones.json'); ?>?country_code=' + $(this).val(),
      type: 'get',
      cache: true,
      async: false,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        //alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
      },
      success: function(data) {
        $('select[name="customer[zone_code]"]').html('');
        if ($('select[name="customer[zone_code]"]').is(':disabled')) $('select[name="customer[zone_code]"]').prop('disabled', false);
        if (data) {
          $.each(data, function(i, zone) {
            $('select[name="customer[zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="customer[zone_code]"]').prop('disabled', true);
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });

  $('#customer-details button[name="copy_billing_address"]').click(function(){
    fields = ['company', 'firstname', 'lastname', 'address1', 'address2', 'postcode', 'city', 'country_code', 'zone_code', 'phone'];
    $.each(fields, function(key, field){
      $('*[name="customer[shipping_address]['+ field +']"]').val($('*[name="customer['+ field +']"]').val()).trigger('change');
    });
  });

  $('#customer-details select[name="customer[shipping_address][country_code]"]').change(function(){

    if ($(this).find('option:selected').data('postcode-format')) {
      $('input[name="customer[shipping_address][postcode]"]').attr('pattern', $(this).find('option:selected').data('postcode-format'));
    } else {
      $('input[name="customer[shipping_address][postcode]"]').removeAttr('pattern');
    }

    if ($(this).find('option:selected').data('phone-code')) {
      $('input[name="customer[shipping_address][phone]"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'));
    } else {
      $('input[name="customer[shipping_address][phone]"]').removeAttr('placeholder');
    }

    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo document::ilink('countries/zones.json'); ?>?country_code=' + $(this).val(),
      type: 'get',
      cache: true,
      async: true,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        //alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
      },
      success: function(data) {
        $('select[name="customer[shipping_address][zone_code]"]').html('');
        if ($('select[name="customer[shipping_address][zone_code]"]').is(':disabled')) $('select[name="customer[shipping_address][zone_code]"]').prop('disabled', false);
        if (data) {
          $.each(data, function(i, zone) {
            $('select[name="customer[shipping_address][zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="customer[shipping_address][zone_code]]"]').prop('disabled', true);
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });

  if ($('select[name="customer[country_code]"] option:selected').data('tax-id-format')) {
    $('input[name="customer[tax_id]"]').attr('pattern', $('select[name="country_code"] option:selected').data('tax-id-format'));
  } else {
    $('input[name="customer[tax_id]"]').removeAttr('pattern');
  }

  if ($('select[name="customer[country_code]"] option:selected').data('postcode-format')) {
    $('input[name="customer[postcode]"]').attr('pattern', $('select[name="customer[country_code]"] option:selected').data('postcode-format'));
  } else {
    $('input[name="customer[postcode]"]').removeAttr('pattern');
  }

  if ($('select[name="customer[country_code]"] option:selected').data('phone-code')) {
    $('input[name="customer[phone]"]').attr('placeholder', '+' + $('select[name="customer[country_code]"] option:selected').data('phone-code'));
  } else {
    $('input[name="customer[phone]"]').removeAttr('placeholder');
  }

  if ($('select[name="customer[shipping_address][country_code]"] option:selected').data('postcode-format')) {
    $('input[name="customer[shipping_address][postcode]"]').attr('pattern', $('select[name="customer[shipping_address][country_code]"] option:selected').data('postcode-format'));
  } else {
    $('input[name="customer[shipping_address][postcode]"]').removeAttr('pattern');
  }

  if ($('select[name="customer[shipping_address][country_code]"] option:selected').data('phone-code')) {
    $('input[name="customer[shipping_address][phone]"]').attr('placeholder', '+' + $('select[name="customer[shipping_address][country_code]"] option:selected').data('phone-code'));
  } else {
    $('input[name="customer[shipping_address][phone]"]').removeAttr('placeholder');
  }

  $('select[name="language_code"], select[name="currency_code"], input[name="currency_value"], :input[name^="customer"]').on('input', function(){
    let params = {
      language_code: $('select[name="language_code"]').val(),
      currency_code: $('select[name="currency_code"]').val(),
      currency_value: $('input[name="currency_value"]').val(),
      customer: {
        id: $(':input[name="customer[id]"]').val(),
        tax_id: $('input[name="customer[tax_id]"]').val(),
        company: $('input[name="customer[company]"]').val(),
        country_code: $('select[name="customer[country_code]"]').val(),
        zone_code: $('select[name="customer[zone_code]"]').val(),
        shipping_address: {
          company: $('input[name="customer[shipping_address][company]"]').val(),
          country_code: $('select[name="customer[shipping_address][country_code]"]').val(),
          zone_code: $('select[name="customer[shipping_address][zone_code]"]').val(),
        }
      }
    }

    $('.add-product').attr('href', $('.add-product').data('href') +'?'+ $.param(params));
  });

  $(':input[name^="customer"]').first().trigger('input');

// Shopping cart items

  $('#shopping-cart-items').on('click', '.edit', function(){
    $.featherlight('#modal-edit-cart-item');

    let modal = $('.featherlight.active'),
        row = $(this).closest('tr');

    $(modal).data('row', row);

    $.each($(modal).find(':input'), function(i,element){
      let field = $(element).attr('name');
      let value = $(row).find(':input[name$="['+field+']"]').val();
      if ($(modal).find(':input[name="'+field+'"]').attr('type') == 'number') value = parseFloat(value);
      $(modal).find(':input[name="'+field+'"]').val(value);
    });
  });

  $('#shopping-cart-items .add-custom-item').click(function(){
    $.featherlight('#modal-add-cart-item');

    let modal = $('.featherlight.active'),
        row = $(this).closest('tr');

    $(modal).data('row', '');
  });

  $('#modal-edit-cart-item button[name="ok"]').click(function(e){

    let modal = $('.featherlight.active');
    let row = $(modal).data('row');
    let fields = [
      'name',
      'price',
      'tax',
    ];

    if (row == '') {
      let item = {};
      $.each($(modal).find(':input'), function(i,element){
        let field = $(element).attr('name');
        item[field] = $(modal).find(':input[name="'+field+'"]').val();
      });
      addItem(item);
    }

    $.each($(modal).find(':input'), function(i,element){
      let field = $(element).attr('name');
      let value = $(modal).find(':input[name="'+field+'"]').val();
      $(row).find(':input[name$="['+field+']"]').val(value).trigger('keyup');
      $(row).find('.'+field).text(value);
    });

    $.featherlight.close();
  });

  $('#modal-add-cart-item button[name="ok"]').click(function(e){

    let modal = $('.featherlight.active');
    let row = $(modal).data('row');
    let item = {};
    let fields = [
      'name',
      'price',
      'tax',
    ];

    $.each($(modal).find(':input'), function(i,element){
      let field = $(element).attr('name');
      item[field] = $(modal).find(':input[name="'+field+'"]').val();
    });

    addItem(item);

    $.featherlight.close();
  });

  let new_item_index = 0;
  while ($(':input[name^="items['+new_item_index+']"]').length) new_item_index++;

  window.addItem = function(item) {

    let output = [
      '  <tr class="item">',
      '    <td class="grabable">' + item.name,
      '      <?php echo functions::escape_js(functions::form_input_hidden('items[new_item_index][id]', '')); ?>',
      '      <?php echo functions::escape_js(functions::form_input_hidden('items[new_item_index][product_id]', '')); ?>',
      '      <?php echo functions::escape_js(functions::form_input_hidden('items[new_item_index][name]', '')); ?>',
      '      <?php echo functions::escape_js(functions::form_input_hidden('items[new_item_index][description]', '')); ?>',
      '    </td>',
      '    <td><?php echo functions::escape_js(functions::form_input_decimal('items[new_item_index][quantity]', '', 2)); ?></td>',
      '    <td><?php echo functions::escape_js(functions::form_input_money('items[new_item_index][price]', $_POST['currency_code'], '')); ?></td>',
      '    <td><?php echo functions::escape_js(functions::form_input_money('items[new_item_index][tax]', $_POST['currency_code'], '')); ?></td>',
      '    <td class="text-end">',
      '      <a class="edit" href="#" title="<?php echo functions::escape_js(language::translate('title_edit', 'Edit'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('edit')); ?></a>',
      '      <a class="remove" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-times-circle fa-lg fa-fw', 'style="color: #c33;"')); ?></a>',
      '    </td>',
      '  </tr>'
    ].join('')
    .replace(/new_item_index/g, 'new_' + new_item_index++);

    $output = $(output);
    $(output).find('*[name$="[product_id]"]').val(item.product_id);
    $(output).find('*[name$="[stock_option_id]"]').val(item.stock_option_id);
    $(output).find('*[name$="[name]"]').val(item.name);
    $(output).find('*[name$="[quantity]"]').val(item.quantity);
    $(output).find('*[name$="[price]"]').val(item.price);
    $(output).find('*[name$="[tax]"]').val(item.tax);
    $(output).find('[data-type="currency"]').parent().find('.input-group-text').text($(':input[name="currency_code"]').val());

    $('#shopping-cart-items tbody').append($output);

    calculate_total();
  }

  $('#shopping-cart-items').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

  Number.prototype.toMoney = function() {
    let n = this,
      c = $('input[name="currency_code"]').data('decimals'),
      d = $('input[name="language_code"]').data('decimal_point'),
      t = $('input[name="language_code"]').data('thousands_sep'),
      p = $('input[name="currency_code"]').data('prefix'),
      x = $('input[name="currency_code"]').data('suffix'),
      s = n < 0 ? '-' : '',
      i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '',
      f = n - i,
      j = (j = i.length) > 3 ? j % 3 : 0;

    return s + p + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (<?php echo (settings::get('auto_decimals')) ? "(c && f)" : "c"; ?> ? d + Math.abs(f).toFixed(c).slice(2) : '') + x;
  }

  function calculate_total() {

    let subtotal = 0;
    $('input[name^="items["][name$="[price]"]').each(function() {
      subtotal += parseFloat($(this).val()) * parseFloat($(this).closest('tr').find('input[name^="items["][name$="[quantity]"]').val());
    });
    subtotal = parseFloat(subtotal.toFixed($('select[name="currency_code"] option:selected').data('decimals')));
    $('#box-shopping-cart-items .subtotal .value').val(subtotal.toMoney());

    let subtotal_tax = 0;
    $('input[name^="items["][name$="[tax]"]').each(function() {
      subtotal_tax += parseFloat($(this).val()) * parseFloat($(this).closest('tr').find('input[name^="items["][name$="[quantity]"]').val());
    });
    subtotal_tax = parseFloat(subtotal_tax.toFixed($('select[name="currency_code"] option:selected').data('decimals')));
    $('#box-shopping-cart-items .subtotal .tax').val(subtotal_tax.toMoney());
  }

  $('body').on('click keyup', 'input[name^="items"][name$="[price]"], input[name^="items"][name$="[tax]"], input[name^="items"][name$="[quantity]"], input[name^="shopping_cart_total"][name$="[value]"], input[name^="shopping_cart_total"][name$="[tax]"], input[name^="shopping_cart_total"][name$="[calculate]"], #shopping-cart-items a.remove, #shopping-cart-total a.remove', function() {
    calculate_total();
  });
</script>