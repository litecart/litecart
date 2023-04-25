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

  breadcrumbs::add(language::translate('title_order_statuses', 'Order Statuses'), document::link(WS_DIR_ADMIN, ['doc' => 'order_statuses'], ['app']));
  breadcrumbs::add(!empty($order_status->data['id']) ? language::translate('title_edit_order_status', 'Edit Order Status') : language::translate('title_create_new_order_status', 'Create New Order Status'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));

      if (empty($_POST['notify'])) $_POST['notify'] = 0;
      if (empty($_POST['is_sale'])) $_POST['is_sale'] = 0;
      if (empty($_POST['is_archived'])) $_POST['is_archived'] = 0;
      if (empty($_POST['is_trackable'])) $_POST['is_trackable'] = 0;

      $fields = [
        'state',
        'icon',
        'color',
        'is_sale',
        'is_archived',
        'is_trackable',
        'stock_action',
        'notify',
        'priority',
        'name',
        'description',
        'email_subject',
        'email_message',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $order_status->data[$field] = $_POST[$field];
      }

      $order_status->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'order_statuses'], true, ['order_status_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($order_status->data['id'])) throw new Exception(language::translate('error_must_provide_order_status', 'You must provide an order status'));

      $order_status->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'order_statuses'], true, ['order_status_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $state_options = [
    [language::translate('title_created', 'Created'), 'created'],
    [language::translate('title_on_hold', 'On Hold'), 'on_hold'],
    [language::translate('title_ready', 'Ready'), 'ready'],
    [language::translate('title_delayed', 'Delayed'), 'delayed'],
    [language::translate('title_processing', 'Processing'), 'processing'],
    [language::translate('title_dispatched', 'Dispatched'), 'dispatched'],
    [language::translate('title_in_transit', 'In Transit'), 'in_transit'],
    [language::translate('title_delivered', 'Delivered'), 'delivered'],
    [language::translate('title_returning', 'Returning'), 'returning'],
    [language::translate('title_returned', 'Returned'), 'returned'],
    [language::translate('title_cancelled', 'Cancelled'), 'cancelled'],
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
    <?php echo functions::form_draw_form_begin('order_status_form', 'post'); ?>

      <div class="row">
        <div class="col-md-6">
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_name', 'Name'); ?></label>
              <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_order_state', 'State'); ?></label>
              <?php echo functions::form_draw_select_field('state', $state_options, true); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
              <?php echo functions::form_draw_number_field('priority', true); ?>
            </div>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_description', 'Description'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_textarea($language_code, 'description['. $language_code .']', (isset($_POST['description'][$language_code]) ? $_POST['description'][$language_code] : ''), 'style="height: 50px;"');  $use_br = true; ?>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_icon', 'Icon'); ?> <a href="https://fontawesome.com/v4.7.0/icons/" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
              <?php echo functions::form_draw_text_field('icon', true, 'placeholder="fa-circle-thin"'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_color', 'Color'); ?></label>
              <?php echo functions::form_draw_color_field('color', empty($_POST['color']) ? '#cccccc' : true, 'placeholder="#cccccc"'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_properties', 'Properties'); ?></label>
              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('is_sale', '1', empty($_POST['is_sale']) ? '0' : '1'); ?> <?php echo language::translate('text_is_sale', 'Is sale'); ?><br />
                <?php echo language::translate('text_include_in_sales_reports', 'Include in sales reports'); ?></label>
              </div>

              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('is_archived', '1', empty($_POST['is_archived']) ? '0' : '1'); ?> <?php echo language::translate('text_is_archived', 'Is archived'); ?><br />
                <?php echo language::translate('text_exclude_from_list_of_orders', 'Exclude from the default list of orders'); ?></label>
              </div>

              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('is_trackable', '1', empty($_POST['is_trackable']) ? '0' : '1'); ?> <?php echo language::translate('text_is_trackable', 'Is trackable'); ?><br />
                <?php echo language::translate('text_will_send_tracking_event_to_shipping_module', 'Will send an event to the shipping module for tracking the shipment.'); ?></label>
              </div>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_stock_action', 'Stock Action'); ?></label>

              <div class="checkbox">
                <label><?php echo functions::form_draw_radio_button('stock_action', 'none', empty($_POST['stock_action']) ? 'none' : true); ?> <?php echo language::translate('title_none', 'None'); ?><br />
                <?php echo language::translate('text_stock_remains_without_an_action', 'Stock remains without any action'); ?></label>
              </div>

              <div class="checkbox">
                <label><?php echo functions::form_draw_radio_button('stock_action', 'reserve', true); ?> <?php echo language::translate('title_reserve', 'Reserve'); ?><br />
                <?php echo language::translate('text_reserve_stock_for_orders_having_this_status', 'Reserve stock for orders having this status'); ?></label>
              </div>

              <div class="checkbox">
                <label><?php echo functions::form_draw_radio_button('stock_action', 'commit', true); ?> <?php echo language::translate('title_commit', 'Commit'); ?><br />
                <?php echo language::translate('text_commit_changes_to_the_stock', 'Commit changes to the stock'); ?></label>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <fieldset>
            <legend>
              <label><?php echo functions::form_draw_checkbox('notify', '1', empty($_POST['notify']) ? '0' : '1'); ?> <?php echo language::translate('title_email_notification', 'Email Notification'); ?></label>
            </legend>

            <?php if (count(language::$languages) > 1) { ?>
            <nav class="nav nav-tabs">
              <?php foreach (language::$languages as $language) { ?>
              <a class="nav-link<?php echo ($language['code'] == language::$selected['code']) ? ' active' : ''; ?>" data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a>
              <?php } ?>
            </nav>
            <?php } ?>

            <div class="tab-content">
              <?php foreach (array_keys(language::$languages) as $language_code) { ?>
              <div id="<?php echo $language_code; ?>" class="tab-pane fade in<?php echo ($language_code == language::$selected['code']) ? ' active' : ''; ?>">
                <div class="form-group">
                  <label><?php echo language::translate('title_subject', 'Subject'); ?></label>
                  <?php echo functions::form_draw_regional_input_field($language_code, 'email_subject['. $language_code .']', true); ?>
                </div>

                <div class="form-group">
                  <label><?php echo language::translate('title_message', 'Message'); ?></label>
                  <?php echo functions::form_draw_regional_wysiwyg_field($language_code, 'email_message['. $language_code .']', true); ?>
                </div>
              </div>
              <?php } ?>
            </div>

            <div><?php echo language::translate('title_aliases', 'Aliases'); ?>: <code>%order_id, %order_status, %firstname, %lastname, %billing_address, %order_items, %payment_due, %payment_transaction_id, %shipping_address, %shipping_tracking_id, %shipping_tracking_url, %order_copy_url, %store_name, %store_url</code></div>
          </fieldset>
        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo !empty($order_status->data['id']) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(&quot;'. language::translate('text_are_you_sure', 'Are you sure?') .'&quot;)) return false;"', 'delete') : ''; ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
