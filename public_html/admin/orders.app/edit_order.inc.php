<?php

  if (!empty($_GET['order_id'])) {
    $order = new ent_order($_GET['order_id']);
  } else {
    $order = new ent_order();
    $order->data['client_ip'] = $_SERVER['REMOTE_ADDR'];
    $order->data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $order->data['date_created'] = date('Y-m-d H:i:s');
  }

  if (empty($_POST)) {

    $_POST = $order->data;

  // Convert to local currency
    foreach (array_keys($_POST['items']) as $key) {
      $_POST['items'][$key]['price'] = $_POST['items'][$key]['price'] / $_POST['currency_value'];
      $_POST['items'][$key]['tax'] = $_POST['items'][$key]['tax'] / $_POST['currency_value'];
    }
    foreach (array_keys($_POST['order_total']) as $key) {
      $_POST['order_total'][$key]['value'] = $_POST['order_total'][$key]['value'] / $_POST['currency_value'];
      $_POST['order_total'][$key]['tax'] = $_POST['order_total'][$key]['tax'] / $_POST['currency_value'];
    }

    if (empty($order->data['id'])) {
      $_POST['customer']['country_code'] = settings::get('default_country_code');
    }
  }

  document::$snippets['title'][] = !empty($order->data['id']) ? language::translate('title_edit_order', 'Edit Order') .' #'. $order->data['id'] : language::translate('title_create_new_order', 'Create New Order');

  breadcrumbs::add(language::translate('title_orders', 'Orders'), document::link(WS_DIR_ADMIN, ['doc' => 'orders'], ['app']));
  breadcrumbs::add(!empty($order->data['id']) ? language::translate('title_edit_order', 'Edit Order') .' #'. $order->data['id'] : language::translate('title_create_new_order', 'Create New Order'));

// Mark as read
  if (!empty($order->data['id'])) {
    database::query(
      "update ". DB_TABLE_PREFIX ."orders
      set unread = 0
      where id = ".  (int)$order->data['id'] ."
      limit 1;"
    );
  }

// Save data to database
  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['items'])) $_POST['items'] = [];
      if (empty($_POST['order_total'])) $_POST['order_total'] = [];
      if (empty($_POST['comments'])) $_POST['comments'] = [];

      if (!empty($_POST['items'])) {
        foreach (array_keys($_POST['items']) as $key) {
          $_POST['items'][$key]['price'] = (float)$_POST['items'][$key]['price'] * (float)$_POST['currency_value'];
          $_POST['items'][$key]['tax'] = (float)$_POST['items'][$key]['tax'] * (float)$_POST['currency_value'];
        }
      }

      if (!empty($_POST['order_total'])) {
        foreach (array_keys($_POST['order_total']) as $key) {
          if (empty($_POST['order_total'][$key]['calculate'])) $_POST['order_total'][$key]['calculate'] = false;
          $_POST['order_total'][$key]['value'] = (float)$_POST['order_total'][$key]['value'] * (float)$_POST['currency_value'];
          $_POST['order_total'][$key]['tax'] = (float)$_POST['order_total'][$key]['tax'] * (float)$_POST['currency_value'];
        }
      }

      $fields = [
        'unread',
        'language_code',
        'currency_code',
        'currency_value',
        'items',
        'order_total',
        'order_status_id',
        'shipping_option',
        'shipping_tracking_id',
        'shipping_tracking_url',
        'payment_option',
        'payment_transaction_id',
        'payment_receipt_url',
        'payment_terms',
        'incoterm',
        'display_prices_including_tax',
        'reference',
        'comments',
        'date_paid',
        'date_dispatched',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $order->data[$field] = $_POST[$field];
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
        if (isset($_POST['customer'][$field])) $order->data['customer'][$field] = $_POST['customer'][$field];
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
        if (isset($_POST['customer']['shipping_address'][$field])) $order->data['customer']['shipping_address'][$field] = $_POST['customer']['shipping_address'][$field];
      }

      $order->save();

      if (!empty($_POST['email_order_copy'])) {

        $bccs = [];
        foreach (preg_split('#[\s;,]+#', settings::get('email_order_copy'), -1, PREG_SPLIT_NO_EMPTY) as $email) {
          $bccs[] = $email;
        }

        $order->email_order_copy($order->data['customer']['email'], $bccs, $order->data['language_code']);
      }

      if (!empty($_GET['redirect_url'])) {
        $redirect_url = new ent_link($_GET['redirect_url']);
        $redirect_url->host = '';
      } else {
        $redirect_url = document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc' => 'orders']);
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
      if (empty($order->data['id'])) throw new Exception(language::translate('error_must_provide_order', 'You must provide an order'));

      $order->delete();

      if (empty($_GET['redirect_url'])) {
        $_GET['redirect_url'] = document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc' => 'orders']);
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
#order-items tr.highlight {
  border: 1px #f00 solid;
}
#order-items tr.extended {
  display: none;
}
#order-items tr.highlight + tr.extended {
  display: table-row;
}

#comments {
  height: 100%;
  bottom: 3em;
  padding: 1em;
}

#comments .bubble .private {
  position: absolute;
  top: 0.5em;
  right: 3em;
  cursor: pointer;
}
#comments .bubble .private input[name$="[hidden]"] {
  display: none;
}
#comments .bubble .private input[name$="[hidden]"] + .fa {
  opacity: 0.25;
}
#comments .bubble .private input[name$="[hidden]"]:checked + .fa {
  opacity: 1;
}

#comments .bubble .notify  {
  position: absolute;
  top: 0.5em;
  right: 4.5em;
  cursor: pointer;
}
#comments .bubble .notify input[name$="[notify]"] {
  display: none;
}
#comments .bubble .notify input[name$="[notify]"] + .fa {
  opacity: 0.25;
}
#comments .bubble .notify input[name$="[notify]"]:checked + .fa {
  opacity: 1;
}

#comments .bubble.semi-transparent {
  opacity: 0.5;
}

#modal-customer-picker tbody tr {
  cursor: pointer;
}
</style>

<?php echo functions::form_draw_form_begin('form_order', 'post'); ?>

  <div class="card card-app">
    <div class="card-header">
      <div class="card-title">
        <?php echo $app_icon; ?> <?php echo !empty($order->data['id']) ? language::translate('title_edit_order', 'Edit Order') .' #'. $order->data['id'] : language::translate('title_create_new_order', 'Create New Order'); ?>
      </div>
    </div>

    <div class="card-body">
      <div class="row">
        <div class="col-lg-9">

          <h2><?php echo language::translate('title_order_details', 'Order Details'); ?></h2>

          <div class="row" style="margin-bottom: 2em;">

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_order_status', 'Order Status'); ?></label>
              <?php echo functions::form_draw_order_status_list('order_status_id', true); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_language', 'Language'); ?></label>
              <?php echo functions::form_draw_languages_list('language_code', true); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_currency', 'Currency'); ?></label>
              <?php echo functions::form_draw_currencies_list('currency_code', true); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_currency_value', 'Currency Value'); ?></label>
              <?php echo functions::form_draw_decimal_field('currency_value', true, 6); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_date', 'Date'); ?></label>
              <div class="form-control" readonly><?php echo date(language::$selected['raw_datetime'], strtotime($order->data['date_created'])); ?></div>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_ip_address', 'IP Address'); ?></label>
              <div class="form-control" readonly><?php echo $order->data['client_ip']; ?> <a href="https://ip-api.com/#<?php echo $order->data['client_ip']; ?>" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></div>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_reference', 'Reference'); ?></label>
              <?php echo functions::form_draw_text_field('reference', true); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_order_copy', 'Order Copy'); ?></label>
              <div class="btn-group btn-block" data-toggle="buttons">
                <label class="btn btn-default<?php echo !empty($_POST['display_prices_including_tax']) ? ' active' : ''; ?>"><input type="radio" name="display_prices_including_tax" value="1"<?php echo !empty($_POST['display_prices_including_tax']) ? ' checked' : ''; ?> /><?php echo language::translate('title_incl_tax', 'Incl. Tax'); ?></label>
                <label class="btn btn-default<?php echo empty($_POST['display_prices_including_tax']) ? ' active' : ''; ?>"><input type="radio" name="display_prices_including_tax" value="0"<?php echo empty($_POST['display_prices_including_tax']) ? ' checked' : ''; ?> /><?php echo language::translate('title_excl_tax', 'Excl. Tax'); ?></label>
              </div>
            </div>
          </div>

          <div id="customer-details" class="row layout" style="margin-bottom: 2em;">
            <div id="billing-address" class="col-md-6">

              <h2><?php echo language::translate('title_billing_address', 'Billing Address'); ?></h2>

              <div class="form-group">
                <div class="input-group">
                  <div class="selected-account form-control">
                    <?php echo functions::form_draw_hidden_field('customer[id]', true); ?>
                    <?php echo language::translate('title_id', 'ID'); ?>:
                    <span class="id"><?php echo isset($_POST['customer']['id']) ? (int)$_POST['customer']['id'] : ''; ?></span> &ndash;
                    <span class="name"><?php echo $account_name; ?></span>
                    <a href="<?php echo document::href_link(WS_DIR_ADMIN, ['app' => 'customers', 'doc' => 'customer_picker']); ?>" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin-inline-start: 5px;"><?php echo language::translate('title_change', 'Change'); ?></a>
                  </div>
                  <?php echo functions::form_draw_button('get_address', language::translate('title_get_address', 'Get Address'), 'button'); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_company', 'Company'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[company]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_tax_id', 'Tax ID / VATIN'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[tax_id]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[firstname]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[lastname]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[address1]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[address2]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[postcode]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_city', 'City'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[city]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_country', 'Country'); ?></label>
                  <?php echo functions::form_draw_countries_list('customer[country_code]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
                  <?php echo form_draw_zones_list(isset($_POST['customer']['country_code']) ? $_POST['customer']['country_code'] : null, 'customer[zone_code]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
                  <?php echo functions::form_draw_email_field('customer[email]', true, 'required'); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_phone', 'Phone'); ?></label>
                  <?php echo functions::form_draw_phone_field('customer[phone]', true); ?>
                </div>
              </div>

            </div>

            <div id="shipping-address" class="col-md-6">

              <h2><?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></h2>

              <div class="form-group">
                <?php echo functions::form_draw_button('copy_billing_address', language::translate('title_copy_billing_address', 'Copy Billing Address'), 'button', 'class="btn btn-default btn-block"'); ?>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_company', 'Company'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[shipping_address][company]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[shipping_address][firstname]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[shipping_address][lastname]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[shipping_address][address1]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[shipping_address][address2]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[shipping_address][postcode]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_city', 'City'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[shipping_address][city]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_country', 'Country'); ?></label>
                  <?php echo functions::form_draw_countries_list('customer[shipping_address][country_code]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
                  <?php echo form_draw_zones_list(isset($_POST['customer']['shipping_address']['country_code']) ? $_POST['customer']['shipping_address']['country_code'] : null, 'customer[shipping_address][zone_code]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_phone', 'Phone'); ?></label>
                  <?php echo functions::form_draw_phone_field('customer[shipping_address][phone]', true); ?>
                </div>
              </div>
            </div>

          </div>

          <div class="row" id="payment-details">
            <div class="col-md-6">

              <h2><?php echo language::translate('title_payment_details', 'Payment Details'); ?></h2>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_option_id', 'Option ID'); ?></label>
                  <?php echo functions::form_draw_text_field('payment_option[id]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_name', 'Name'); ?></label>
                  <?php echo functions::form_draw_text_field('payment_option[name]', true); ?>
                </div>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_transaction_id', 'Transaction ID'); ?></label>
                <?php echo functions::form_draw_text_field('payment_transaction_id', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_receipt_url', 'Receipt URL'); ?></label>
                <?php echo functions::form_draw_url_field('payment_receipt_url', true); ?>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_payment_terms', 'Payment Terms'); ?></label>
                  <?php echo functions::form_draw_payment_terms_list('payment_terms', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_date_paid', 'Date Paid'); ?></label>
                  <?php echo functions::form_draw_datetime_field('date_paid', true); ?>
                </div>
              </div>
            </div>

            <div class="col-md-6" id="shipping-details">

              <h2><?php echo language::translate('title_shipping_information', 'Shipping Information'); ?></h2>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_option_id', 'Option ID'); ?></label>
                  <?php echo functions::form_draw_text_field('shipping_option[id]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_name', 'Name'); ?></label>
                  <?php echo functions::form_draw_text_field('shipping_option[name]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-8">
                  <label><?php echo language::translate('title_tracking_id', 'Tracking ID'); ?></label>
                  <?php echo functions::form_draw_text_field('shipping_tracking_id', true); ?>
                </div>

                <div class="form-group col-md-4">
                  <label><?php echo language::translate('title_weight', 'Weight'); ?></label>
                  <span class="form-control"><?php echo weight::format($order->data['weight_total'], $order->data['weight_class']) ?></span>
                </div>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_tracking_url', 'Tracking URL'); ?></label>
                <?php echo functions::form_draw_url_field('shipping_tracking_url', true); ?>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_incoterm', 'Incoterm'); ?></label>
                  <?php echo functions::form_draw_incoterms_list('incoterm', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_date_dispatched', 'Date Dispatched'); ?></label>
                  <?php echo functions::form_draw_datetime_field('date_dispatched', true); ?>
                </div>
              </div>

            </div>
          </div>
        </div>

        <div class="col-lg-3">

          <h2><?php echo language::translate('title_comments', 'Comments'); ?></h2>

          <div id="comments" class="form-control bubbles">
<?php
  foreach (array_keys($_POST['comments']) as $key) {

    switch($_POST['comments'][$key]['author']) {
      case 'customer':
        $type = 'remote';
        break;
      case 'staff':
        $type = 'local';
        break;
      default:
        $type = 'event';
        break;
    }

    if (!empty($_POST['comments'][$key]['hidden'])) $type .= ' semi-transparent';
?>
            <div class="bubble <?php echo $type; ?>">
              <?php echo functions::form_draw_hidden_field('comments['. $key .'][id]', true); ?>
              <?php echo functions::form_draw_hidden_field('comments['. $key .'][order_id]', true); ?>
              <?php echo functions::form_draw_hidden_field('comments['. $key .'][author]', true); ?>
              <?php echo functions::form_draw_hidden_field('comments['. $key .'][text]', true); ?>

                <div class="text"><?php echo nl2br(functions::escape_html($_POST['comments'][$key]['text'])); ?></div>

              <div class="date"><?php echo language::strftime(language::$selected['format_datetime'], !empty($_POST['comments'][$key]['date_created']) ? strtotime($_POST['comments'][$key]['date_created']) : 0); ?></div>

              <div class="actions">
                <a class="remove" href="#" title="<?php echo functions::escape_html(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('fa-times-circle'); ?></a>
                <label class="private" title="<?php echo functions::escape_html(language::translate('title_hidden', 'Hidden')); ?>"><?php echo functions::form_draw_checkbox('comments['.$key .'][hidden]', '1', true); ?> <?php echo functions::draw_fonticon('fa-eye-slash'); ?></label>
              </div>
            </div>
            <?php } ?>

            <div class="add text-end"><button class="btn btn-default" type="button" title="<?php echo language::translate('title_add', 'Add'); ?>"><?php echo functions::draw_fonticon('fa-plus'); ?> <?php echo language::translate('title_add_comment', 'Add Comment'); ?></button></div>
          </div>

        </div>
      </div>
    </div>

    <div class="card-action">
      <ul class="list-inline">
        <li>
          <div class="checkbox">
            <label><?php echo functions::form_draw_checkbox('email_order_copy', '1', true); ?> <?php echo language::translate('text_send_order_copy_email', 'Send order copy email'); ?></label>
          </div>
        </li>
        <li>
          <div class="checkbox">
            <label><?php echo functions::form_draw_checkbox('unread', '1', false); ?> <?php echo language::translate('title_mark_as_unread', 'Mark as unread'); ?></label>
          </div>
        </li>
        <li>
          <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
          <?php echo !empty($order->data['id']) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(&quot;'. language::translate('text_are_you_sure', 'Are you sure?') .'&quot;)) return false;"', 'delete') : ''; ?>
          <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        </li>
      </ul>
    </div>
  </div>

  <div id="order-items" class="card">
    <div class="card-header">
      <div class="card-title"><?php echo language::translate('title_order_items', 'Order Items'); ?></div>
    </div>

    <table class="table table-striped table-hover table-input">
      <thead>
        <tr>
          <th><?php echo language::translate('title_item', 'Item'); ?></th>
          <th style="width: 200px;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
          <th style="width: 150px;"><?php echo language::translate('title_weight', 'Weight'); ?></th>
          <th style="width: 175px;"><?php echo language::translate('title_dimensions', 'Dimensions'); ?></th>
          <th style="width: 125px;" class="text-center"><?php echo language::translate('title_qty', 'Qty'); ?></th>
          <th style="width: 200px;" class="text-center"><?php echo language::translate('title_unit_price', 'Unit Price'); ?></th>
          <th style="width: 200px;" class="text-center"><?php echo language::translate('title_tax', 'Tax'); ?></th>
          <th style="width: 150px;">&nbsp;</th>
        </tr>
      </thead>

      <tbody>
        <?php if (!empty($_POST['items'])) foreach (array_keys($_POST['items']) as $key) { ?>
        <tr class="item">
          <td>
                  <?php echo !empty($_POST['items'][$key]['product_id']) ? '<a href="'. document::href_link(WS_DIR_ADMIN, ['app' => 'catalog', 'doc' => 'edit_product', 'product_id' => $_POST['items'][$key]['product_id']]) .'" target="_blank">'. functions::escape_html($_POST['items'][$key]['name']) .'</a>' : functions::escape_html($_POST['items'][$key]['name']); ?> <?php echo '<a class="float-end" href="'. document::href_ilink('product', ['product_id' => $_POST['items'][$key]['product_id']]) .'" target="_blank">'. functions::draw_fonticon('fa-external-link') .'</a>'; ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][id]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][product_id]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][option_stock_combination]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][name]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['. $key .'][sku]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['. $key .'][gtin]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['. $key .'][taric]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['. $key .'][weight]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['. $key .'][weight_class]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['. $key .'][dim_x]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['. $key .'][dim_y]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['. $key .'][dim_z]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['. $key .'][dim_class]', true); ?>
<?php
      if (!empty($_POST['items'][$key]['options'])) {
        foreach (array_keys($_POST['items'][$key]['options']) as $field) {
          echo '<div>' . PHP_EOL
             . ' - '. functions::escape_html($field) .': ' . PHP_EOL;
          if (is_array($_POST['items'][$key]['options'][$field])) {
            $use_comma = false;
            foreach (array_keys($_POST['items'][$key]['options'][$field]) as $k) {
              echo '  ' . functions::form_draw_hidden_field('items['.$key.'][options]['.$field.']['.$k.']', true) . functions::escape_html($_POST['items'][$key]['options'][$field][$k]);
              if ($use_comma) echo ', ';
              $use_comma = true;
            }
          } else {
            echo '  ' . functions::form_draw_hidden_field('items['.$key.'][options]['.$field.']', true) . functions::escape_html($_POST['items'][$key]['options'][$field]);
          }
          echo '</div>' . PHP_EOL;
        }
      } else {
        echo functions::form_draw_hidden_field('items['.$key.'][options]', '');
      }
?>
          </td>
              <td class="sku"><?php echo functions::escape_html($_POST['items'][$key]['sku']); ?></td>
          <td>
                <span class="weight"><?php echo (float)$_POST['items'][$key]['weight']; ?></span> <span class="weight_class"><?php echo functions::escape_html($_POST['items'][$key]['weight_class']); ?></span>
          </td>
          <td>
                <span class="dim_x"><?php echo (float)$_POST['items'][$key]['dim_x']; ?></span> x <span class="dim_y"><?php echo (float)$_POST['items'][$key]['dim_y']; ?></span> x <span class="dim_z"><?php echo (float)$_POST['items'][$key]['dim_z']; ?></span> <span class="dim_class"><?php echo functions::escape_html($_POST['items'][$key]['dim_class']); ?></span>
          </td>
          <td><?php echo functions::form_draw_decimal_field('items['. $key .'][quantity]', true, 2); ?></td>
          <td><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'items['. $key .'][price]', true); ?></td>
          <td><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'items['. $key .'][tax]', true); ?></td>
          <td class="text-end">
            <a class="btn btn-default btn-sm edit" href="#" title="<?php echo functions::escape_html(language::translate('title_edit', 'Edit')); ?>"><?php echo functions::draw_fonticon('fa-pencil fa-fw'); ?></a>
            <a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_html(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('remove'); ?></a>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>

    <div class="card-body">
      <a class="btn btn-default add-product" href="<?php echo document::href_link('', ['doc' => 'product_picker'], ['app'], []); ?>" data-toggle="lightbox" data-width="" data-href="<?php echo document::href_link('', ['doc' => 'product_picker'], ['app'], []); ?>"><?php echo functions::draw_fonticon('fa-plus'); ?> <?php echo language::translate('title_add_product', 'Add Product'); ?></a>
      <div class="btn btn-default add-custom-item"><?php echo functions::draw_fonticon('fa-plus'); ?> <?php echo language::translate('title_add_custom_item', 'Add Custom Item'); ?></div>
    </div>
  </div>

  <div class="card">

    <div class="card-header">
      <div class="card-title"><?php echo language::translate('title_order_total', 'Order Total'); ?></div>
    </div>

    <table  id="order-total" class="table table-striped table-hover">
      <thead>
        <tr>
          <th style="width: 30px;">&nbsp;</th>
          <th style="width: 250px;"><?php echo language::translate('title_module_id', 'Module ID'); ?></th>
          <th class="text-end"><?php echo language::translate('title_title', 'Title'); ?></th>
          <th style="width: 250px;"><?php echo language::translate('title_value', 'Value'); ?></th>
          <th style="width: 250px;"><?php echo language::translate('title_tax', 'Tax'); ?></th>
          <th style="width: 75px;">&nbsp;</th>
        </tr>
      </thead>

      <tbody>
<?php
  if (empty($_POST['order_total'])) {
    $_POST['order_total'][] = [
      'id' => '',
      'module_id' => 'ot_subtotal',
      'title' => language::translate('title_subtotal', 'Subtotal'),
      'value' => '0',
      'tax' => '0',
      'calculate' => '0',
    ];
  }
  foreach (array_keys($_POST['order_total']) as $key) {
    switch($_POST['order_total'][$key]['module_id']) {
      case 'ot_subtotal':
?>
        <tr>
          <td class="text-end">&nbsp;</td>
          <td class="text-end"><?php echo functions::form_draw_hidden_field('order_total['. $key .'][id]', true) . functions::form_draw_text_field('order_total['. $key .'][module_id]', true, 'readonly'); ?></td>
          <td class="text-end"><?php echo functions::form_draw_text_field('order_total['. $key .'][title]', true, 'class="form-control text-end"'); ?></td>
          <td class="text-end">
            <div class="input-group">
              <span class="input-group-text"><?php echo functions::form_draw_checkbox('order_total['. $key .'][calculate]', '1', true, 'disabled title="'. functions::escape_html(language::translate('title_calculate', 'Calculate')).'"'); ?></span>
              <?php echo functions::form_draw_decimal_field('order_total['. $key .'][value]', true, !empty(currency::$currencies[$_POST['currency_code']]) ? currency::$currencies[$_POST['currency_code']]['decimals'] : 2, null, null, 'style="text-align: end;"'); ?>
              <span class="input-group-text"><?php echo $_POST['currency_code']; ?></span>
            </div>
          </td>
          <td class="text-end"><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'order_total['. $key .'][tax]', true, 'style="text-align: end;"'); ?></td>
          <td></td>
        </tr>
<?php
        break;
      default:
?>
        <tr>
          <td class="text-end"><a href="#" class="add btn btn-default btn-sm" title="<?php echo functions::escape_html(language::translate('text_insert_before', 'Insert before')); ?>"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #66cc66;"'); ?></a></td>
          <td class="text-end"><?php echo functions::form_draw_hidden_field('order_total['. $key .'][id]', true) . functions::form_draw_text_field('order_total['. $key .'][module_id]', true); ?></td>
          <td class="text-end"><?php echo functions::form_draw_text_field('order_total['. $key .'][title]', true, 'style="text-align: end;"'); ?></td>
          <td class="text-end">
            <div class="input-group">
            <span class="input-group-text"><?php echo functions::form_draw_checkbox('order_total['. $key .'][calculate]', '1', true, 'title="'. functions::escape_html(language::translate('title_calculate', 'Calculate')) .'"'); ?></span>
            <?php echo functions::form_draw_decimal_field('order_total['. $key .'][value]', true, !empty(currency::$currencies[$_POST['currency_code']]) ? currency::$currencies[$_POST['currency_code']]['decimals'] : 2, null, null, 'style="text-align: end;"'); ?>
            <span class="input-group-text"><?php echo $_POST['currency_code']; ?></span>
            </div>
          </td>
          <td class="text-end"><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'order_total['. $key .'][tax]', true, 'style="text-align: end;"'); ?></td>
          <td><a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_html(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
        </tr>
<?php
        break;
    }
  }
?>
        <tr>
          <td colspan="6"><a class="add btn btn-default btn-sm" href="#" title="<?php echo functions::escape_html(language::translate('title_insert', 'Insert')); ?>"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #66cc66;"'); ?></a></td>
        </tr>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="6" class="text-end" style="font-size: 1.5em;"><?php echo language::translate('title_payment_due', 'Payment Due'); ?>: <strong class="total"><?php echo currency::format($order->data['payment_due'], false, $_POST['currency_code'], $_POST['currency_value']); ?></strong></td>
        </tr>
      </tfoot>
    </table>



  </div>
<?php echo functions::form_draw_form_end(); ?>

<div id="modal-customer-picker" class="modal fade" style="max-width: 640px; display: none;">

  <h2><?php echo language::translate('title_customer', 'Customer'); ?></h2>

  <div class="modal-body">
    <div class="form-group">
      <?php echo functions::form_draw_text_field('query', true, 'placeholder="'. functions::escape_html(language::translate('title_search', 'Search')) .'"'); ?>
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

<div id="modal-edit-order-item" class="modal fade" style="max-width: 640px; display: none;">

  <h2><?php echo language::translate('title_edit_order_item', 'Edit Order Item'); ?></h2>

  <div class="modal-body">

    <div class="row">
      <div class="form-group col-md-9">
        <label><?php echo language::translate('title_name', 'Name'); ?></label>
        <?php echo functions::form_draw_text_field('name', ''); ?>
      </div>

      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_product_id', 'Product ID'); ?></label>
        <?php echo functions::form_draw_number_field('product_id', ''); ?>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_sku', 'SKU'); ?></label>
        <?php echo functions::form_draw_text_field('sku', ''); ?>
      </div>

      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_gtin', 'GTIN'); ?></label>
        <?php echo functions::form_draw_text_field('gtin', ''); ?>
      </div>

      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_taric', 'TARIC'); ?></label>
        <?php echo functions::form_draw_text_field('taric', ''); ?>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_weight', 'Weight'); ?></label>
        <div class="input-group">
          <?php echo functions::form_draw_decimal_field('weight', '', 2, 0); ?>
          <?php echo functions::form_draw_weight_classes_list('weight_class', settings::get('store_weight_class'), false, 'style="width: auto;"'); ?>
        </div>
      </div>

      <div class="form-group col-md-8">
        <label><?php echo language::translate('title_dimensions', 'Dimensions'); ?></label>
        <div class="input-group">
          <?php echo functions::form_draw_decimal_field('dim_x', '', 1, 0); ?>
          <span class="input-group-text">x</span>
          <?php echo functions::form_draw_decimal_field('dim_y', '', 1, 0); ?>
          <span class="input-group-text">x</span>
          <?php echo functions::form_draw_decimal_field('dim_z', '', 1, 0); ?>
          <?php echo functions::form_draw_length_classes_list('dim_class', settings::get('store_length_class'), false, 'style="width: auto;"'); ?>
        </div>
      </div>
    </div>

    <div class="row">
        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_quantity', 'quantity'); ?></label>
        <?php echo functions::form_draw_decimal_field('quantity', ''); ?>
      </div>

        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_price', 'Price'); ?></label>
        <?php echo functions::form_draw_currency_field($_POST['currency_code'], 'price', ''); ?>
      </div>

        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_tax', 'Tax'); ?></label>
        <?php echo functions::form_draw_currency_field($_POST['currency_code'], 'tax', ''); ?>
      </div>
    </div>

    <div>
      <?php echo functions::form_draw_button('ok', language::translate('title_ok', 'OK'), 'button', '', 'ok'); ?>
      <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.featherlight.close();"', 'cancel'); ?>
    </div>
  </div>
</div>

<div id="modal-add-order-item" class="modal fade" style="max-width: 640px; display: none;">

  <h2><?php echo language::translate('title_add_order_item', 'Add Order Item'); ?></h2>

  <div class="modal-body">

    <div class="row">
      <div class="form-group col-md-9">
        <label><?php echo language::translate('title_name', 'Name'); ?></label>
        <?php echo functions::form_draw_text_field('name', ''); ?>
      </div>

      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_product_id', 'Product ID'); ?></label>
        <?php echo functions::form_draw_number_field('product_id', ''); ?>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_sku', 'SKU'); ?></label>
        <?php echo functions::form_draw_text_field('sku', ''); ?>
      </div>

      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_gtin', 'GTIN'); ?></label>
        <?php echo functions::form_draw_text_field('gtin', ''); ?>
      </div>

      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_taric', 'TARIC'); ?></label>
        <?php echo functions::form_draw_text_field('taric', ''); ?>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_weight', 'Weight'); ?></label>
        <div class="input-group">
          <?php echo functions::form_draw_decimal_field('weight', '', 2, 0); ?>
          <?php echo functions::form_draw_weight_classes_list('weight_class', '', false, 'style="width: auto;"'); ?>
        </div>
      </div>

      <div class="form-group col-md-8">
        <label><?php echo language::translate('title_dimensions', 'Dimensions'); ?></label>
        <div class="input-group">
          <?php echo functions::form_draw_decimal_field('dim_x', '', 1, 0); ?>
          <span class="input-group-text">x</span>
          <?php echo functions::form_draw_decimal_field('dim_y', '', 1, 0); ?>
          <span class="input-group-text">x</span>
          <?php echo functions::form_draw_decimal_field('dim_z', '', 1, 0); ?>
          <?php echo functions::form_draw_length_classes_list('dim_class', '', false, 'style="width: auto;"'); ?>
        </div>
      </div>
    </div>

    <div class="row">
        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
        <?php echo functions::form_draw_decimal_field('quantity', '0'); ?>
      </div>

        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_price', 'Price'); ?></label>
        <?php echo functions::form_draw_currency_field($_POST['currency_code'], 'price', '0'); ?>
      </div>

        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_tax', 'Tax'); ?></label>
        <?php echo functions::form_draw_currency_field($_POST['currency_code'], 'tax', '0'); ?>
      </div>
    </div>

    <div>
      <?php echo functions::form_draw_button('ok', language::translate('title_ok', 'OK'), 'button', '', 'ok'); ?>
      <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.featherlight.close();"', 'cancel'); ?>
    </div>
  </div>
</div>

<script>
// Order

  $('select[name="currency_code"]').change(function(e){
    $('input[name="currency_value"]').val($(this).find('option:selected').data('value'));
    $('input[data-type="currency"]').closest('.input-group').find('.input-group-text').text($(this).val());
    calculate_total();
  });

// Customer

  $('#customer-details button[name="get_address"]').click(function() {
    $.ajax({
      url: '<?php echo document::link(WS_DIR_ADMIN, ['app' => 'customers', 'doc' => 'get_address.json']); ?>',
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
      url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
      type: 'get',
      cache: true,
      async: false,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        //alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
      },
      success: function(data) {
        $('select[name="customer[zone_code]"]').html('');
        if ($('select[name="customer[zone_code]"]').attr('disabled')) $('select[name="customer[zone_code]"]').prop('disabled', false);
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
    console.log('a');

    let fields = ['company', 'firstname', 'lastname', 'address1', 'address2', 'postcode', 'city', 'country_code', 'zone_code', 'phone'];
    $.each(fields, function(key, field){
          console.log($(':input[name="customer[shipping_address]['+ field +']"]').length);
      $(':input[name="customer[shipping_address]['+ field +']"]').val(
        $(':input[name="customer['+ field +']"]').val()
      ).trigger('change');
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
      url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
      type: 'get',
      cache: true,
      async: true,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        //alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
      },
      success: function(data) {
        $('select[name="customer[shipping_address][zone_code]"]').html('');
        if ($('select[name="customer[shipping_address][zone_code]"]').attr('disabled')) $('select[name="customer[shipping_address][zone_code]"]').prop('disabled', false);
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

  $('select[name="language_code"], select[name="currency_code"], input[name="currency_value"], :input[name^="customer"]').on('input change', function(){
    var params = {
      language_code: $('select[name="language_code"]').val(),
      currency_code: $('select[name="currency_code"]').val(),
      currency_value: $('input[name="currency_value"]').val(),
      customer: {
        id: $(':input[name="customer[id]"]').val(),
        tax_id: $('input[name="customer[tax_id]"]').val(),
        company: $('input[name="customer[company]"]').val(),
        country_code: $('select[name="customer[country_code]"]').val(),
        zone_code: $('select[name="customer[zone_code]"]').val(),
        city: $('select[name="customer[city]"]').val(),
        shipping_address: {
          company: $('input[name="customer[shipping_address][company]"]').val(),
          country_code: $('select[name="customer[shipping_address][country_code]"]').val(),
          zone_code: $('select[name="customer[shipping_address][zone_code]"]').val(),
          city: $('select[name="customer[shipping_address][city]"]').val(),
        }
      }
    }

    $('.add-product').attr('href', $('.add-product').data('href') +'&'+ $.param(params));
  });

  $(':input[name^="customer"]').first().trigger('input');

// Comments

  $('#comments').on('input', 'textarea[name^="comments"][name$="[text]"]', function(){
    $(this).height('auto').height('calc(' + $(this).prop('scrollHeight') + 'px + 1em) ');
  }).trigger('input');

  $("#comments").animate({scrollTop: $('#comments').prop('scrollHeight')}, 2000);

  var new_comment_index = 0;
  $('#comments .add').click(function(e) {

    e.preventDefault();
    while ($('input[name="comments[new_'+new_comment_index+'][id]"]').length) new_comment_index++;
    var output = '  <div class="bubble local me">'
               + '    <?php echo functions::form_draw_hidden_field('comments[new_comment_index][id]', ''); ?>'
               + '    <?php echo functions::form_draw_hidden_field('comments[new_comment_index][author]', 'staff'); ?>'
               + '    <?php echo functions::form_draw_hidden_field('comments[new_comment_index][date_created]', language::strftime(language::$selected['format_datetime'])); ?>'
               + '    <div class="text"><?php echo functions::escape_js(functions::form_draw_textarea('comments[new_comment_index][text]', '')); ?></div>'
               + '    <div class="date"><?php echo language::strftime(language::$selected['format_datetime']); ?></div>'
               + '    <div class="actions">'
               + '      <label class="notify" title="<?php echo functions::escape_html(language::translate('title_notify', 'Notify')); ?>"><?php echo functions::form_draw_checkbox('comments[new_comment_index][notify]', 1, true); ?> <?php echo functions::draw_fonticon('fa-envelope'); ?></label>'
               + '      <label class="private" title="<?php echo functions::escape_html(language::translate('title_hidden', 'Hidden')); ?>"><?php echo functions::form_draw_checkbox('comments[new_comment_index][hidden]', 1, true); ?> <?php echo functions::draw_fonticon('fa-eye-slash'); ?></label>'
               + '      <a class="remove" href="#" title="<?php echo functions::escape_html(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg fa-fw'); ?></a>'
               + '    </div>'
               + '  </div>';
    output = output.replace(/new_comment_index/g, 'new_' + new_comment_index);
    $(this).before(output);
    $(this).closest('#comments .bubbles textarea:last-child').focus();
  });

  $('#comments').on('click', ':input[name$="[hidden]"]', function(e) {
    $(this).closest('.bubble').find(':input[name$="[notify]"]').prop('checked', false).trigger('change');
  });

  $('#comments').on('click', ':input[name$="[notify]"]', function(e) {
    $(this).closest('.bubble').find(':input[name$="[hidden]"]').prop('checked', false).trigger('change');
  });

  $('#comments').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('.bubble').remove();
  });

  $('#comments').on('change', 'input[name^="comments"][name$="[hidden]"]', function(e) {
    if ($(this).is(':checked')) {
      $(this).closest('.bubble').addClass('semi-transparent');
    } else {
      $(this).closest('.bubble').removeClass('semi-transparent');
    }
  });

// Order items

  $('#order-items').on('click', '.edit', function(){
    $.featherlight('#modal-edit-order-item');

    var modal = $('.featherlight.active'),
        row = $(this).closest('tr');

    $(modal).data('row', row);

    $.each($(modal).find(':input'), function(i,element){
      var field = $(element).attr('name');
      var value = $(row).find(':input[name$="['+field+']"]').val();
      if ($(modal).find(':input[name="'+field+'"]').attr('type') == 'number') value = parseFloat(value || 0);
      $(modal).find(':input[name="'+field+'"]').val(value);
    });
  });

  $('#order-items .add-custom-item').click(function(){
    $.featherlight('#modal-add-order-item');

    var modal = $('.featherlight.active'),
        row = $(this).closest('tr');

    $(modal).data('row', '');
  });

  $('#modal-edit-order-item button[name="ok"]').click(function(e){

    var modal = $('.featherlight.active');
    var row = $(modal).data('row');
    var fields = [
      'name',
      'sku',
      'gtin',
      'taric',
      'weight',
      'weight_class',
      'dim_x',
      'dim_y',
      'dim_z',
      'dim_class',
      'price',
      'tax',
    ];

    if (row == '') {
      var item = {};
      $.each($(modal).find(':input'), function(i,element){
        var field = $(element).attr('name');
        item[field] = $(modal).find(':input[name="'+field+'"]').val();
      });
      addItem(item);
    }

    $(modal).find(':input[name="weight_class"]').val('<?php echo settings::get('store_weight_class'); ?>');
    $(modal).find(':input[name="length_class"]').val('<?php echo settings::get('store_length_class'); ?>');

    $.each($(modal).find(':input'), function(i,element){
      var field = $(element).attr('name');
      var value = $(modal).find(':input[name="'+field+'"]').val();
      $(row).find(':input[name$="['+field+']"]').val(value).trigger('keyup');
      $(row).find('.'+field).text(value);
    });

    $.featherlight.close();
  });

  $('#modal-add-order-item button[name="ok"]').click(function(e){

    var modal = $('.featherlight.active');
    var row = $(modal).data('row');
    var item = {};
    var fields = [
      'name',
      'sku',
      'gtin',
      'taric',
      'weight',
      'weight_class',
      'dim_x',
      'dim_y',
      'dim_z',
      'dim_class',
      'price',
      'tax',
    ];

    $.each($(modal).find(':input'), function(i,element){
      var field = $(element).attr('name');
      item[field] = $(modal).find(':input[name="'+field+'"]').val();
    });

    addItem(item);

    $.featherlight.close();
  });

  var new_item_index = 0;
  window.addItem = function(item) {
    new_item_index++;

    var output = '  <tr class="item">'
               + '    <td>' + item.name
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][id]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][product_id]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][option_stock_combination]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][options]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][name]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][sku]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][gtin]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][taric]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][weight]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][weight_class]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][dim_x]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][dim_y]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][dim_z]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][dim_class]', '')); ?>'
               + '    </td>'
               + '    <td>'+ item.sku +'</td>'
               + '    <td>'
               + '      <span class="weight"></span> <span class="weight_class"></span>'
               + '    </td>'
               + '    <td>'
               + '      <span class="dim_x"></span> x <span class="dim_y"></span> x <span class="dim_z"></span> <span class="dim_class"></span>'
               + '    </td>'
               + '    <td><?php echo functions::escape_js(functions::form_draw_decimal_field('items[new_item_index][quantity]', '', 2)); ?></td>'
               + '    <td><?php echo functions::escape_js(functions::form_draw_currency_field($_POST['currency_code'], 'items[new_item_index][price]', '')); ?></td>'
               + '    <td><?php echo functions::escape_js(functions::form_draw_currency_field($_POST['currency_code'], 'items[new_item_index][tax]', '')); ?></td>'
               + '    <td class="text-end">'
               + '      <a class="btn btn-default btn-sm edit" href="#" title="<?php echo functions::escape_js(language::translate('title_edit', 'Edit'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-pencil fa-fw')); ?></a>'
               + '      <a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('remove')); ?></a>'
               + '    </td>'
               + '  </tr>';

    output = output.replace(/new_item_index/g, 'new_' + new_item_index);
    $('#order-items tbody').append(output);

    var row = $('#order-items tbody tr.item').last();
    $(row).find('*[name$="[product_id]"]').val(item.product_id);
    $(row).find('*[name$="[sku]"]').val(item.sku);
    $(row).find('*[name$="[option_stock_combination]"]').val(item.option_stock_combination);
    $(row).find('*[name$="[name]"]').val(item.name);
    $(row).find('*[name$="[gtin]"]').val(item.gtin);
    $(row).find('*[name$="[taric]"]').val(item.taric);
    $(row).find('*[name$="[weight]"]').val(item.weight);
    $(row).find('*[name$="[weight_class]"]').val(item.weight_class);
    $(row).find('*[name$="[dim_x]"]').val(item.dim_x);
    $(row).find('*[name$="[dim_y]"]').val(item.dim_y);
    $(row).find('*[name$="[dim_z]"]').val(item.dim_z);
    $(row).find('*[name$="[dim_class]"]').val(item.dim_class);
    $(row).find('*[name$="[quantity]"]').val(item.quantity);
    $(row).find('*[name$="[price]"]').val(item.price);
    $(row).find('*[name$="[tax]"]').val(item.tax);

    $(row).find('[data-type="currency"]').parent().find('.input-group-text').text($(':input[name="currency_code"]').val());
    $(row).find('.weight').text(String(item.weight).trim('.0'));
    $(row).find('.weight_class').text(item.weight_class);
    $(row).find('.dim_x').text(String(item.dim_x).trim('.0'));
    $(row).find('.dim_y').text(String(item.dim_y).trim('.0'));
    $(row).find('.dim_z').text(String(item.dim_z).trim('.0'));
    $(row).find('.dim_class').text(item.dim_class);

    if (item.options) {
      var product_options = '';
      $.each(item.options, function(group, value) {
        product_options += '<div>'
                         + '  - '+ group +': ';
        if ($.isArray(value)) {
          $.each(value, function(i, array_value) {
            product_options += '<input type="hidden" name="items[new_'+ new_item_index +'][options]['+ group +'][]" value="'+ array_value +'" />' + array_value +', ';
          });
          product_options = product_options.substring(0, product_options.length - 2);
        } else {
          product_options += '<input type="hidden" name="items[new_'+ new_item_index +'][options]['+ group +']" value="'+ value +'" />' + value;
        }
        product_options += '</div>';
      });
      $(row).find('input[type="hidden"][name$="[options]"]').replaceWith(product_options);
    }

    calculate_total();
  }

  $('#order-items').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

// Order Total

  var new_ot_row_index = 0;
  $('#order-total').on('click', '.add', function(e) {
    while ($('input[name="order_total['+new_ot_row_index+'][id]"]').length) new_ot_row_index++;
    e.preventDefault();
    var output = '  <tr>'
               + '    <td class="text-end"><a href="#" class="add btn btn-default btn-sm" title="<?php echo functions::escape_js(language::translate('text_insert_before', 'Insert before'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-plus', 'style="color: #66cc66;"')); ?></a></td>'
               + '    <td class="text-end"><?php echo functions::escape_js(functions::form_draw_hidden_field('order_total[new_ot_row_index][id]', '')); ?><?php echo functions::escape_js(functions::form_draw_text_field('order_total[new_ot_row_index][module_id]', '')); ?></td>'
               + '    <td class="text-end"><?php echo functions::escape_js(functions::form_draw_text_field('order_total[new_ot_row_index][title]', '', 'style="text-align: end;"')); ?></td>'
               + '    <td class="text-end">'
               + '      <div class="input-group">'
               + '        <span class="input-group-text"><?php echo functions::escape_js(functions::form_draw_checkbox('order_total[new_ot_row_index][calculate]', '1', '1', 'title="'. functions::escape_html(language::translate('title_calculate', 'Calculate')) .'"')); ?></span>'
               + '        <?php echo functions::form_draw_decimal_field('order_total[new_ot_row_index][value]', true, !empty(currency::$currencies[$_POST['currency_code']]) ? currency::$currencies[$_POST['currency_code']]['decimals'] : 2, null, null, 'style="text-align: end;"'); ?>'
               + '        <span class="input-group-text"><?php echo $_POST['currency_code']; ?></span>'
               + '      </div>'
               + '    </td>'
               + '    <td class="text-end"><?php echo functions::escape_js(functions::form_draw_currency_field($_POST['currency_code'], 'order_total[new_ot_row_index][tax]', currency::format_raw(0), 'style="text-align: end;"')); ?></td>'
               + '    <td><a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('remove')); ?></a></td>'
               + '  </tr>';
  output = output.replace(/new_ot_row_index/g, 'new_' + new_ot_row_index);
  $(this).closest('tr').before(output);
  new_ot_row_index++;
  });

  $('#order-total').on('click', '.remove', function(e) {
    e.preventDefault();
  $(this).closest('tr').remove();
  });

  function calculate_total() {

    var subtotal = 0;
    $('input[name^="items["][name$="[price]"]').each(function() {
      subtotal += parseFloat($(this).val() || 0) * parseFloat($(this).closest('tr').find('input[name^="items["][name$="[quantity]"]').val() || 0);
    });
    subtotal = parseFloat(subtotal.toFixed($('select[name="currency_code"] option:selected').data('decimals')) || 0);
    $('input[name^="order_total["][value="ot_subtotal"]').closest('tr').find('input[name^="order_total["][name$="[value]"]').val(subtotal);

    var subtotal_tax = 0;
    $('input[name^="items["][name$="[tax]"]').each(function() {
      subtotal_tax += parseFloat($(this).val() || 0) * parseFloat($(this).closest('tr').find('input[name^="items["][name$="[quantity]"]').val() || 0);
    });
    subtotal_tax = parseFloat(subtotal_tax.toFixed($('select[name="currency_code"] option:selected').data('decimals')) || 0);
    $('input[name^="order_total["][value="ot_subtotal"]').closest('tr').find('input[name^="order_total["][name$="[tax]"]').val(subtotal_tax);

    var order_total = subtotal + subtotal_tax;
    $('input[name^="order_total["][name$="[value]"]').each(function() {
      if ($(this).closest('tr').find('input[name^="order_total["][name$="[calculate]"]').is(':checked')) {
        order_total += parseFloat($(this).val() || 0);
      }
    });

    $('input[name^="order_total["][name$="[tax]"]').each(function() {
      if ($(this).closest('tr').find('input[name^="order_total["][name$="[calculate]"]').is(':checked')) {
        order_total += parseFloat($(this).val() || 0);
      }
    });

    order_total = parseFloat(order_total.toFixed($('select[name="currency_code"] option:selected').data('decimals')) || 0);
    $('#order-total .total').text($('select[name="currency_code"] option:selected').data('prefix') + order_total + $('select[name="currency_code"] option:selected').data('suffix'));
  }

  $('body').on('click keyup', 'input[name^="items"][name$="[price]"], input[name^="items"][name$="[tax]"], input[name^="items"][name$="[quantity]"], input[name^="order_total"][name$="[value]"], input[name^="order_total"][name$="[tax]"], input[name^="order_total"][name$="[calculate]"], #order-items a.remove, #order-total a.remove', function() {
    calculate_total();
  });
</script>