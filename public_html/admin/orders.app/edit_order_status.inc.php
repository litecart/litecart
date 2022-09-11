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

      $fields = [
        'icon',
        'color',
        'keywords',
        'is_sale',
        'is_archived',
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
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo !empty($order_status->data['id']) ? language::translate('title_edit_order_status', 'Edit Order Status') : language::translate('title_create_new_order_status', 'Create New Order Status'); ?>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('order_status_form', 'post'); ?>

      <div class="row">
        <div class="col-md-6">
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_name', 'Name'); ?></label>
              <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_keywords', 'Keywords'); ?></label>
              <?php echo functions::form_draw_text_field('keywords', true); ?>
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
            <div class="form-group col-md-12">
              <label><?php echo language::translate('title_properties', 'Properties'); ?></label>
              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('is_sale', '1', empty($_POST['is_sale']) ? '0' : '1'); ?> <?php echo language::translate('text_is_sale', 'Is sale'); ?><br />
                <?php echo language::translate('order_status:description_is_sale', 'Reserve/withdraw stock and include in sales reports'); ?></label>
              </div>

              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('is_archived', '1', empty($_POST['is_archived']) ? '0' : '1'); ?> <?php echo language::translate('text_is_archived', 'Is archived'); ?><br />
                <?php echo language::translate('order_status:description_is_archived', 'Exclude from the default list of orders'); ?></label>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
                <?php echo functions::form_draw_number_field('priority', true); ?>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <fieldset>
            <legend>
              <label><?php echo functions::form_draw_checkbox('notify', '1', empty($_POST['notify']) ? '0' : '1'); ?> <?php echo language::translate('title_email_notification', 'Email Notification'); ?></label>
            </legend>

            <ul class="nav nav-tabs">
              <?php foreach (language::$languages as $language) { ?>
                <li<?php echo ($language['code'] == language::$selected['code']) ? ' class="active"' : ''; ?>><a data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a></li>
              <?php } ?>
            </ul>

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

      <div class="panel-action btn-group">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($order_status->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
