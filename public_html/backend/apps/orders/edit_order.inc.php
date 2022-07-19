<?php

  if (!empty($_GET['order_id'])) {
    $order = new ent_order($_GET['order_id']);
  } else {
    $order = new ent_order();
    $order->data['client_ip'] = $_SERVER['REMOTE_ADDR'];
    $order->data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $order->data['date_created'] = date('Y-m-d H:i:s');
  }

  if (!$_POST) {

    $_POST = $order->data;

  // Convert to local currency
    foreach (array_keys($_POST['items']) as $key) {
      $_POST['items'][$key]['price'] = $_POST['items'][$key]['price'] / $_POST['currency_value'];
      $_POST['items'][$key]['tax'] = $_POST['items'][$key]['tax'] / $_POST['currency_value'];
    }

    foreach (array_keys($_POST['order_total']) as $key) {
      $_POST['order_total'][$key]['amount'] = $_POST['order_total'][$key]['amount'] / $_POST['currency_value'];
      $_POST['order_total'][$key]['tax'] = $_POST['order_total'][$key]['tax'] / $_POST['currency_value'];
    }

    if (empty($order->data['id'])) {
      $_POST['customer']['country_code'] = settings::get('default_country_code');
    }
  }

  document::$snippets['title'][] = !empty($order->data['id']) ? language::translate('title_edit_order', 'Edit Order') .' #'. $order->data['id'] : language::translate('title_create_new_order', 'Create New Order');

  breadcrumbs::add(language::translate('title_orders', 'Orders'), document::ilink(__APP__.'/orders'));
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

// Create return order
  if (!empty($_POST['return'])) {

    try {

      if (empty($_POST['selected_items'])) throw new Exception(language::translate('error_must_select_items', 'You must select items'));

      $return_order = new ent_order();

      $fields = [
        'language_code',
        'currency_code',
        'currency_value',
        'display_prices_including_tax',
        'customer',
      ];

      foreach ($fields as $field) {
        $return_order->data[$field] = $order->data[$field];
      }

      foreach ($_POST['selected_items'] as $item_id) {
        $return_order->add_item(array_merge($order->data['items'][$item_id], ['quantity' => 0 - $order->data['items'][$item_id]['quantity']]));
      }

      $return_order->data['comments'] = [[
        'author' => 'system',
        'hidden' => true,
        'text' => 'Returned items from order '. $order->data['id'],
      ]];

      $return_order->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/edit_order', ['order_id' => $return_order->data['id']]));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Split order
  if (!empty($_POST['split'])) {

    try {

      if (empty($_POST['selected_items'])) throw new Exception(language::translate('error_must_select_items', 'You must select items'));

      $split_order = new ent_order();

      $split_order->previous['order_status_id'] = $order->data['order_status_id'];

      $fields = [
        'order_status_id',
        'reference',
        'language_code',
        'currency_code',
        'currency_value',
        'display_prices_including_tax',
        'customer',
        'shipping_option',
        'payment_option',
        'payment_transaction_id',
      ];

      foreach ($fields as $field) {
        $split_order->data[$field] = $order->data[$field];
      }

      foreach ($_POST['selected_items'] as $key) {
        $split_order->add_item($order->data['items'][$key]);
        unset($order->data['items'][$key]);
      }

      $split_order->shipping->selected = $order->shipping->selected;
      $split_order->payment->selected = $order->payment->selected;

      $split_order->data['comments'] = [[
        'author' => 'system',
        'hidden' => true,
        'text' => 'Split items from order '. $order->data['id'],
      ]];

      $split_order->save();
      $order->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Save data to database
  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['items'])) $_POST['items'] = [];
      if (empty($_POST['order_total'])) $_POST['order_total'] = [];
      if (empty($_POST['comments'])) $_POST['comments'] = [];

      if (!empty($_POST['items'])) {
        foreach (array_keys($_POST['items']) as $key) {
          $_POST['items'][$key]['price'] = $_POST['items'][$key]['price'] * $_POST['currency_value'];
          $_POST['items'][$key]['tax'] = $_POST['items'][$key]['tax'] * $_POST['currency_value'];
        }

        foreach (array_keys($_POST['order_total']) as $key) {
          if (empty($_POST['order_total'][$key]['calculate'])) $_POST['order_total'][$key]['calculate'] = false;
          $_POST['order_total'][$key]['amount'] = $_POST['order_total'][$key]['amount'] * $_POST['currency_value'];
          $_POST['order_total'][$key]['tax'] = $_POST['order_total'][$key]['tax'] * $_POST['currency_value'];
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
        'shipping_tracking_id',
        'shipping_tracking_url',
        'payment_transaction_id',
        'payment_receipt_url',
        'payment_terms',
        'display_prices_including_tax',
        'reference',
        'date_paid',
        'date_dispatched',
        'comments',
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

      $order->shipping->selected = $_POST['shipping_option'];
      $order->payment->selected = $_POST['payment_option'];

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
        $redirect_url = document::ilink(__APP__.'/orders');
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
        $_GET['redirect_url'] = document::ilink(__APP__.'/orders');
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

#box-comments {
  height: 1006px;
  background: var(--input-background-color);
  border-radius: var(--border-radius);
  padding: var(--gutter-size);
}
#box-comments .bubbles .private {
  position: absolute;
  top: 0.5em;
  right: 2.5em;
  cursor: pointer;
}
#box-comments .bubble {
  padding-top: 2em;
}
#box-comments .bubbles .notify  {
  position: absolute;
  top: 0.5em;
  right: 4em;
  cursor: pointer;
}
#box-comments .bubbles .private input[name$="[hidden]"],
#box-comments .bubbles .notify input[name$="[notify]"] {
  display: none;
}
#box-comments .bubbles .private input[name$="[hidden]"] + .fa,
#box-comments .bubbles .notify input[name$="[notify]"] + .fa {
  opacity: 0.25;
}
#box-comments .bubbles .private input[name$="[hidden]"]:checked + .fa,
#box-comments .bubbles .notify input[name$="[notify]"]:checked + .fa {
  opacity: 1;
}


#box-comments .bubbles .semi-transparent {
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
        <?php echo $app_icon; ?> <?php echo !empty($order->data['id']) ? language::translate('title_edit_order', 'Edit Order') .' #'. $order->data['no'] : language::translate('title_create_new_order', 'Create New Order'); ?>
      </div>
    </div>

    <div class="card-action">
      <ul class="list-inline">
        <li>
          <?php echo functions::form_draw_checkbox('send_order_copy', ['1', language::translate('text_send_order_copy_email', 'Send order copy email')], true); ?>
        </li>
        <li>
          <?php echo functions::form_draw_checkbox('unread', ['1', language::translate('title_mark_as_unread', 'Mark as unread')], false); ?>
        </li>
        <li>
          <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
          <?php echo (!empty($order->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
          <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        </li>
      </ul>
    </div>

    <div class="card-body">

      <div class="row">
        <div class="col-lg-9">

          <h2><?php echo language::translate('title_order_details', 'Order Details'); ?></h2>

          <div class="row">
            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_order_status', 'Order Status'); ?></label>
              <?php echo functions::form_draw_order_statuses_list('order_status_id', true); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_date', 'Date'); ?></label>
              <div class="form-input" readonly><?php echo language::strftime(language::$selected['format_datetime'], strtotime($order->data['date_created'])); ?></div>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_language', 'Language'); ?></label>
              <?php echo functions::form_draw_languages_list('language_code', true); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_tax_display', 'Tax Display'); ?></label>
              <div class="btn-group btn-block" data-toggle="buttons">
                <label class="btn btn-default<?php echo !empty($_POST['display_prices_including_tax']) ? ' active' : ''; ?>"><input type="radio" name="display_prices_including_tax" value="1"<?php echo !empty($_POST['display_prices_including_tax']) ? ' checked' : ''; ?> /><?php echo language::translate('title_incl_tax', 'Incl. Tax'); ?></label>
                <label class="btn btn-default<?php echo empty($_POST['display_prices_including_tax']) ? ' active' : ''; ?>"><input type="radio" name="display_prices_including_tax" value="0"<?php echo empty($_POST['display_prices_including_tax']) ? ' checked' : ''; ?> /><?php echo language::translate('title_excl_tax', 'Excl. Tax'); ?></label>
              </div>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_currency', 'Currency'); ?></label>
              <?php echo functions::form_draw_currencies_list('currency_code', true); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_currency_value', 'Currency Value'); ?></label>
              <?php echo functions::form_draw_decimal_field('currency_value', true, 6); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_ip_address', 'IP Address'); ?></label>
              <div class="input-group">
                <div class="form-input"><?php echo $order->data['client_ip']; ?></div>
                <a class="btn btn-default" href="https://ip-api.com/#<?php echo $order->data['client_ip']; ?>" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a>
              </div>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_reference', 'Reference'); ?></label>
              <?php echo functions::form_draw_text_field('reference', true); ?>
            </div>
          </div>

          <div id="customer-details">

            <div class="row" style="margin-bottom: 0;">
              <div class="col-md-6 customer-details">
                <h2><?php echo language::translate('title_billing_address', 'Billing Address'); ?></h2>

                <div class="form-group">
                  <div class="input-group">
                    <div class="selected-account form-input"><?php echo language::translate('title_id', 'ID'); ?>: <span class="id"><?php echo isset($_POST['customer']['id']) ? (int)$_POST['customer']['id'] : ''; ?></span> &ndash; <span class="name"><?php echo $account_name; ?></span> <a href="<?php echo document::href_ilink('customers/customer_picker'); ?>" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin-inline-start: 5px;"><?php echo language::translate('title_change', 'Change'); ?></a></div>
                    <?php echo functions::form_draw_hidden_field('customer[id]', true); ?>
                    <?php echo functions::form_draw_button('get_address', language::translate('title_get_address', 'Get Address'), 'button'); ?>
                  </div>
                </div>

                <div class="row">
                  <div class="form-group col-md-6">
                    <label><?php echo language::translate('title_company_name', 'Company Name'); ?></label>
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
                    <?php echo form_draw_zones_list('customer[zone_code]', fallback($_POST['customer']['country_code']), true); ?>
                  </div>
                </div>

                <div class="row">
                  <div class="form-group col-md-6">
                    <label><?php echo language::translate('title_phone_number', 'Phone Number'); ?></label>
                    <?php echo functions::form_draw_phone_field('customer[phone]', true); ?>
                  </div>

                  <div class="form-group col-md-6">
                    <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
                    <?php echo functions::form_draw_email_field('customer[email]', true, 'required'); ?>
                  </div>
                </div>
              </div>

              <div class="form-group col-md-6 shipping-address">
                <h2><?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></h2>

                <div class="form-group">
                  <?php echo functions::form_draw_button('copy_billing_address', language::translate('title_copy_billing_address', 'Copy Billing Address'), 'button', 'class="btn btn-default btn-block"'); ?>
                </div>

                <div class="row">
                  <div class="form-group col-md-6">
                    <label><?php echo language::translate('title_company_name', 'Company Name'); ?></label>
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
                    <?php echo form_draw_zones_list('customer[shipping_address][zone_code]', fallback($_POST['customer']['shipping_address']['country_code']), true); ?>
                  </div>
                </div>

                <div class="row">
                  <div class="form-group col-md-6">
                    <label><?php echo language::translate('title_phone_number', 'Phone Number'); ?></label>
                    <?php echo functions::form_draw_phone_field('customer[shipping_address][phone]', true); ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row" style="margin-bottom: 0;">
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

            <div class="col-md-6">
              <h2><?php echo language::translate('title_shipping_details', 'Shipping Details'); ?></h2>

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
                  <div class="form-input"><?php echo weight::format($order->data['weight_total'], $order->data['weight_unit']) ?></div>
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

          <div id="box-comments" class="form-input" style="margin-bottom: 0;">
            <div class="bubbles">
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
                <?php echo functions::form_draw_hidden_field('comments['.$key.'][id]', true); ?>
                <?php echo functions::form_draw_hidden_field('comments['.$key.'][order_id]', true); ?>
                <?php echo functions::form_draw_hidden_field('comments['.$key.'][author]', true); ?>
                <?php echo functions::form_draw_hidden_field('comments['.$key.'][text]', true); ?>

                <?php echo nl2br($_POST['comments'][$key]['text']); ?>

                <div class="date"><?php echo language::strftime(language::$selected['format_datetime'], strtotime($_POST['comments'][$key]['date_created'])); ?></div>

                <div class="actions">
                  <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle'); ?></a>
                  <label class="private" title="<?php echo functions::escape_html(language::translate('title_hidden', 'Hidden')); ?>"><?php echo functions::form_draw_checkbox('comments['.$key .'][hidden]', '1', true); ?> <?php echo functions::draw_fonticon('fa-eye-slash'); ?></label>
                </div>
              </div>
              <?php } ?>

              <div class="add text-end"><button class="btn btn-default" type="button" title="<?php echo language::translate('title_add', 'Add'); ?>"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #6c6;"'); ?> <?php echo language::translate('title_add_comment', 'Add Comment'); ?></button></div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="order-items" class="card card-default">
    <div class="card-body">
      <h2><?php echo language::translate('title_order_items', 'Order Items'); ?></h2>
    </div>

    <table class="table table-striped table-hover table-input table-dragable data-table">
      <thead>
        <tr>
          <th style="width: 50px;"><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
          <th><?php echo language::translate('title_item', 'Item'); ?></th>
          <th><?php echo language::translate('title_sku', 'SKU'); ?></th>
          <th style="width: 125px;" class="text-center"><?php echo language::translate('title_qty', 'Qty'); ?></th>
          <th style="width: 125px;" class="text-center"><?php echo language::translate('title_in_stock', 'In Stock'); ?></th>
          <th style="width: 185px;" class="text-center"><?php echo language::translate('title_unit_price', 'Unit Price'); ?></th>
          <th style="width: 185px;" class="text-center"><?php echo language::translate('title_discount', 'Discount'); ?></th>
          <th style="width: 150px;" class="text-center"><?php echo language::translate('title_tax_rate', 'Tax Rate'); ?></th>
          <th style="width: 185px;" class="text-center"><?php echo language::translate('title_sum', 'Sum'); ?></th>
          <th style="width: 185px;" class="text-center"><?php echo language::translate('title_tax', 'Tax'); ?></th>
          <th style="width: 50px;"></th>
          <th style="width: 50px;"></th>
        </tr>
      </thead>

      <tbody>
        <?php if (!empty($_POST['items'])) foreach (array_keys($_POST['items']) as $key) { ?>
        <tr class="item">
          <td><?php echo functions::form_draw_checkbox('selected_items[]', $key, true); ?></td>
          <td class="grabable">
            <?php echo !empty($_POST['items'][$key]['product_id']) ? '<a href="'. document::href_ilink('f:product', ['product_id' => $_POST['items'][$key]['product_id']]) .'" target="_blank">'. $_POST['items'][$key]['name'] .'</a>' : $_POST['items'][$key]['name']; ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][id]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][product_id]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][stock_item_id]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][name]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][data]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][sku]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][gtin]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][taric]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][weight]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][weight_unit]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][length]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][width]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][height]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][length_unit]', true); ?>
            <?php echo functions::form_draw_hidden_field('items['.$key.'][tax_class_id]', true); ?>
          </td>
          <td class="grabable sku"><?php echo functions::escape_html($_POST['items'][$key]['sku']); ?></td>
          <td><?php echo functions::form_draw_decimal_field('items['.$key.'][quantity]', true, 2); ?></td>
          <td class="text-center"><?php if (isset($_POST['items'][$key]['sufficient_stock'])) echo $item['sufficient_stock'] ? '<span style="color: #88cc44;">'. functions::draw_fonticon('fa-check') .' '. $item['stock_quantity'] .'</span>' : '<span style="color: #ff6644;">'. functions::draw_fonticon('fa-times') .' '. $item['stock_quantity'] .'</span>'; ?></td>
          <td><?php echo functions::form_draw_currency_field('items['.$key.'][price]', $_POST['currency_code'], true); ?></td>
          <td><?php echo functions::form_draw_currency_field('items['.$key.'][discount]', $_POST['currency_code'], true); ?></td>
          <td>
            <div class="input-group">
              <?php echo functions::form_draw_decimal_field('items['.$key.'][tax_rate]', true, 2); ?>
              <span class="input-group-text">%</span>
            </div>
          </td>
          <td><?php echo functions::form_draw_currency_field('items['.$key.'][sum]', $_POST['currency_code'], true, 'readonly'); ?></td>
          <td><?php echo functions::form_draw_currency_field('items['.$key.'][tax]', $_POST['currency_code'], true, 'readonly'); ?></td>
          <td class="text-end">
            <a class="btn btn-default btn-sm remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg fa-fw', 'style="color: #c33;"'); ?></a>
          </td>
          <td class="text-end">
            <a class="btn btn-default btn-sm edit" href="#" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a>
          </td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="12">
            <a class="btn btn-default add-product" href="<?php echo document::href_ilink('catalog/product_picker'); ?>" data-toggle="lightbox" data-callback="selectProduct"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #6c6;"'); ?> <?php echo language::translate('title_add_product', 'Add Product'); ?></a>
            <div class="btn btn-default add-custom-item"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #6c6;"'); ?> <?php echo language::translate('title_add_custom_item', 'Add Custom Item'); ?></div>
            <?php echo functions::form_draw_button('return', language::translate('title_return_items', 'Return Items'), 'submit', 'formnovalidate onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"'); ?>
            <?php echo functions::form_draw_button('split', language::translate('title_split_order', 'Split Order'), 'submit', 'formnovalidate onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"'); ?>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div id="order-total" class="card card-default">
    <div class="card-body">
      <h2><?php echo language::translate('title_order_total', 'Order Total'); ?></h2>
    </div>

    <table class="table table-striped data-table">
      <thead>
        <tr>
          <th style="width: 30px;"></th>
          <th style="width: 250px;"><?php echo language::translate('title_module_id', 'Module ID'); ?></th>
          <th class="text-end"><?php echo language::translate('title_title', 'Title'); ?></th>
          <th class="text-center" style="width: 250px;"><?php echo language::translate('title_amount', 'Amount'); ?></th>
          <th class="text-center" style="width: 250px;"><?php echo language::translate('title_tax', 'Tax'); ?></th>
          <th style="width: 30px;"></th>
        </tr>
      </thead>

      <tbody>
        <tr>
          <td colspan="3" class="text-end"><?php echo language::translate('title_subtotal'); ?></td>
          <td class="text-end"><?php echo functions::form_draw_currency_field('subtotal', $_POST['currency_code'], true, 'readonly'); ?></td>
          <td class="text-end"><?php echo functions::form_draw_currency_field('subtotal_tax', $_POST['currency_code'], true, 'readonly'); ?></td>
          <td></td>
        </tr>

        <?php foreach (array_keys($_POST['order_total']) as $key) { ?>
        <tr>
          <td class="text-end"><a href="#" class="btn btn-default add" title="<?php echo language::translate('text_insert_before', 'Insert before'); ?>"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #6c6;"'); ?></a></td>
          <td class="text-end"><?php echo functions::form_draw_hidden_field('order_total['. $key .'][id]', true) . functions::form_draw_text_field('order_total['. $key .'][module_id]', true); ?></td>
          <td><?php echo functions::form_draw_text_field('order_total['. $key .'][title]', true, 'class="form-input text-end"'); ?></td>
          <td>
            <div class="input-group">
            <span class="input-group-text"><?php echo functions::form_draw_checkbox('order_total['. $key .'][calculate]', ['1', functions::draw_fonticon('fa-calculator')], true, 'title="'. functions::escape_html(language::translate('title_calculate', 'Calculate')) .'"'); ?></span>
            <?php echo functions::form_draw_currency_field('order_total['. $key .'][value]', $_POST['currency_code'], true); ?>
            </div>
          </td>
          <td><?php echo functions::form_draw_currency_field('order_total['. $key .'][tax]', $_POST['currency_code'], true); ?></td>
          <td><a class="btn btn-default btn-sm remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
        </tr>
        <?php } ?>

        <tr>
          <td colspan="3" class="text-end"><?php echo language::translate('title_payment_fee', 'Payment Fee'); ?></td>
          <td><?php echo functions::form_draw_currency_field('payment_option[fee]', $_POST['currency_code'], true); ?></td>
          <td><?php echo functions::form_draw_currency_field('payment_option[tax]', $_POST['currency_code'], true, 'readonly'); ?></td>
          <td></td>
        </tr>

        <?php foreach (array_keys($_POST['order_total']) as $key) { ?>
        <tr>
          <td class="text-end"><a href="#" class="btn btn-default add" title="<?php echo language::translate('text_insert_before', 'Insert before'); ?>"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #6c6;"'); ?></a></td>
          <td class="text-end"><?php echo functions::form_draw_hidden_field('order_total['.$key.'][id]', true) . functions::form_draw_text_field('order_total['.$key.'][module_id]', true); ?></td>
          <td><?php echo functions::form_draw_text_field('order_total['.$key.'][title]', true, 'class="form-input text-end"'); ?></td>
          <td>
            <div class="input-group">
            <span class="input-group-text"><?php echo functions::form_draw_checkbox('order_total['.$key.'][calculate]', ['1', functions::draw_fonticon('fa-calculator')], true, 'title="'. htmlspecialchars(language::translate('title_calculate', 'Calculate')) .'"'); ?></span>
            <?php echo functions::form_draw_currency_field('order_total['.$key.'][value]', $_POST['currency_code'], true); ?>
            </div>
          </td>
          <td><?php echo functions::form_draw_currency_field('order_total['.$key.'][tax]', $_POST['currency_code'], true); ?></td>
          <td><a class="btn btn-default btn-sm remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
        </tr>
        <?php } ?>
        <tr>
          <td colspan="3" class="text-end"><?php echo language::translate('title_discount_received', 'Discount Received'); ?></td>
          <td><?php echo functions::form_draw_currency_field('discount', $_POST['currency_code'], true, 'readonly'); ?></td>
          <td><?php echo functions::form_draw_currency_field('discount_tax', $_POST['currency_code'], true, 'readonly'); ?></td>
          <td></td>
        </tr>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="2"><a class="btn btn-default add" href="#"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #6c6;"'); ?> <?php echo language::translate('title_add_row', 'Add Row'); ?></a></td>
          <td colspan="3" class="text-end" style="font-size: 1.5em;"><?php echo language::translate('title_grand_total', 'Grand Total'); ?>: <strong class="total"><?php echo currency::format($order->data['total'], false, $_POST['currency_code'], $_POST['currency_value']); ?></strong></td>
          <td></td>
        </tr>
        <tr>
          <td colspan="5" class="text-end"><?php echo language::translate('title_whereof_tax', 'Whereof Tax'); ?>: <strong class="total-tax"><?php echo currency::format($order->data['total_tax'], false, $_POST['currency_code'], $_POST['currency_value']); ?></strong></td>
          <td></td>
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
          <?php echo functions::form_draw_decimal_field('weight', true, 3, 'min="0"'); ?>
          <?php echo functions::form_draw_weight_units_list('weight_unit', true); ?>
        </div>
      </div>

      <div class="form-group col-md-8">
        <label><?php echo language::translate('title_dimensions', 'Dimensions'); ?></label>
        <div class="input-group">
          <?php echo functions::form_draw_decimal_field('length', true, 3, 'min="0"'); ?>
          <span class="input-group-text">x</span>
          <?php echo functions::form_draw_decimal_field('width', true, 3, 'min="0"'); ?>
          <span class="input-group-text">x</span>
          <?php echo functions::form_draw_decimal_field('height', true, 3, 'min="0"'); ?>
          <?php echo functions::form_draw_length_units_list('length_unit', true); ?>
        </div>
      </div>
    </div>

    <div class="row">
        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
        <?php echo functions::form_draw_decimal_field('quantity', ''); ?>
      </div>

        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_price', 'Price'); ?></label>
        <?php echo functions::form_draw_currency_field('price', $_POST['currency_code'], ''); ?>
      </div>

      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_tax', 'Tax'); ?></label>
        <?php echo functions::form_draw_currency_field('tax', $_POST['currency_code'], ''); ?>
      </div>

      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_tax_class', 'Tax Class'); ?></label>
        <?php echo functions::form_draw_tax_classes_list('tax_class_id', ''); ?>
      </div>
    </div>

    <div class="card-action">
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
          <?php echo functions::form_draw_decimal_field('weight', '', 3, 'min="0"'); ?>
          <?php echo functions::form_draw_weight_units_list('weight_unit', ''); ?>
        </div>
      </div>

      <div class="form-group col-md-8">
        <label><?php echo language::translate('title_dimensions', 'Dimensions'); ?></label>
        <div class="input-group">
          <?php echo functions::form_draw_decimal_field('length', '', 3, 'min="0"'); ?>
          <span class="input-group-text">x</span>
          <?php echo functions::form_draw_decimal_field('width', '', 3, 'min="0"'); ?>
          <span class="input-group-text">x</span>
          <?php echo functions::form_draw_decimal_field('height', '', 3, 'min="0"'); ?>
          <?php echo functions::form_draw_length_units_list('length_unit', ''); ?>
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
        <?php echo functions::form_draw_currency_field('price', $_POST['currency_code'], ''); ?>
      </div>

      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_tax', 'Tax'); ?></label>
        <?php echo functions::form_draw_currency_field('tax', $_POST['currency_code'], ''); ?>
      </div>
    </div>

    <div class="btn-group">
      <?php echo functions::form_draw_button('ok', language::translate('title_ok', 'OK'), 'button', '', 'ok'); ?>
      <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.featherlight.close();"', 'cancel'); ?>
    </div>
  </div>
</div>

<script>

// Order

  $('select[name="order_status_id"]').change(function(e){
    var color = $(this).find('option:selected').data('color');
    $(this).css('box-shadow', color ? '0 0 0px 2px'+ color +'cc' : '');
  }).trigger('change');

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
              if ($(':input[name="customer[shipping_address]['+key+']"]').length) $(':input[name="customer[shipping_address]['+key+']"]').val(value).trigger('change');
            });
          } else {
            if ($(':input[name="customer['+key+']"]').length) $(':input[name="customer['+key+']"]').val(value).trigger('change');
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
        $('select[name="customer[zone_code]"]').trigger('change');
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
        $('select[name="customer[zone_code]"]').trigger('change');
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

    $('.add-product').attr('href', '<?php echo document::ilink('catalog/product_picker'); ?>?'+ $.param(params));
  });

  $(':input[name^="customer"]').first().trigger('input');

// Comments

  $('#box-comments').on('input', 'textarea[name^="comments"][name$="[text]"]', function(){
    $(this).height('auto').height('calc(' + $(this).prop('scrollHeight') + 'px + 1em) ');
  }).trigger('input');

  var new_comment_index = 0;
  $('#box-comments .add').click(function(e) {

    e.preventDefault();
    while ($('input[name="comments[new_'+new_comment_index+'][id]"]').length) new_comment_index++;
    var output = '  <div class="bubble local me">'
               + '    <?php echo functions::form_draw_hidden_field('comments[new_comment_index][id]', ''); ?>'
               + '    <?php echo functions::form_draw_hidden_field('comments[new_comment_index][author]', 'staff'); ?>'
               + '    <?php echo functions::form_draw_hidden_field('comments[new_comment_index][date_created]', language::strftime(language::$selected['format_datetime'])); ?>'
               + '    <?php echo functions::escape_js(functions::form_draw_textarea('comments[new_comment_index][text]', '')); ?>'
               + '    <div class="date"><?php echo language::strftime(language::$selected['format_datetime']); ?></div>'
               + '    <div class="actions">'
               + '      <label class="notify" title="<?php echo functions::escape_html(language::translate('title_notify', 'Notify')); ?>"><?php echo functions::escape_js(functions::form_draw_checkbox('comments[new_comment_index][notify]', [1, functions::draw_fonticon('fa-envelope')], true)); ?> </label>'
               + '      <label class="private" title="<?php echo functions::escape_html(language::translate('title_hidden', 'Hidden')); ?>"><?php echo functions::escape_js(functions::form_draw_checkbox('comments[new_comment_index][hidden]', [1, functions::draw_fonticon('fa-eye-slash')], true)); ?></label>'
               + '      <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg fa-fw'); ?></a>'
               + '    </div>'
               + '  </div>';
    output = output.replace(/new_comment_index/g, 'new_' + new_comment_index);
    $(this).before(output);
    $(this).closest('#box-comments .bubbles textarea:last-child').focus();
  });

  $('#box-comments').on('click', ':input[name$="[hidden]"]', function(e) {
    $(this).closest('.bubble').find(':input[name$="[notify]"]').prop('checked', false).trigger('change');
  });

  $('#box-comments').on('click', ':input[name$="[notify]"]', function(e) {
    $(this).closest('.bubble').find(':input[name$="[hidden]"]').prop('checked', false).trigger('change');
  });

  $('#box-comments').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('.bubble').remove();
  });

  $('#box-comments .bubbles').on('change', 'input[name^="comments"][name$="[hidden]"]', function(e) {
    if ($(this).is(':checked')) {
      $(this).closest('.bubble').addClass('semi-transparent');
    } else {
      $(this).closest('.bubble').removeClass('semi-transparent');
    }
  });

// Tax Rates

  var tax_rates = [];

  function get_tax(tax_class_id) {
    $.each(tax_rates, function(i, tax_rate) {
      if (tax_class_id == i) return tax_rate;
    });
  }

  $('#customer-details').on('input', function(){
    $.ajax({
      url: '<?php echo document::ilink('tax/tax_rates.json'); ?>?' + $(':input[name^="customer\["]').serialize(),
      type: 'get',
      cache: true,
      async: false,
      dataType: 'json',
      success: function(data) {
        tax_rates = [];
        $.each(data, function(i, tax_rate) {
          tax_rates[tax_rate.tax_class_id] = tax_rate.rate;
        });
      },
    });
  });

// Order items

  $('#order-items').on('input change', ':input[name$="[quantity]"], :input[name$="[price]"], :input[name$="[tax_rate]"], :input[name$="[discount]"]', function(){
    var row = $(this).closest('tr'),
      quantity = parseFloat($(row).find(':input[name$="[quantity]"]').val() || 0),
      price = parseFloat($(row).find(':input[name$="[price]"]').val() || 0),
      tax_rate = parseFloat($(row).find(':input[name$="[tax_rate]"]').val() || 0),
      discount = parseFloat($(row).find(':input[name$="[discount]"]').val() || 0),
      sum = quantity * (price - discount),
      sum_tax = (sum * tax_rate / 100),
      decimals = $('select[name="currency_code"] option:selected').data('decimals') || 0;

    $(row).find(':input[name$="[sum]"]').val(sum.toFixed(decimals)).trigger('change');
    $(row).find(':input[name$="[tax]"]').val(sum_tax.toFixed(decimals)).trigger('change');
  });

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

  // Callback handler for product picker modal
  var selectProduct = function(product) {

    var params = {
      product_id: product.id,
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

    var url = '<?php echo document::ilink(__APP__.'/add_product'); ?>?' + $.param(params);

    $.get(url, function(content) {
      $('.featherlight-modal').html(content);
    }, 'html');
  }

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
      'weight_unit',
      'length',
      'width',
      'height',
      'length_unit',
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

    $(modal).find(':input[name="weight_unit"]').val('<?php echo settings::get('store_weight_unit'); ?>');
    $(modal).find(':input[name="length_unit"]').val('<?php echo settings::get('store_length_unit'); ?>');

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
      'weight_unit',
      'length',
      'width',
      'height',
      'length_unit',
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
               + '    <td></td>'
               + '    <td class="grabable">' + item.name
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][id]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][product_id]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][stock_item_id]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][name]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][description]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][data]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][sku]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][gtin]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][taric]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][weight]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][weight_unit]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][length]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][width]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][height]', '')); ?>'
               + '      <?php echo functions::escape_js(functions::form_draw_hidden_field('items[new_item_index][length_unit]', '')); ?>'
               + '    </td>'
               + '    <td class="grabable sku">'+ item.sku +'</td>'
               + '    <td class="grabable">'
               + '      <span class="weight"></span> <span class="weight_unit"></span>'
               + '    </td>'
               + '    <td class="grabable">'
               + '      <span class="length"></span> x <span class="width"></span> x <span class="height"></span> <span class="length_unit"></span>'
               + '    </td>'
               + '    <td><?php echo functions::escape_js(functions::form_draw_decimal_field('items[new_item_index][quantity]', '', 2)); ?></td>'
               + '    <td><?php echo functions::escape_js(functions::form_draw_currency_field('items[new_item_index][price]', $_POST['currency_code'], '')); ?></td>'
               + '    <td><?php echo functions::escape_js(functions::form_draw_currency_field('items[new_item_index][discount]', $_POST['currency_code'], '')); ?></td>'
               + '    <td><?php echo functions::escape_js(functions::form_draw_currency_field('items[new_item_index][sum]', $_POST['currency_code'], '', 'readonly')); ?></td>'
               + '    <td><?php echo functions::escape_js(functions::form_draw_currency_field('items[new_item_index][tax]', $_POST['currency_code'], '', 'readonly')); ?></td>'
               + '    <td class="text-end">'
               + '      <a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-times-circle fa-lg fa-fw', 'style="color: #c33;"')); ?></a>'
               + '    </td>'
               + '    <td class="text-end">'
               + '      <a class="btn btn-default btn-sm edit" href="#" title="<?php echo functions::escape_js(language::translate('title_edit', 'Edit'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('edit')); ?></a>'
               + '    </td>'
               + '  </tr>';

    output = output.replace(/new_item_index/g, 'new_' + new_item_index);
    $('#order-items tbody').append(output);

    var row = $('#order-items tbody tr.item').last();
    $(row).find('*[name$="[product_id]"]').val(item.product_id);
    $(row).find('*[name$="[stock_item_id]"]').val(item.stock_item_id);
    $(row).find('*[name$="[sku]"]').val(item.sku);
    $(row).find('*[name$="[name]"]').val(item.name);
    $(row).find('*[name$="[gtin]"]').val(item.gtin);
    $(row).find('*[name$="[taric]"]').val(item.taric);
    $(row).find('*[name$="[weight]"]').val(item.weight);
    $(row).find('*[name$="[weight_unit]"]').val(item.weight_unit);
    $(row).find('*[name$="[length]"]').val(item.length);
    $(row).find('*[name$="[width]"]').val(item.width);
    $(row).find('*[name$="[height]"]').val(item.height);
    $(row).find('*[name$="[length_unit]"]').val(item.length_unit);
    $(row).find('*[name$="[quantity]"]').val(item.quantity);
    $(row).find('*[name$="[price]"]').val(item.price);
    $(row).find('*[name$="[tax]"]').val(item.tax);
    $(row).find('*[name$="[tax_rate]"]').val(item.tax_rate);
    $(row).find('*[name$="[tax_class_id]"]').val(item.tax_class_id);

    $(row).find('[data-type="currency"]').parent().find('.input-group-text').text($(':input[name="currency_code"]').val());
    $(row).find('.weight').text(String(item.weight).trim('.0'));
    $(row).find('.weight_unit').text(item.weight_unit);
    $(row).find('.length').text(String(item.length).trim('.0'));
    $(row).find('.width').text(String(item.width).trim('.0'));
    $(row).find('.height').text(String(item.height).trim('.0'));
    $(row).find('.length_unit').text(item.length_unit);

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
               + '    <td class="text-end"><a href="#" class="btn btn-default add" title="<?php echo functions::escape_js(language::translate('text_insert_before', 'Insert before'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-plus', 'style="color: #6c6;"')); ?></a></td>'
               + '    <td><?php echo functions::escape_js(functions::form_draw_hidden_field('order_total[new_ot_row_index][id]', '')); ?><?php echo functions::escape_js(functions::form_draw_text_field('order_total[new_ot_row_index][module_id]', '')); ?></td>'
               + '    <td><?php echo functions::escape_js(functions::form_draw_text_field('order_total[new_ot_row_index][title]', '', 'class="form-input text-end"')); ?></td>'
               + '    <td>'
               + '      <div class="input-group">'
               + '        <span class="input-group-text"><?php echo functions::escape_js(functions::form_draw_checkbox('order_total[new_ot_row_index][calculate]', ['1', functions::draw_fonticon('fa-calculator')], '1', 'title="'. functions::escape_html(language::translate('title_calculate', 'Calculate')) .'"')); ?></span>'
               + '        <?php echo functions::escape_js(functions::form_draw_currency_field('order_total[new_ot_row_index][value]', $_POST['currency_code'], currency::format_raw(0))); ?>'
               + '      </div>'
               + '    </td>'
               + '    <td><?php echo functions::escape_js(functions::form_draw_currency_field('order_total[new_ot_row_index][tax]', $_POST['currency_code'], currency::format_raw(0))); ?></td>'
               + '    <td><a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-times-circle fa-lg fa-fw', 'style="color: #c33;"')); ?></a></td>'
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

    var subtotal = 0,
     subtotal_tax = 0,
     total = 0;
     total_tax = 0,
     decimals = $('select[name="currency_code"] option:selected').data('decimals') || 0,
     prefix = $('select[name="currency_code"] option:selected').data('prefix'),
     suffix = $('select[name="currency_code"] option:selected').data('suffix');

    $('#order-items tbody tr').each(function() {
      subtotal += parseFloat($(this).find(':input[name$="[sum]"]').val() || 0);
      subtotal_tax += parseFloat($(this).find(':input[name$="[tax]"]').val() || 0);
    });

    total += subtotal;
    total_tax += subtotal_tax;

    $('#order-total tbody tr').each(function() {
      var row = this,
        amount = parseFloat($(row).find(':input[name$="[amount]"]').val() || 0),
        tax_rate = parseFloat($(row).find(':input[name$="[tax_rate]"]').val() || 0),
        discount = parseFloat($(row).find(':input[name$="[discount]"]').val() || 0),
        tax = (amount - discount) * tax_rate / 100;

      if (!$(row).find(':input[name$="[calculate]"]:checked').length) return;

      total += amount - discount;
      total_tax += tax;
    });

    $('#order-total :input[name="subtotal"]').val(subtotal.toFixed(decimals)).trigger('change');
    $('#order-total :input[name="subtotal_tax"]').val(subtotal_tax.toFixed(decimals)).trigger('change');
    $('#order-total .total').text(prefix + total.toFixed(decimals) + suffix);
    $('#order-total .total-tax').text(prefix + total_tax.toFixed(decimals) + suffix);
  }

  $('body').on('input change', '#order-items :input[name$="[sum]"], #order-items :input[name$="[tax]"], #order-items a.remove, #order-total :input[name$="[amount]"], #order-total :input[name$="[tax]"], #order-total [name$="[calculate]"], #order-total a.remove', function() {
    calculate_total();
  });

</script>
