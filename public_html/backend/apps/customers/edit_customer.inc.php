<?php

  if (!empty($_GET['customer_id'])) {
    $customer = new ent_customer($_GET['customer_id']);
  } else {
    $customer = new ent_customer();
  }

  if (!$_POST) {
    $_POST = $customer->data;
  }

  document::$title[] = !empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_create_new_customer', 'Create New Customer');

  breadcrumbs::add(language::translate('title_customers', 'Customers'), document::ilink(__APP__.'/customers'));
  breadcrumbs::add(!empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_create_new_customer', 'Create New Customer'));

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

      header('Location: '. document::ilink('f:'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['newsletter'])) {
        $_POST['newsletter'] = 0;
      }

      foreach ([
        'code',
        'status',
        'email',
        'password',
        'newsletter',
        'notes',
        'default_billing_address_id',
        'default_shipping_address_id',
      ] as $field) {
        if (isset($_POST[$field])) {
          $customer->data[$field] = $_POST[$field];
        }
      }

      $customer->save();

      if (!empty($_POST['new_password'])) {
        $customer->set_password($_POST['new_password']);
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/customers'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {

      if (empty($customer->data['id'])) {
        throw new Exception(language::translate('error_must_provide_customer', 'You must provide a customer'));
      }

      $customer->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/customers'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (!empty($customer->data['id'])) {
    $orders = database::query(
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
    )->fetch();
  }
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_create_new_customer', 'Create New Customer'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('customer_form', 'post', '', false, 'autocomplete="off"'); ?>

      <div class="row" style="max-width: 960px;">

        <div class="col-md-8">

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_status', 'Status'); ?></label>
              <?php echo functions::form_toggle('status', 'e/d', (file_get_contents('php://input') != '') ? true : '1'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_code', 'Code'); ?></label>
              <?php echo functions::form_input_text('code', true); ?>
            </div>
          </div>

          <?php if (!empty($customer->data['id'])) { ?>
          <div class="form-group">
            <?php echo functions::form_button('sign_in', ['true', language::translate('text_sign_in_as_customer', 'Sign in as customer')], 'submit', 'class="btn btn-default btn-block"'); ?>
          </div>
          <?php } ?>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
              <?php echo functions::form_input_email('email', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_newsletter', 'Newsletter'); ?></label>
              <?php echo functions::form_checkbox('newsletter', ['1', language::translate('title_subscribe', 'Subscribe')], true); ?>
            </div>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_default_billing_address', 'Default Billing Address'); ?></label>
            <?php echo functions::form_select_address('default_billing_address_id', true); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_default_shipping_address', 'Default Shipping Address'); ?></label>
            <?php echo functions::form_select_address('default_shipping_address_id', true); ?>
          </div>

          <div class="row">

            <div class="form-group col-md-6">
              <label><?php echo !empty($customer->data['id']) ? language::translate('title_new_password', 'New Password') : language::translate('title_password', 'Password'); ?></label>
              <?php echo functions::form_input_password('new_password', '', 'autocomplete="new-password"'); ?>
            </div>

            <div class="col-md-6">
            </div>
          </div>

          <?php if (!empty($customer->data['id'])) { ?>
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_ip_address', 'Last IP Address'); ?></label>
              <?php echo functions::form_input_text('last_ip_address', true, 'readonly'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_hostname', 'Last Hostname'); ?></label>
              <?php echo functions::form_input_text('last_hostname', true, 'readonly'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_login', 'Last Login'); ?></label>
              <?php echo functions::form_input_text('date_login', true, 'readonly'); ?>
            </div>
          </div>
          <?php } ?>

          <div class="card-action">
            <?php echo functions::form_button_predefined('save'); ?>
            <?php if (!empty($customer->data['id'])) echo functions::form_button_predefined('delete'); ?>
            <?php echo functions::form_button_predefined('cancel'); ?>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label><?php echo language::translate('title_notes', 'Notes'); ?></label>
            <?php echo functions::form_input_textarea('notes', true, 'style="height: 450px;"'); ?>
          </div>

          <?php if (!empty($customer->data['id'])) { ?>
          <table class="table table-striped table-hover data-table">
            <tbody>
              <tr>
                <td><?php echo language::translate('title_orders', 'Orders'); ?><br>
                  <?php echo !empty($orders['total_count']) ? (int)$orders['total_count'] : '0'; ?>
                </td>
                <td><?php echo language::translate('title_total_sales', 'Total Sales'); ?><br>
                  <?php echo currency::format(fallback($orders['total_sales'], 0), false, settings::get('store_currency_code')); ?>
                </td>
              </tr>
            </tbody>
          </table>
          <?php } ?>
        </div>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>
