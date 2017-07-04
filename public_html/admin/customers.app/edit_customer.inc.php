<?php

  if (!empty($_GET['customer_id'])) {
    $customer = new ctrl_customer($_GET['customer_id']);
  } else {
    $customer = new ctrl_customer();
  }

  if (empty($_POST)) {
      foreach ($customer->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }

  breadcrumbs::add(!empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_add_new_customer', 'Add New Customer'));

  if (isset($_POST['save'])) {

    if (empty(notices::$data['errors'])) {

      if (empty($_POST['newsletter'])) $_POST['newsletter'] = 0;

      $fields = array(
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
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $customer->data[$field] = $_POST[$field];
      }

      $customer->save();

      if (!empty($_POST['new_password'])) $customer->set_password($_POST['new_password']);

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('app' => $_GET['app'], 'doc' => 'customers')));
      exit;
    }
  }

  if (isset($_POST['delete'])) {

    $customer->delete();

    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('app' => $_GET['app'], 'doc' => 'customers')));
    exit;
  }

  if (!empty($customer->data['id'])) {
    $order_statuses = array();
    $orders_status_query = database::query(
      "select id from ". DB_TABLE_ORDER_STATUSES ." where is_sale;"
    );
    while ($order_status = database::fetch($orders_status_query)) {
      $order_statuses[] = (int)$order_status['id'];
    }

    $orders_query = database::query(
      "select count(o.id) as total_count, sum(oi.total_sales) as total_sales
      from ". DB_TABLE_ORDERS ." o
      left join (
        select order_id, sum(price * quantity) as total_sales from ". DB_TABLE_ORDERS_ITEMS ."
        group by order_id
      ) oi on (oi.order_id = o.id)
      where o.order_status_id in ('". implode("', '", $order_statuses) ."')
      and (o.customer_id = '". (int)$customer->data['id'] ."' or o.customer_email = '". database::input($customer->data['email']) ."');"
    );
    $orders = database::fetch($orders_query);
  }

?>
<h1><?php echo $app_icon; ?> <?php echo !empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_add_new_customer', 'Add New Customer'); ?></h1>

<?php echo functions::form_draw_form_begin('customer_form', 'post', false, false); ?>

  <div class="row" style="max-width: 960px;">

    <div class="col-md-8">

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_status', 'Status'); ?></label>
            <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '1', 'e/d'); ?>
          </div>

          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_code', 'Code'); ?></label>
            <?php echo functions::form_draw_text_field('code', true); ?>
          </div>
        </div>

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
            <label><?php echo language::translate('title_tax_id', 'Tax ID'); ?></label>
            <?php echo functions::form_draw_text_field('tax_id', true); ?>
          </div>

          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_company', 'Company'); ?></label>
            <?php echo functions::form_draw_text_field('company', true); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_firstname', 'Firstname'); ?></label>
            <?php echo functions::form_draw_text_field('firstname', true); ?>
          </div>

          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_lastname', 'Lastname'); ?></label>
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
            <label><?php echo language::translate('title_city', 'City'); ?></label>
            <?php echo functions::form_draw_text_field('city', true); ?>
          </div>

          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_postcode', 'Postcode'); ?></label>
            <?php echo functions::form_draw_text_field('postcode', true); ?>
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
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo !empty($customer->data['id']) ? language::translate('title_new_password', 'New Password') : language::translate('title_password', 'Password'); ?></label>
              <?php echo functions::form_draw_text_field('new_password', '', 'autocomplete="off"'); ?>
          </div>
        </div>

        <p class="btn-group">
          <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
          <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
          <?php echo (isset($customer->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
        </p>


    </div>

    <div class="col-md-4">
      <div class="form-group">
        <label><?php echo language::translate('title_notes', 'Notes'); ?></label>
        <?php echo functions::form_draw_textarea('notes', true, 'style="height: 480px;"'); ?>
      </div>

      <?php if (!empty($customer->data['id'])) { ?>
      <table class="table table-striped data-table">
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

<script>
  $('form[name="customer_form"] :input').change(function() {
    if ($(this).val() == '') return;
    if (console) console.log('Retrieving address ["'+ $(this).attr('name') +']');
    $.ajax({
      url: '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
      type: 'post',
      data: $(this).closest('form').serialize(),
      cache: false,
      async: false,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        if (console) console.warn(errorThrown.message);
      },
      success: function(data) {
        if (data['alert']) {
          alert(data['alert']);
        }
        $.each(data, function(key, value) {
          if (console) console.log('  ' + key +": "+ value);
          if ($('form[name="customer_form"] *[name="'+key+'"]').length && $('form[name="customer_form"] *[name="'+key+'"]').val() == '') {
            $('form[name="customer_form"] *[name="'+key+'"]').val(value);
          }
        });
      },
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
        if (data) {
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
    $('select[name="country_code"]').closest('table').find('input[name="tax_id"]').attr('pattern', $('select[name="country_code"]').find('option:selected').data('tax-id-format'));
  } else {
    $('select[name="country_code"]').closest('table').find('input[name="tax_id"]').removeAttr('pattern');
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

  if ($('select[name="country_code"]').find('option:selected').data('phone-code') != '') {
    $('select[name="country_code"]').closest('table').find('input[name="phone"]').attr('placeholder', '+' + $('select[name="country_code"]').find('option:selected').data('phone-code'));
  } else {
    $('select[name="country_code"]').closest('table').find('input[name="phone"]').removeAttr('placeholder');
  }

  if ($('select[name="zone_code"] option').length == 0) $('select[name="zone_code"]').closest('td').css('opacity', 0.15);
</script>