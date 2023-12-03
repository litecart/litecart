<?php

  if (!empty($_GET['order_status_id'])) {
    $order_status = new ent_order_status($_GET['order_status_id']);
  } else {
    $order_status = new ent_order_status();
  }

  if (!$_POST) {
    $_POST = $order_status->data;
  }

  document::$snippets['title'][] = !empty($order_status->data['id']) ? language::translate('title_edit_order_status', 'Edit Order Status') : language::translate('title_create_new_order_status', 'Create New Order Status');

  breadcrumbs::add(language::translate('title_order_statuses', 'Order Statuses'), document::ilink(__APP__.'/order_statuses'));
  breadcrumbs::add(!empty($order_status->data['id']) ? language::translate('title_edit_order_status', 'Edit Order Status') : language::translate('title_create_new_order_status', 'Create New Order Status'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['name'])) {
        throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
      }

      if (empty($_POST['notify'])) $_POST['notify'] = 0;
      if (empty($_POST['is_sale'])) $_POST['is_sale'] = 0;
      if (empty($_POST['is_archived'])) $_POST['is_archived'] = 0;
      if (empty($_POST['is_trackable'])) $_POST['is_trackable'] = 0;

      $fields = [
        'hidden',
        'state',
        'icon',
        'color',
        'is_sale',
        'is_archived',
        'is_trackable',
        'notify',
        'priority',
        'name',
        'description',
        'email_subject',
        'email_message',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) {
          $order_status->data[$field] = $_POST[$field];
        }
      }

      $order_status->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/order_statuses'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {

      if (empty($order_status->data['id'])) {
        throw new Exception(language::translate('error_must_provide_order_status', 'You must provide an order status'));
      }

      $order_status->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/order_statuses'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $states = [
    'created' => language::translate('title_created', 'Created'),
    'on_hold' => language::translate('title_on_hold', 'On Hold'),
    'ready' => language::translate('title_ready', 'Ready'),
    'delayed' => language::translate('title_delayed', 'Delayed'),
    'processing' => language::translate('title_processing', 'Processing'),
    'completed' => language::translate('title_completed', 'Completed'),
    'dispatched' => language::translate('title_dispatched', 'Dispatched'),
    'in_transit' => language::translate('title_in_transit', 'In Transit'),
    'delivered' => language::translate('title_delivered', 'Delivered'),
    'returning' => language::translate('title_returning', 'Returning'),
    'returned' => language::translate('title_returned', 'Returned'),
    'cancelled' => language::translate('title_cancelled', 'Cancelled'),
  ];

?>
<style>
.form-group .checkbox {
  margin-top: .5em;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($order_status->data['id']) ? language::translate('title_edit_order_status', 'Edit Order Status') : language::translate('title_create_new_order_status', 'Create New Order Status'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('order_status_form', 'post'); ?>

      <div class="row">
        <div class="col-md-6">
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_name', 'Name'); ?></label>
              <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_text_field('name['. $language_code .']', $language_code, true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_order_state', 'State'); ?></label>
              <?php echo functions::form_select_field('state', $states, true); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
              <?php echo functions::form_number_field('priority', true); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_description', 'Description'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_textarea('description['. $language_code .']', $language_code, true, 'style="height: 50px;"'); ?>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_icon', 'Icon'); ?> <a href="https://fontawesome.com/v4.7.0/icons/" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
              <?php echo functions::form_text_field('icon', true, 'placeholder="fa-circle-thin"'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_color', 'Color'); ?></label>
              <?php echo functions::form_color_field('color', empty($_POST['color']) ? '#cccccc' : true, 'placeholder="#cccccc"'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_properties', 'Properties'); ?></label>

              <div>
                <strong><?php echo functions::form_checkbox('hidden', ['1', language::translate('text_hidden', 'Hidden')], empty($_POST['hidden']) ? '0' : '1'); ?></strong>
                <?php echo language::translate('text_hidden_from_customer', 'Hidden from the customer\'s order history'); ?>
              </div>

              <div>
                <strong><?php echo functions::form_checkbox('is_sale', ['1', language::translate('text_is_sale', 'Is sale')], empty($_POST['is_sale']) ? '0' : '1'); ?></strong>
                <?php echo language::translate('text_include_in_sales_reports', 'Include in sales reports'); ?>
              </div>

              <div>
                <strong><?php echo functions::form_checkbox('is_archived', ['1', language::translate('text_is_archived', 'Is archived')], empty($_POST['is_archived']) ? '0' : '1'); ?></strong>
                <?php echo language::translate('text_exclude_from_list_of_orders', 'Exclude from the default list of orders'); ?>
              </div>

              <div class="checkbox">
                <strong><?php echo functions::form_checkbox('is_trackable', ['1', language::translate('text_is_trackable', 'Is trackable')], empty($_POST['is_trackable']) ? '0' : '1'); ?> </strong>
                <?php echo language::translate('text_will_send_tracking_event_to_shipping_module', 'Will send an event to the shipping module for tracking the shipment.'); ?></label>
              </div>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_stock_action', 'Stock Action'); ?></label>

              <div>
                <strong><?php echo functions::form_radio_button('stock_action', ['none', language::translate('title_none', 'None')], empty($_POST['stock_action']) ? 'none' : true); ?></strong>
                <?php echo language::translate('text_stock_remains_without_an_action', 'Stock remains without an action.'); ?>
              </div>

              <div>
                <strong><?php echo functions::form_radio_button('stock_action', ['reserve', language::translate('title_reserve_stock', 'Reserve Stock')], true); ?></strong>
                <?php echo language::translate('text_reserve_stock_for_orders_having_this_status', 'Reserve stock for orders having this status.'); ?>
              </div>

              <div class="checkbox">
                <strong><?php echo functions::form_radio_button('stock_action', ['commit', language::translate('title_commit_stock', 'Commit Stock')], true); ?> </strong>
                <?php echo language::translate('text_commit_changes_to_the_stock', 'Commit changes to stock.'); ?></label>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
                <?php echo functions::form_number_field('priority', true); ?>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <fieldset>
            <legend>
              <?php echo functions::form_checkbox('notify', ['1', language::translate('title_email_notification', 'Email Notification')], empty($_POST['notify']) ? '0' : '1'); ?>
            </legend>

            <?php if (count(language::$languages) > 1) { ?>
            <nav class="nav nav-tabs">
              <?php foreach (language::$languages as $language) { ?>
              <a class="nav-link<?php if ($language['code'] == language::$selected['code']) echo ' active'; ?>" data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a>
              <?php } ?>
            </nav>
            <?php } ?>

            <div class="tab-content">
              <?php foreach (array_keys(language::$languages) as $language_code) { ?>
              <div id="<?php echo $language_code; ?>" class="tab-pane fade in<?php if ($language_code == language::$selected['code']) echo ' active'; ?>">
                <div class="form-group">
                  <label><?php echo language::translate('title_subject', 'Subject'); ?></label>
                  <?php echo functions::form_regional_text_field('email_subject['. $language_code .']', $language_code, true); ?>
                </div>

                <div class="form-group">
                  <label><?php echo language::translate('title_message', 'Message'); ?></label>
                  <?php echo functions::form_regional_wysiwyg_field('email_message['. $language_code .']', $language_code, true); ?>
                </div>
              </div>
              <?php } ?>
            </div>

            <div><?php echo language::translate('title_aliases', 'Aliases'); ?>: <code>%order_id, %order_status, %firstname, %lastname, %billing_address, %order_items, %total, %payment_transaction_id, %shipping_address, %shipping_tracking_id, %shipping_tracking_url, %shipping_current_status, %shipping_current_location, %order_copy_url, %store_name, %store_url</code></div>
          </fieldset>
        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_button_predefined('save'); ?>
        <?php if (!empty($order_status->data['id'])) echo functions::form_button_predefined('delete'); ?>
        <?php echo functions::form_button_predefined('cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>
