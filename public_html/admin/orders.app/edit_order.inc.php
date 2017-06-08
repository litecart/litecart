<?php

  if (!empty($_GET['order_id'])) {
    $order = new ctrl_order($_GET['order_id']);
  } else {
    $order = new ctrl_order();
  }

  if (empty($_POST)) {

    foreach ($order->data as $key => $value) {
      $_POST[$key] = $value;
    }

  // Convert to local currency
    foreach (array_keys($_POST['items']) as $key) {
      $_POST['items'][$key]['price'] = $_POST['items'][$key]['price'] * $_POST['currency_value'];
      $_POST['items'][$key]['tax'] = $_POST['items'][$key]['tax'] * $_POST['currency_value'];
    }
    foreach (array_keys($_POST['order_total']) as $key) {
      $_POST['order_total'][$key]['value'] = $_POST['order_total'][$key]['value'] * $_POST['currency_value'];
      $_POST['order_total'][$key]['tax'] = $_POST['order_total'][$key]['tax'] * $_POST['currency_value'];
    }

    if (empty($_POST['customer']['country_code'])) $_POST['customer']['country_code'] = settings::get('default_country_code');
  }

  breadcrumbs::add(!empty($order->data['id']) ? language::translate('title_edit_order', 'Edit Order') .' #'. $order->data['id'] : language::translate('title_create_new_order', 'Create New Order'));

// Save data to database
  if (isset($_POST['save'])) {

    if (empty($_POST['items'])) $_POST['items'] = array();
    if (empty($_POST['order_total'])) $_POST['order_total'] = array();
    if (empty($_POST['comments'])) $_POST['comments'] = array();

    if (empty(notices::$data['errors'])) {

      if (!empty($_POST['items'])) {
        foreach (array_keys($_POST['items']) as $key) {
          $_POST['items'][$key]['price'] = (float)$_POST['items'][$key]['price'] / (float)$_POST['currency_value'];
          $_POST['items'][$key]['tax'] = (float)$_POST['items'][$key]['tax'] / (float)$_POST['currency_value'];
        }

        foreach (array_keys($_POST['order_total']) as $key) {
          if (empty($_POST['order_total'][$key]['calculate'])) $_POST['order_total'][$key]['calculate'] = false;
          $_POST['order_total'][$key]['value'] = (float)$_POST['order_total'][$key]['value'] / (float)$_POST['currency_value'];
          $_POST['order_total'][$key]['tax'] = (float)$_POST['order_total'][$key]['tax'] / (float)$_POST['currency_value'];
        }
      }

      $fields = array(
        'language_code',
        'currency_code',
        'currency_value',
        'items',
        'order_total',
        'order_status_id',
        'shipping_option',
        'shipping_tracking_id',
        'payment_option',
        'payment_transaction_id',
        'comments',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $order->data[$field] = $_POST[$field];
      }

      $fields = array(
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
      );

      foreach ($fields as $field) {
        if (isset($_POST['customer'][$field])) $order->data['customer'][$field] = $_POST['customer'][$field];
      }

      $fields = array(
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
      );

      foreach ($fields as $field) {
        if (isset($_POST['customer']['shipping_address'][$field])) $order->data['customer']['shipping_address'][$field] = $_POST['customer']['shipping_address'][$field];
      }

      $order->save();

    // Send e-mails
      if (!empty($_POST['email_order_copy'])) {
        $order->email_order_copy($order->data['customer']['email']);
        foreach (explode(';', settings::get('email_order_copy')) as $email) {
          $order->email_order_copy($email, settings::get('store_language_code'));
        }
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. (!empty($_GET['redirect']) ? $_GET['redirect'] : document::link('', array('app' => $_GET['app'], 'doc' => 'orders'))));
      exit;
    }
  }

  // Delete from database
  if (isset($_POST['delete']) && !empty($order->data['id'])) {
    $order->delete();
    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. (!empty($_GET['redirect']) ? $_GET['redirect'] : document::link('', array('app' => $_GET['app'], 'doc' => 'orders'))));
    exit;
  }

  functions::draw_lightbox();

  $account_name = '('. language::translate('title_guest', 'Guest') .')';
  if (!empty($_POST['customer']['id'])) {
    $customer = reference::customer((int)$_POST['customer']['id']);
    $account_name = $customer->company ? $customer->company : $customer->firstname .' '. $customer->lastname;
  }
?>
<style>
#comments {
  overflow-y: auto;
  border: 1px #ddd dashed;
  padding: 2em;
  background: #fcfcfc;
  border-radius: 0.5em;
}
@media screen and (min-width: 1200px) {
  #comments {
    height: 900px;
  }
}
#comments .comment {
  position: relative;
  margin-bottom: 1em;
  padding: 0.5em 1em;
  border-radius: 1em;
  box-sizing: border-box;
  min-height: 4em;
}
#comments .comment.system {
  margin-left: 10%;
  margin-right: 10%;
  background: #e5e5ea;
}

#comments .comment.customer {
  margin-right: 20%;
  background: #e5e5ea;
}
#comments .comment.customer:after {
  content: "";
  position: absolute;
  left: -0.5em;
  right: initial;
  bottom: 0;
  width: 1em;
  height: 1em;
  border-right: 0.5em solid #e5e5ea;
  border-bottom-right-radius: 1em 0.5em;
}

#comments .comment.staff {
  margin-left: 20%;
  background-color: #4096ee;
  color: white;
}
#comments .comment.staff:after {
  content: "";
  position: absolute;
  left: initial;
  right: -0.5em;
  bottom: 0;
  border-right: none;
  border-bottom-right-radius: 0;
  border-left: 0.5em solid #4096ee;
  border-bottom-left-radius: 1em 0.5em;
  width: 1em;
  height: 1em;
}

#comments .text {
  margin-right: 2em;
}

#comments .date {
  padding-top: 0.5em;
  font-size: 0.8em;
  text-align: center;
  opacity: 0.5;
}

#comments .remove {
  position: absolute;
  top: 0.5em;
  right: 0.5em;
  color: inherit;
}

#comments .notify  {
  position: absolute;
  top: 0.5em;
  right: 3.25em;
  cursor: pointer;
}
#comments .notify input[name$="[notify]"] {
  display: none;
}
#comments .notify input[name$="[notify]"] + .fa {
  opacity: 0.25;
}
#comments .notify input[name$="[notify]"]:checked + .fa {
  opacity: 1;
}

#comments .private  {
  position: absolute;
  top: 0.5em;
  right: 1.75em;
  cursor: pointer;
}
#comments .private input[name$="[hidden]"] {
  display: none;
}
#comments .private input[name$="[hidden]"] + .fa {
  opacity: 0.25;
}
#comments .private input[name$="[hidden]"]:checked + .fa {
  opacity: 1;
}

#comments .semi-transparent {
  opacity: 0.5;
}

#comments textarea {
  margin-right: 2em;
  height: 4em;
  box-sizing: border-box;
  color: inherit;
  background: transparent;
  border: none;
  padding: none;
  outline: none;
  box-shadow: none;
}

#modal-customer-picker tbody tr {
  cursor: pointer;
}
</style>

<h1><?php echo $app_icon; ?> <?php echo !empty($order->data['id']) ? language::translate('title_edit_order', 'Edit Order') .' #'. $order->data['id'] : language::translate('title_create_new_order', 'Create New Order'); ?></h1>

<?php echo functions::form_draw_form_begin('form_order', 'post'); ?>

  <div class="row">
    <div class="col-lg-8">

      <h2><?php echo language::translate('title_order_information', 'Order Information'); ?></h2>

      <div class="row">

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
          <?php echo functions::form_draw_decimal_field('currency_value', true, 4); ?>
        </div>
      </div>

      <div id="customer-details" class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><?php echo language::translate('title_customer_information', 'Customer Information'); ?></h2>
        </div>

        <div class="panel-body">
          <div class="row">
            <div class="col-md-6 customer-details">
              <h3><?php echo language::translate('title_billing_address', 'Billing Address'); ?></h3>

              <div class="row">
                <div class="form-group col-md">
                  <div class="input-group">
                    <?php echo functions::form_draw_hidden_field('customer[id]', true); ?>
                    <div class="selected-account form-control disabled"><?php echo language::translate('title_id', 'ID'); ?>: <span class="id"><?php echo @(int)$_POST['customer']['id']; ?></span> &mdash; <span class="name"><?php echo $account_name; ?></span> <a href="#modal-customer-picker" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin-left: 5px;"><?php echo language::translate('title_change', 'Change'); ?></a></div>
                    <span class="input-group-btn">
                      <?php echo functions::form_draw_button('get_address', language::translate('title_get_address', 'Get Address'), 'button'); ?>
                    </span>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_tax_id', 'Tax ID'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[tax_id]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_company', 'Company'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[company]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_firstname', 'Firstname'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[firstname]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_lastname', 'Lastname'); ?></label>
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
                  <label><?php echo language::translate('title_postcode', 'Postcode'); ?></label>
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
                  <label><?php echo language::translate('title_email', 'Email'); ?></label>
                  <?php echo functions::form_draw_email_field('customer[email]', true, 'required="required"'); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_phone', 'Phone'); ?></label>
                  <?php echo functions::form_draw_phone_field('customer[phone]', true); ?>
                </div>
              </div>
            </div>

            <div class="form-group col-md-6 shipping-address">
              <h3><?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></h3>

              <div class="row">
                <div class="form-group col-md">
                  <?php echo functions::form_draw_button('copy_billing_address', language::translate('title_copy_billing_address', 'Copy Billing Address'), 'button', 'class="btn btn-default btn-block"'); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_company', 'Company'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[shipping_address][company]', true); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_firstname', 'Firstname'); ?></label>
                  <?php echo functions::form_draw_text_field('customer[shipping_address][firstname]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_lastname', 'Lastname'); ?></label>
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
                  <label><?php echo language::translate('title_postcode', 'Postcode'); ?></label>
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
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h2 class="panel-title"><?php echo language::translate('title_payment_information', 'Payment Information'); ?></h2>
            </div>

            <div class="panel-body">
              <div class="row container-fluid">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_option_id', 'Option ID'); ?></label>
                  <?php echo functions::form_draw_text_field('payment_option[id]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_name', 'Name'); ?></label>
                  <?php echo functions::form_draw_text_field('payment_option[name]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_transaction_id', 'Transaction ID'); ?></label>
                  <?php echo functions::form_draw_text_field('payment_transaction_id', true); ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h2 class="panel-title"><?php echo language::translate('title_shipping_information', 'Shipping Information'); ?></h2>
            </div>

            <div class="panel-body">
              <div class="row container-fluid">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_option_id', 'Option ID'); ?></label>
                  <?php echo functions::form_draw_text_field('shipping_option[id]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_name', 'Name'); ?></label>
                  <?php echo functions::form_draw_text_field('shipping_option[name]', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_tracking_id', 'Tracking ID'); ?></label>
                  <?php echo functions::form_draw_text_field('shipping_tracking_id', true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_weight', 'Weight'); ?></label>
                  <span class="form-control"><?php echo weight::format($order->data['weight_total'], $order->data['weight_class']) ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <h2><?php echo language::translate('title_comments', 'Comments'); ?></h2>
      <ul id="comments" class="list-unstyled">
        <?php if (!empty($_POST['comments'])) foreach (array_keys($_POST['comments']) as $key) { ?>
        <li class="comment <?php echo $_POST['comments'][$key]['author']; ?><?php echo !empty($_POST['comments'][$key]['hidden']) ? ' semi-transparent' : null; ?>">
          <?php echo functions::form_draw_hidden_field('comments['. $key .'][id]', true); ?>
          <?php echo functions::form_draw_hidden_field('comments['. $key .'][order_id]', true); ?>
          <?php echo functions::form_draw_hidden_field('comments['. $key .'][author]', true); ?>
          <?php echo functions::form_draw_hidden_field('comments['. $key .'][text]', true); ?>
          <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle'); ?></a>
          <div class="text"><?php echo nl2br($_POST['comments'][$key]['text']); ?></div>
          <label class="private" title="<?php echo htmlspecialchars(language::translate('title_hidden', 'Hidden')); ?>"><?php echo functions::form_draw_checkbox('comments['.$key .'][hidden]', '1', true); ?> <?php echo functions::draw_fonticon('fa-eye-slash'); ?></label>
          <div class="date"><?php echo strftime(language::$selected['format_datetime'], strtotime($_POST['comments'][$key]['date_created'])); ?></div>
        </li>
        <?php } ?>
        <li class="text-right"><a class="add btn btn-default" href="#" title="<?php echo language::translate('title_add', 'Add'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_comment', 'Add Comment'); ?></a></li>
      </ul>
    </div>
  </div>

  <div class="row">
    <div class="col-md">
      <div id="order-items" class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><?php echo language::translate('title_order_items', 'Order Items'); ?></h2>
        </div>

        <div class="panel-body table-responsive">
          <table class="table table-striped data-table">
            <thead>
              <tr>
                <th><?php echo language::translate('title_item', 'Item'); ?></th>
                <th style="width: 200px;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
                <th style="width: 175px;"><?php echo language::translate('title_weight', 'Weight'); ?></th>
                <th style="width: 100px;"><?php echo language::translate('title_qty', 'Qty'); ?></th>
                <th style="width: 175px;"><?php echo language::translate('title_unit_price', 'Unit Price'); ?></th>
                <th style="width: 175px;"><?php echo language::translate('title_tax', 'Tax'); ?></th>
                <th style="width: 30px;">&nbsp;</th>
              </tr>
            </thead>
            <tbody>
<?php
  if (!empty($_POST['items'])) {
    foreach (array_keys($_POST['items']) as $key) {
?>
              <tr class="item">
                <td>
                  <?php echo !empty($_POST['items'][$key]['product_id']) ? '<a href="'. document::href_ilink('product', array('product_id' => $_POST['items'][$key]['product_id'])) .'" target="_blank">'. $_POST['items'][$key]['name'] .'</a>' : $_POST['items'][$key]['name']; ?></div>
                  <?php echo functions::form_draw_hidden_field('items['.$key.'][id]', true); ?>
                  <?php echo functions::form_draw_hidden_field('items['.$key.'][name]', true); ?>
                  <?php echo functions::form_draw_hidden_field('items['.$key.'][product_id]', true); ?>
                  <?php echo functions::form_draw_hidden_field('items['.$key.'][option_stock_combination]', true); ?>
<?php
      if (!empty($_POST['items'][$key]['options'])) {
        foreach (array_keys($_POST['items'][$key]['options']) as $field) {
          echo '<div>' . PHP_EOL
             . ' - '. $field .': ' . PHP_EOL;
          if (is_array($_POST['items'][$key]['options'][$field])) {
            $use_coma = false;
            foreach (array_keys($_POST['items'][$key]['options'][$field]) as $k) {
              echo '  ' . functions::form_draw_hidden_field('items['.$key.'][options]['.$field.']['.$k.']', true) . $_POST['items'][$key]['options'][$field][$k];
              if ($use_coma) echo ', ';
              $use_coma = true;
            }
          } else {
            echo '  ' . functions::form_draw_hidden_field('items['.$key.'][options]['.$field.']', true) . $_POST['items'][$key]['options'][$field];
          }
          echo '</div>' . PHP_EOL;
        }
      } else {
        echo functions::form_draw_hidden_field('items['.$key.'][options]', '');
      }
?>
                </td>
                <td><?php echo functions::form_draw_hidden_field('items['. $key .'][sku]', true); ?><?php echo $_POST['items'][$key]['sku']; ?></td>
                <td><div class="input-group"><?php echo functions::form_draw_decimal_field('items['. $key .'][weight]', true, 2, 0, null, 'style="width: 60%"'); ?> <?php echo functions::form_draw_weight_classes_list('items['. $key .'][weight_class]', true, false, 'style="width: 40%"'); ?></div></td>
                <td><?php echo functions::form_draw_decimal_field('items['. $key .'][quantity]', true, 2); ?></td>
                <td><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'items['. $key .'][price]', true); ?></td>
                <td><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'items['. $key .'][tax]', true); ?></td>
                <td><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
              </tr>
            </tbody>
<?php
    }
  }
?>
            <tfoot>
              <tr>
                <td colspan="7">
                  <a class="btn btn-default add-product" href="<?php echo document::href_link('', array('doc' => 'product_picker'), array('app'), array()); ?>" data-toggle="lightbox" data-width="" data-href="<?php echo document::href_link('', array('doc' => 'product_picker'), array('app'), array()); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_product', 'Add Product'); ?></a>
                  <a class="btn btn-default add-custom-item" href="<?php echo document::href_link('', array('doc' => 'add_custom_item'), array('app'), array()); ?>" data-toggle="lightbox" data-width="640px" data-href="<?php echo document::href_link('', array('doc' => 'add_custom_item'), array('app'), array()); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_custom_item', 'Add Custom Item'); ?></a>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md">
      <div id="order-total" class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><?php echo language::translate('title_order_total', 'Order Total'); ?></h2>
        </div>

        <div class="panel-body table-responsive">
          <table class="table table-striped data-table">
            <thead>
              <tr>
                <th style="width: 30px;">&nbsp;</th>
                <th><?php echo language::translate('title_module_id', 'Module ID'); ?></th>
                <th class="text-right"><?php echo language::translate('title_title', 'Title'); ?></th>
                <th style="width: 210px;"><?php echo language::translate('title_value', 'Value'); ?></th>
                <th style="width: 175px;"><?php echo language::translate('title_tax', 'Tax'); ?></th>
                <th style="width: 30px;">&nbsp;</th>
              </tr>
            </thead>
            <tbody>
<?php
  if (empty($_POST['order_total'])) {
    $_POST['order_total'][] = array(
      'id' => '',
      'module_id' => 'ot_subtotal',
      'title' => language::translate('title_subtotal', 'Subtotal'),
      'value' => '0',
      'tax' => '0',
      'calculate' => '0',
    );
  }
  foreach (array_keys($_POST['order_total']) as $key) {
    switch($_POST['order_total'][$key]['module_id']) {
      case 'ot_subtotal':
?>
              <tr>
                <td class="text-right">&nbsp;</td>
                <td class="text-right"><?php echo functions::form_draw_hidden_field('order_total['. $key .'][id]', true) . functions::form_draw_text_field('order_total['. $key .'][module_id]', true, 'readonly="readonly"'); ?></td>
                <td class="text-right"><?php echo functions::form_draw_text_field('order_total['. $key .'][title]', true, 'class="form-control text-right"'); ?></td>
                <td class="text-right">
                  <div class="input-group">
                    <span class="input-group-addon"><?php echo functions::form_draw_checkbox('order_total['. $key .'][calculate]', '1', true, 'disabled="disabled" title="'. htmlspecialchars(language::translate('title_calculate', 'Calculate')).'"'); ?></span>
                    <?php echo functions::form_draw_currency_field($_POST['currency_code'], 'order_total['. $key .'][value]', true, 'style="text-align: right;"'); ?>
                  </div>
                </td>
                <td class="text-right"><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'order_total['. $key .'][tax]', true, 'style="text-align: right;"'); ?></td>
                <td>&nbsp;</td>
              </tr>
<?php
        break;
      default:
?>
            <tr>
              <td class="text-right"><a href="#" class="add" title="<?php echo language::translate('text_insert_before', 'Insert before'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
              <td class="text-right"><?php echo functions::form_draw_hidden_field('order_total['. $key .'][id]', true) . functions::form_draw_text_field('order_total['. $key .'][module_id]', true); ?></td>
              <td class="text-right"><?php echo functions::form_draw_text_field('order_total['. $key .'][title]', true, 'style="text-align: right;"'); ?></td>
              <td class="text-right">
                <div class="input-group">
                <span class="input-group-addon"><?php echo functions::form_draw_checkbox('order_total['. $key .'][calculate]', '1', true, 'title="'. htmlspecialchars(language::translate('title_calculate', 'Calculate')) .'"'); ?></span>
                <?php echo functions::form_draw_currency_field($_POST['currency_code'], 'order_total['. $key .'][value]', true, 'style="text-align: right;"'); ?>
                </div>
              </td>
              <td class="text-right"><?php echo functions::form_draw_currency_field($_POST['currency_code'], 'order_total['. $key .'][tax]', true, 'style="text-align: right;"'); ?></td>
              <td><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
            </tr>

<?php
        break;
    }
  }
?>
            <tr>
              <td colspan="6"><a class="add" href="#" title="<?php echo language::translate('title_insert_', 'Insert'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
            </tr>
          </tbody>
          </tfoot>
            <tr>
              <td colspan="6" class="text-right" style="font-size: 1.5em;"><?php echo language::translate('title_payment_due', 'Payment Due'); ?>: <strong class="total"><?php echo currency::format($order->data['payment_due'], false, $_POST['currency_code'], $_POST['currency_value']); ?></strong></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md text-right">
      <div class="checkbox">
        <label><?php echo functions::form_draw_checkbox('email_order_copy', true); ?> <?php echo language::translate('title_send_order_copy_email', 'Send order copy email'); ?></label>
      </div>
    </div>

    <div class="col-md text-right">
      <div class="btn-group">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($order->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>
    </div>
  </div>

<?php echo functions::form_draw_form_end(); ?>

<div id="modal-customer-picker" class="modal fade" style="max-width: 640px; display: none;">

  <h2><?php echo language::translate('title_customer', 'Customer'); ?></h2>

  <div class="modal-body">
    <div class="form-group">
      <?php echo functions::form_draw_text_field('query', true, 'placeholder="'. htmlspecialchars(language::translate('title_search', 'Search')) .'"'); ?>
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
        <tbody>
        </tbody>
      </table>

      <p class="text-center"><button class="set-guest btn btn-default" type="button"><?php echo language::translate('text_set_as_guest', 'Set As Guest'); ?></button></p>
    </div>
  </div>

</div>

<script>
// Order

  $('select[name="currency_code"]').change(function(e){
    $('input[name="currency_value"]').val($(this).find('option:selected').data('value'));
    $('input[data-type="currency"]').closest('.input-group').find('.input-group-addon').text($(this).val());
    calculate_total();
  });

// Customer

  $('#customer-details button[name="get_address"]').click(function() {
    $.ajax({
      url: '<?php echo document::link('', array('doc' => 'get_address.json'), array('app')); ?>',
      type: 'post',
      data: 'customer_id=' + $('*[name="customer[id]"]').val() + '&token=<?php echo form::session_post_token(); ?>',
      cache: true,
      async: false,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        if (console) console.warn(errorThrown.message);
      },
      success: function(data) {
        $.each(data, function(key, value) {
          if (console) console.log(key +": "+ value);
          if ($('*[name="customer['+key+']"]').length) $('*[name="customer['+key+']"]').val(data[key]).trigger('change');
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
        if ($('select[name="customer[zone_code]"]').attr('disabled')) $('select[name="customer[zone_code]"]').removeAttr('disabled');
        if (data) {
          $.each(data, function(i, zone) {
            $('select[name="customer[zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="customer[zone_code]"]').attr('disabled', 'disabled');
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
        if ($('select[name="customer[shipping_address][zone_code]"]').attr('disabled')) $('select[name="customer[shipping_address][zone_code]"]').removeAttr('disabled');
        if (data) {
          $.each(data, function(i, zone) {
            $('select[name="customer[shipping_address][zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="customer[shipping_address][zone_code]]"]').attr('disabled', 'disabled');
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

  $(':input[name^="customer"]').bind('input propertyChange', function(){
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
        shipping_address: {
          company: $('input[name="customer[shipping_address][company]"]').val(),
          country_code: $('select[name="customer[shipping_address][country_code]"]').val(),
          zone_code: $('select[name="customer[shipping_address][zone_code]"]').val(),
        }
      }
    }

    $('.add-product').attr('href', $('.add-product').data('href') +'&'+ $.param(params));
    $('.add-custom-item').attr('href', $('.add-custom-item').data('href') +'&'+ $.param(params));
  });

  $(':input[name^="customer"]').first().trigger('input');

// Comments

  var new_comment_index = 0;
  $('#comments .add').click(function(e) {
    e.preventDefault();
    while ($('input[name="comments['+new_comment_index+'][id]"]').length) new_comment_index++;
    var output = '  <li class="comment staff">'
               + '    <?php echo functions::general_escape_js(functions::form_draw_hidden_field('comments[new_comment_index][id]', '') . functions::form_draw_hidden_field('comments[new_comment_index][author]', 'staff') . functions::form_draw_hidden_field('comments[new_comment_index][date_created]', strftime(language::$selected['format_datetime']))); ?>'
               + '    <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle'); ?></a>'
               + '    <div class="text"><?php echo functions::general_escape_js(functions::form_draw_textarea('comments[new_comment_index][text]', '')); ?></div>'
               + '    <label class="notify" title="<?php echo htmlspecialchars(language::translate('title_notify', 'Notify')); ?>"><?php echo functions::form_draw_checkbox('comments[new_comment_index][notify]', 1, true); ?> <?php echo functions::draw_fonticon('fa-envelope'); ?></label>'
               + '    <label class="private" title="<?php echo htmlspecialchars(language::translate('title_hidden', 'Hidden')); ?>"><?php echo functions::form_draw_checkbox('comments[new_comment_index][hidden]', 1, true); ?> <?php echo functions::draw_fonticon('fa-eye-slash'); ?></label>'
               + '    <div class="date"><?php echo strftime(language::$selected['format_datetime']); ?></div>'
               + '  </li>';
    output = output.replace(/new_comment_index/g, 'new_' + new_comment_index);
    $(this).closest('li').before(output);
    $('#comments textarea:last-child').focus();
    new_comment_index++;
  });

  $('#comments').on('click', ':input[name$="[hidden]"]', function(e) {
    $(this).closest('li').find(':input[name$="[notify]"]').prop('checked', false).trigger('change');
  });

  $('#comments').on('click', ':input[name$="[notify]"]', function(e) {
    $(this).closest('li').find(':input[name$="[hidden]"]').prop('checked', false).trigger('change');
  });

  $('#comments').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('li').remove();
  });

  $('#comments').on('change', 'input[name^="comments"][name$="[hidden]"]', function(e) {
    if ($(this).is(':checked')) {
      $(this).closest('li').addClass('semi-transparent');
    } else {
      $(this).closest('li').removeClass('semi-transparent');
    }
  });

// Order items

  var new_item_index = 0;
  window.addItem = function(item) {
    new_item_index++;

    var output = '  <tr class="item">'
               + '    <td>' + item.name
               + '      <?php echo functions::general_escape_js(functions::form_draw_hidden_field('items[new_item_index][id]', '')); ?>'
               + '      <?php echo functions::general_escape_js(functions::form_draw_hidden_field('items[new_item_index][product_id]', '')); ?>'
               + '      <?php echo functions::general_escape_js(functions::form_draw_hidden_field('items[new_item_index][option_stock_combination]', '')); ?>'
               + '      <?php echo functions::general_escape_js(functions::form_draw_hidden_field('items[new_item_index][options]', '')); ?>'
               + '      <?php echo functions::general_escape_js(functions::form_draw_hidden_field('items[new_item_index][name]', '')); ?>'
               + '    </td>'
               + '    <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('items[new_item_index][sku]', '')); ?>'+ item.sku +'</td>'
               + '    <td><div class="input-group"><?php echo functions::general_escape_js(functions::form_draw_decimal_field('items[new_item_index][weight]', '', 2, 0, null, 'style="width: 60%"')); ?> <?php echo str_replace(PHP_EOL, '', functions::form_draw_weight_classes_list('items[new_item_index][weight_class]', '', false, 'style="width: 40%"')); ?></div></td>'
               + '    <td><?php echo functions::general_escape_js(functions::form_draw_decimal_field('items[new_item_index][quantity]', '', 2)); ?></td>'
               + '    <td><?php echo functions::general_escape_js(functions::form_draw_currency_field($_POST['currency_code'], 'items[new_item_index][price]', '')); ?></td>'
               + '    <td><?php echo functions::general_escape_js(functions::form_draw_currency_field($_POST['currency_code'], 'items[new_item_index][tax]', '')); ?></td>'
               + '    <td><a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
               + '  </tr>';
    output = output.replace(/new_item_index/g, 'new_' + new_item_index);
    $('#order-items tbody').append(output);

    var row = $('#order-items tbody tr.item').last();
    $(row).find('*[name$="[product_id]"]').val(item.product_id);
    $(row).find('*[name$="[sku]"]').val(item.sku);
    $(row).find('*[name$="[option_stock_combination]"]').val(item.option_stock_combination);
    $(row).find('*[name$="[name]"]').val(item.name);
    $(row).find('*[name$="[sku]"]').val(item.sku);
    $(row).find('*[name$="[weight]"]').val(item.weight);
    $(row).find('*[name$="[weight_class]"]').val(item.weight_class);
    $(row).find('*[name$="[quantity]"]').val(item.quantity);
    $(row).find('*[name$="[price]"]').val(item.price);
    $(row).find('*[name$="[tax]"]').val(item.tax);

    if (item.options) {
      var product_options = '';
      $.each(item.options, function(group, value) {
        product_options += '<div>'
                         + '  - '+ group +': ';
        if ($.isArray(value)) {
          $.each(value, function(i, array_value) {
            product_options += '<input type="hidden" name="items[new_'+ new_item_index +'][options]['+ group +'][]" value="'+ escape(array_value) +'" />' + array_value +', ';
          });
          product_options = product_options.substring(0, product_options.length - 2);
        } else {
          product_options += '<input type="hidden" name="items[new_'+ new_item_index +'][options]['+ group +']" value="'+ escape(value) +'" />' + value;
        }
        product_options += '</div>';
      });
      $(row).find('input[type="hidden"][name$="[options]"]').replaceWith(product_options);
    }

    calculate_total();
  }

  $('#order-items').on('click', '.remove', function(event) {
    event.preventDefault();
    $(this).closest('tr').remove();
  });

// Order Total

  var new_ot_row_index = 0;
  $('#order-total').on('click', '.add', function(event) {
    while ($('input[name="order_total['+new_ot_row_index+'][id]"]').length) new_ot_row_index++;
    event.preventDefault();
    var output = '  <tr>'
               + '    <td class="text-right"><a href="#" class="add" title="<?php echo functions::general_escape_js(language::translate('text_insert_before', 'Insert before'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"')); ?></a></td>'
               + '    <td class="text-right"><?php echo functions::general_escape_js(functions::form_draw_hidden_field('order_total[new_ot_row_index][id]', '')); ?><?php echo functions::general_escape_js(functions::form_draw_text_field('order_total[new_ot_row_index][module_id]', '')); ?></td>'
               + '    <td class="text-right"><?php echo functions::general_escape_js(functions::form_draw_text_field('order_total[new_ot_row_index][title]', '', 'style="text-align: right;"')); ?></td>'
               + '    <td class="text-right">'
               + '      <div class="input-group">'
               + '        <span class="input-group-addon"><?php echo functions::general_escape_js(functions::form_draw_checkbox('order_total[new_ot_row_index][calculate]', '1', '1', 'title="'. htmlspecialchars(language::translate('title_calculate', 'Calculate')) .'"')); ?></span>'
               + '        <?php echo functions::general_escape_js(functions::form_draw_currency_field($_POST['currency_code'], 'order_total[new_ot_row_index][value]', currency::format_raw(0), 'style="text-align: right;"')); ?>'
               + '      </div>'
               + '    </td>'
               + '    <td class="text-right"><?php echo functions::general_escape_js(functions::form_draw_currency_field($_POST['currency_code'], 'order_total[new_ot_row_index][tax]', currency::format_raw(0), 'style="text-align: right;"')); ?></td>'
               + '    <td><a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
               + '  </tr>';
  output = output.replace(/new_ot_row_index/g, 'new_' + new_ot_row_index);
  $(this).closest('tr').before(output);
  new_ot_row_index++;
  });

  $('#order-total').on('click', '.remove', function(event) {
    event.preventDefault();
  $(this).closest('tr').remove();
  });

  function calculate_total() {
    var subtotal = 0;
    $('input[name^="items["][name$="[price]"]').each(function() {
      subtotal += Number($(this).val()) * Number($(this).closest('tr').find('input[name^="items["][name$="[quantity]"]').val());
    });
    subtotal = Math.round(subtotal * Math.pow(10, $('select[name="currency_code"] option:selected').data('decimals'))) / Math.pow(10, $('select[name="currency_code"] option:selected').data('decimals'));
    $('input[name^="order_total["][value="ot_subtotal"]').closest('tr').find('input[name^="order_total["][name$="[value]"]').val(subtotal);

    var subtotal_tax = 0;
    $('input[name^="items["][name$="[tax]"]').each(function() {
      subtotal_tax += Number($(this).val()) * Number($(this).closest('tr').find('input[name^="items["][name$="[quantity]"]').val());
    });
    subtotal_tax = Math.round(subtotal_tax * Math.pow(10, $('select[name="currency_code"] option:selected').data('decimals'))) / Math.pow(10, $('select[name="currency_code"] option:selected').data('decimals'));
    $('input[name^="order_total["][value="ot_subtotal"]').closest('tr').find('input[name^="order_total["][name$="[tax]"]').val(subtotal_tax);

    var order_total = subtotal + subtotal_tax;
    $('input[name^="order_total["][name$="[value]"]').each(function() {
      if ($(this).closest('tr').find('input[name^="order_total["][name$="[calculate]"]').is(':checked')) {
        order_total += Number(Number($(this).val()));
      }
    });
    $('input[name^="order_total["][name$="[tax]"]').each(function() {
      if ($(this).closest('tr').find('input[name^="order_total["][name$="[calculate]"]').is(':checked')) {
        order_total += Number($(this).val());
      }
    });
    order_total = Math.round(order_total * Math.pow(10, $('select[name="currency_code"] option:selected').data('decimals'))) / Math.pow(10, $('select[name="currency_code"] option:selected').data('decimals'));
    $('#order-total .total').text($('select[name="currency_code"] option:selected').data('prefix') + order_total + $('select[name="currency_code"] option:selected').data('suffix'));
  }

  $('body').on('click keyup', 'input[name^="items"][name$="[price]"], input[name^="items"][name$="[tax]"], input[name^="items"][name$="[quantity]"], input[name^="order_total"][name$="[value]"], input[name^="order_total"][name$="[tax]"], input[name^="order_total"][name$="[calculate]"], #order-items a.remove, #order-total a.remove', function() {
    calculate_total();
  });

// Customer Picker

  var xhr_customer_picker = null;
  $('#modal-customer-picker input[name="query"]').bind('propertyChange input', function(){
    if ($(this).val() == '') {
      $('#modal-customer-picker .results tbody').html('');
      xhr_customer_picker = null;
      return;
    }
    xhr_customer_picker = $.ajax({
      type: 'get',
      async: true,
      cache: false,
      url: '<?php echo document::link('', array('app' => 'customers', 'doc' => 'customers.json')); ?>&query=' + $(this).val(),
      dataType: 'json',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error(textStatus + ': ' + errorThrown);
      },
      success: function(json) {
        $('#modal-customer-picker .results tbody').html('');
        $.each(json, function(i, row){
          if (row) {
            $('#modal-customer-picker .results tbody').append(
              '<tr>' +
              '  <td class="id">' + row.id + '</td>' +
              '  <td class="name">' + row.name + '</td>' +
              '  <td class="email">' + row.email + '</td>' +
              '  <td class="date-created">' + row.date_created + '</td>' +
              '  <td></td>' +
              '</tr>'
            );
          }
        });
        if ($('#modal-customer-picker .results tbody').html() == '') {
          $('#modal-customer-picker .results tbody').html('<tr><td colspan="4"><em><?php echo functions::general_escape_js(language::translate('text_no_results', 'No results')); ?></em></td></tr>');
        }
      },
    });
  });

  $('#modal-customer-picker tbody').on('click', 'td', function() {
    var row = $(this).closest('tr');

    var id = $(row).find('.id').text();
    var name = $(row).find('.name').text();
    if (!id) {
      id = 0;
      name = '(<?php echo functions::general_escape_js(language::translate('title_guest', 'Guest')); ?>)';
    }

    $('input[name="customer[id]"]').val(id);
    $('.selected-account .id').text(id);
    $('.selected-account .name').text(name);
    $.featherlight.close();
  });

  $('#modal-customer-picker .set-guest').click(function(){
    $('input[name="customer[id]"]').val('0');
    $('.selected-account .id').text('0');
    $('.selected-account .name').text('(<?php echo functions::general_escape_js(language::translate('title_guest', 'Guest')); ?>)');
    $.featherlight.close();
  });
</script>